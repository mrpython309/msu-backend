<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Http\Requests\Procurement\ClosePurchaseRequisitionRequest;
use App\Models\Department;
use App\Models\PurchaseRequisition;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\PurchaseRequisitionService;
use App\Services\ReportPdfExportService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Facades\Excel;

class PurchaseRequisitionController extends Controller
{
    public function __construct(
        private readonly PurchaseRequisitionService $requisitions,
        private readonly ReportPdfExportService $pdf,
    ) {
    }

    public function index(Request $request)
    {
        $this->assertPurchaseOfficerAccess();
        $this->requisitions->ensureAllApprovedIndentsHavePRs();

        $filters = $request->only(['search', 'status', 'store_id', 'date_from', 'date_to']);

        $query = PurchaseRequisition::query()
            ->with([
                'indent.department',
                'indent.fromStore',
                'indent.toStore',
                'indent.requestedBy',
                'requisitionStore',
                'createdByUser',
                'purchaseOfficer',
                'closedByUser',
            ])
            ->filtered($filters)
            ->latest();

        if ($request->get('export') === 'excel') {
            return $this->exportExcel($query->get());
        }

        if ($request->get('export') === 'pdf') {
            return $this->exportListPdf($query->get(), $filters);
        }

        $requisitions = $query->paginate(15)->withQueryString();

        $stores = Department::query()
            ->orderBy('department_name')
            ->pluck('department_name', 'id');

        $statusOptions = PurchaseRequisitionStatus::filterOptions();

        return view('purchase-requisitions.index', compact('requisitions', 'stores', 'statusOptions', 'filters'));
    }

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $this->assertPurchaseOfficerAccess();

        $purchaseRequisition->load([
            'indent.items.category',
            'indent.department',
            'indent.fromStore',
            'indent.toStore',
            'indent.requestedBy',
            'vendor',
            'purchaseOfficer',
            'createdByUser',
            'closedByUser',
            'requisitionStore',
            'rfqs.quotations.vendor',
            'purchaseOrders.vendor',
            'purchaseOrders.grns',
            'activityLogs.user',
        ]);

        return view('purchase-requisitions.show', compact('purchaseRequisition'));
    }

    public function close(ClosePurchaseRequisitionRequest $request, PurchaseRequisition $purchaseRequisition)
    {
        $this->assertPurchaseOfficerAccess();

        try {
            $this->requisitions->manualClose(
                $purchaseRequisition,
                $request->user(),
                $request->validated('close_reason')
            );
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Successfully closed');
    }

    public function transfer(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $this->assertPurchaseOfficerAccess();

        try {
            $this->requisitions->transferFromStore(
                $purchaseRequisition,
                $request->user(),
                $request->input('remarks')
            );
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Successfully transferred to store');
    }

    public function transferAvailability(PurchaseRequisition $purchaseRequisition): \Illuminate\Http\JsonResponse
    {
        $this->assertPurchaseOfficerAccess();

        return response()->json(
            $this->requisitions->transferAssetAvailability($purchaseRequisition)
        );
    }

    /** @deprecated Internal RFQ workflow — not exposed in PR UI. */
    public function process(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $this->assertPurchaseOfficerAccess();
        $request->validate(['remarks' => 'nullable|string|max:2000']);

        try {
            $pr = $this->requisitions->process(
                $purchaseRequisition,
                $request->user(),
                $request->input('remarks')
            );
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Successfully transferred to RFQ');
    }

    private function exportExcel($requisitions)
    {
        $export = new class($requisitions) implements FromCollection, WithHeadings
        {
            public function __construct(private readonly Collection $requisitions) {}

            public function collection()
            {
                return $this->requisitions->map(fn (PurchaseRequisition $pr) => [
                    $pr->pr_no,
                    $pr->indent->indent_no ?? '',
                    $pr->statusEnum()?->label() ?? (string) $pr->status,
                    $pr->createdByUser->name ?? '',
                    optional($pr->created_at)->format('Y-m-d H:i'),
                    $pr->requisitionStore->department_name ?? ($pr->indent->toStore->department_name ?? $pr->indent->fromStore->department_name ?? ''),
                    $pr->reasonLabel() ?? '',
                    $pr->purchaseOfficer->name ?? '',
                ]);
            }

            public function headings(): array
            {
                return [
                    'PR No.',
                    'Indent No.',
                    'Status',
                    'Created By',
                    'Created Date & Time',
                    'Store',
                    'Approved/Rejected Reason',
                    'Processed By',
                ];
            }
        };

        return Excel::download($export, 'purchase-requisitions-'.now()->format('Ymd_His').'.xlsx');
    }

    private function exportListPdf($requisitions, array $filters)
    {
        return $this->pdf->download(
            'purchase-requisitions.export-list',
            compact('requisitions', 'filters'),
            'purchase-requisitions-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    private function assertPurchaseOfficerAccess(): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['superadmin', 'Super Admin'])) {
            return;
        }

        abort_unless(
            $user->can('purchase-requisition.view')
            || $user->can('purchase-requisitions:view')
            || $user->can('purchase-requisition.process')
            || $user->can('purchase-requisitions:process')
            || $user->can('purchase-requisition.proceed-to-rfq')
            || $user->can('purchase-requisitions:proceed-to-rfq')
            || $user->can('purchase-requisition.transfer-from-store')
            || $user->can('purchase-requisitions:transfer-from-store')
            || $user->can('purchase-requisition.manual-close')
            || $user->can('purchase-requisitions:manual-close')
            || $user->can('purchase-requisition.close'),
            403,
            'You do not have permission to access Purchase Requisitions.'
        );
    }
}
