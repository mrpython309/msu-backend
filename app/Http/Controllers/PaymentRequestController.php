<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\PaymentRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function __construct(
        private readonly PaymentRequestService $paymentRequestService
    ) {}

    public function index(Request $request): View
    {
        $this->assertCanView();

        try {
            PaymentRequest::where('payment_request_no', 'like', 'PAY-REQ-%')->get()->each(function ($item) {
                $item->update(['payment_request_no' => str_replace('PAY-REQ-', 'PAY-', $item->payment_request_no)]);
            });
        } catch (\Throwable $e) {}

        $query = PaymentRequest::with(['purchaseOrder.vendor', 'requestedBy', 'approvedBy', 'paidBy']);

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_request_no', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn ($po) => $po->where('po_no', 'like', "%{$search}%"))
                    ->orWhereHas('purchaseOrder.vendor', fn ($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        $paymentRequests = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'all' => PaymentRequest::count(),
            'pending' => PaymentRequest::where('status', PaymentRequest::STATUS_PENDING)->count(),
            'approved' => PaymentRequest::where('status', PaymentRequest::STATUS_APPROVED)->count(),
            'partially_paid' => PaymentRequest::where('status', PaymentRequest::STATUS_PARTIALLY_PAID)->count(),
            'paid' => PaymentRequest::where('status', PaymentRequest::STATUS_PAID)->count(),
            'rejected' => PaymentRequest::where('status', PaymentRequest::STATUS_REJECTED)->count(),
        ];

        $user = Auth::user();
        $canManagePayment = $this->userCanApproveOrPay($user);

        return view('payments.index', compact('paymentRequests', 'counts', 'canManagePayment'));
    }

    public function show(PaymentRequest $paymentRequest): View
    {
        $this->assertCanView();

        if (str_starts_with((string) $paymentRequest->payment_request_no, 'PAY-REQ-')) {
            $paymentRequest->update(['payment_request_no' => str_replace('PAY-REQ-', 'PAY-', $paymentRequest->payment_request_no)]);
        }

        $paymentRequest->load(['purchaseOrder.vendor', 'requestedBy', 'approvedBy', 'paidBy', 'rejectedBy']);
        $purchaseOrder = $paymentRequest->purchaseOrder;
        $user = Auth::user();
        $canManagePayment = $this->userCanApproveOrPay($user);
        $paymentMethods = PurchaseOrderPayment::METHODS;

        return view('payments.show', compact('paymentRequest', 'purchaseOrder', 'canManagePayment', 'paymentMethods'));
    }

    public function storeFromPo(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->assertCanCreate();

        try {
            if ($request->has('percentage') && is_numeric($request->input('percentage'))) {
                $percentage = (float) $request->input('percentage');
                if ($percentage >= 0) {
                    $purchaseOrder->update(['minimum_advance_payment_percentage' => $percentage]);
                }
            }

            $amount = $request->has('amount') && $request->input('amount') !== '' ? (float) $request->input('amount') : null;
            $paymentRequest = $this->paymentRequestService->createFromPurchaseOrder(
                $purchaseOrder,
                Auth::user(),
                $request->input('remarks'),
                $amount
            );

            return redirect()
                ->back()
                ->with('success', "Payment Request {$paymentRequest->payment_request_no} created successfully.");
        } catch (ProcurementException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->assertCanApproveOrPay();

        try {
            $this->paymentRequestService->approve($paymentRequest, Auth::user(), $request->input('remarks'));

            return redirect()->route('payments.show', $paymentRequest)
                ->with('success', "Payment Request {$paymentRequest->payment_request_no} approved successfully.");
        } catch (ProcurementException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->assertCanApproveOrPay();

        $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ], [
            'remarks.required' => 'Rejection remarks are required.',
        ]);

        try {
            $this->paymentRequestService->reject($paymentRequest, Auth::user(), $request->input('remarks'));

            return redirect()->route('payments.show', $paymentRequest)
                ->with('success', "Payment Request {$paymentRequest->payment_request_no} rejected.");
        } catch (ProcurementException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->assertCanApproveOrPay();

        $validated = $request->validate([
            'payment_amount' => ['nullable', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:'.implode(',', PurchaseOrderPayment::ATTACHMENT_MIMES), 'max:10240'],
        ]);

        try {
            $this->paymentRequestService->recordPayment(
                $paymentRequest,
                Auth::user(),
                $validated,
                $request->file('attachment')
            );

            $paymentRequest->refresh();
            $purchaseOrder = $paymentRequest->purchaseOrder?->fresh();
            $rem = $purchaseOrder ? $purchaseOrder->remainingAmount() : max(0, $paymentRequest->amount - (!empty($validated['payment_amount']) ? $validated['payment_amount'] : $paymentRequest->amount));
            $remFormatted = number_format((float) $rem, 2);

            $statusLabel = $paymentRequest->status;
            return redirect()->route('payments.show', $paymentRequest)
                ->with('success', "Payment saved successfully! Status is now '{$statusLabel}', Remaining Balance: ₹{$remFormatted}");
        } catch (ProcurementException $e) {
            return redirect()->back()->with('error', $e->getMessage())
                ->withInput();
        }
    }

    private function assertCanView(): void
    {
        $user = Auth::user();
        abort_unless(
            $user && ($this->isPrivileged($user) || $user->canAny(['payment.view', 'payments:view', 'purchase-order.payment', 'purchase-orders:payment', 'purchase-order.view', 'purchase-orders:view']) || $this->userCanApproveOrPay($user)),
            403,
            'You do not have permission to view payment requests.'
        );
    }

    private function assertCanCreate(): void
    {
        $user = Auth::user();
        abort_unless(
            $user && ($this->isPrivileged($user) || $user->canAny(['payment.create', 'payments:create', 'purchase-order.create', 'purchase-orders:create', 'purchase-order.edit', 'purchase-orders:edit'])),
            403,
            'You do not have permission to create payment requests.'
        );
    }

    private function assertCanApproveOrPay(): void
    {
        $user = Auth::user();
        abort_unless(
            $user && $this->userCanApproveOrPay($user),
            403,
            'You do not have permission to process or approve payment requests.'
        );
    }

    private function userCanApproveOrPay(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        if ($user->canAny(['payment.approve', 'payments:approve', 'payment.pay', 'payments:pay', 'purchase-order.payment', 'purchase-orders:payment', 'purchase-order.pay'])) {
            return true;
        }

        if ($user->hasAnyRole(['Accounts Department', 'Accounts', 'Finance & Accounts', 'Accounts Manager', 'Accounts Officer', 'L3 Approver', 'L3 Finance'])) {
            return true;
        }

        try {
            return $user->departments()->where(function ($q) {
                $q->where('department_name', 'like', '%Account%')
                    ->orWhere('department_name', 'like', '%Finance%');
            })->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function isPrivileged(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            if ($user->hasAnyRole(['superadmin', 'Super Admin'])) {
                return true;
            }
        } catch (\Throwable) {
            // keep checking
        }

        return false;
    }
}
