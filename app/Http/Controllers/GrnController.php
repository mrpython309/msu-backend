<?php

namespace App\Http\Controllers;

use App\Http\Requests\Procurement\ApproveGrnRequest;
use App\Http\Requests\Procurement\StoreGrnRequest;
use App\Models\Grn;
use App\Models\PurchaseOrder;
use App\Enums\Procurement\GrnStatus;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\GrnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrnController extends Controller
{
    public function __construct(private readonly GrnService $grns)
    {
    }

    public function index(Request $request)
    {
        $this->assertCanViewGrn();

        try {
            $this->grns->syncApprovedGrnAssets(Auth::user());
        } catch (\Throwable) {
            // Never block GRN listing if asset sync fails.
        }

        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);

        $awaitingPos = PurchaseOrder::query()
            ->awaitingGrn()
            ->with(['vendor', 'createdBy', 'purchaseRequisition.indent.department', 'items.grnItems', 'quotation.rfq', 'purchaseRequisition.rfqs'])
            ->latest('id')
            ->take(50)
            ->get();

        $grns = Grn::query()
            ->with(['items.poItem', 'items.assets', 'purchaseOrder.quotation.rfq', 'purchaseOrder.purchaseRequisition.rfqs', 'vendor', 'receivedBy', 'inspectedBy'])
            ->filtered($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $canCreateGrn = $this->userCanCreateGrn(Auth::user());
        $statusOptions = GrnStatus::cases();

        return view('grns.index', compact('grns', 'awaitingPos', 'canCreateGrn', 'filters', 'statusOptions'));
    }

    public function create(PurchaseOrder $purchaseOrder)
    {
        $this->assertCanCreateGrn();

        $purchaseOrder->load([
            'items.grnItems',
            'items.indentItem.category',
            'vendor',
            'purchaseRequisition.indent.department',
        ]);

        $users = \App\Models\User::orderBy('name')->get();

        return view('grns.create', compact('purchaseOrder', 'users'));
    }

    public function store(StoreGrnRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanCreateGrn();

        $data = $this->normalizeGrnPayload($request->validated(), $request);

        try {
            $grn = $this->grns->createFromPurchaseOrder($purchaseOrder, $request->user(), $data);

            foreach ($request->file('photos', []) as $photo) {
                $this->grns->attachDocument($grn, [
                    'document_type' => 'photo',
                    'path' => $photo->store('procurement/grns', 'public'),
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                ], $request->user());
            }

            if ($data['save_as_draft'] ?? false) {
                return redirect()
                    ->route('grns.show', $grn)
                    ->with('success', 'GRN saved as draft.');
            }

            return redirect()
                ->route('grns.show', $grn)
                ->with('success', 'GRN created successfully. Please inspect and approve to complete receipt and create assets.');
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Grn $grn)
    {
        $this->assertCanViewGrn();

        $grn->load([
            'purchaseOrder.items',
            'purchaseOrder.purchaseRequisition.indent.department',
            'purchaseOrder.createdBy',
            'vendor',
            'items.poItem.indentItem.category',
            'items.assets.department',
            'documents',
            'histories.user',
            'activityLogs.user',
            'receivedBy',
            'approvedBy',
            'inspectedBy',
        ]);

        try {
            $grn = $this->grns->ensureAssetsGenerated($grn, Auth::user());
        } catch (ProcurementException) {
            // GRN may not be in an accepted state yet; ignore on show.
        }

        return view('grns.show', compact('grn'));
    }

    public function approve(ApproveGrnRequest $request, Grn $grn)
    {
        $data = $request->validated();

        foreach ($data['items'] as $i => $item) {
            if (! array_key_exists('accepted_quantity', $item)) {
                $received = (int) ($item['received_quantity'] ?? 0);
                $rejected = (int) ($item['rejected_quantity'] ?? 0);
                $data['items'][$i]['accepted_quantity'] = max(0, $received - $rejected);
            }
        }

        try {
            $grn = $this->grns->approve(
                $grn,
                $request->user(),
                $data['items'],
                $data['inspection_status'],
                false,
                $data['comments'] ?? null,
                $data['inspection_remarks'] ?? null,
            );

            $message = "GRN Quality Inspection completed and approved. You can now transfer the approved items to Assets via the Quality Inspection module.";

            return back()->with('success', $message);
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** @deprecated Use approve() */
    public function inspect(ApproveGrnRequest $request, Grn $grn)
    {
        return $this->approve($request, $grn);
    }

    public function document(Grn $grn, \App\Models\GrnDocument $document, string $mode = 'view')
    {
        $this->assertCanViewGrn();
        abort_unless((int) $document->grn_id === (int) $grn->id, 404);

        $path = ltrim((string) $document->path, '/');
        $path = preg_replace('#^(public|storage)/#', '', $path) ?: $path;

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($path !== '' && $disk->exists($path), 404, 'Document not found.');

        $name = $document->fileName();

        if ($mode === 'download') {
            return $disk->download($path, $name);
        }

        return $disk->response($path, $name, [
            'Content-Type' => $document->mime_type ?: $disk->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }

    private function normalizeGrnPayload(array $data, Request $request): array
    {
        foreach ($data['items'] as $i => $item) {
            $serials = array_values(array_filter(array_map('trim', explode(',', (string) ($item['serial_numbers'] ?? '')))));
            $data['items'][$i]['serial_numbers'] = $serials ?: null;

            $received = (int) ($item['received_quantity'] ?? 0);
            $rejected = (int) ($item['rejected_quantity'] ?? 0);
            $data['items'][$i]['rejected_quantity'] = min($rejected, $received);
        }

        foreach (['invoice' => 'invoice_path', 'delivery_challan' => 'delivery_challan_path'] as $file => $key) {
            if ($request->hasFile($file)) {
                $data[$key] = $request->file($file)->store('procurement/grns', 'public');
            }
        }

        if (! empty($data['gate_entry_date']) && ! str_contains((string) $data['gate_entry_date'], ':')) {
            $data['gate_entry_date'] = $data['gate_entry_date'].' 00:00:00';
        }

        $data['save_as_draft'] = $request->boolean('save_as_draft');

        return $data;
    }

    private function assertCanViewGrn(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && ($user->can('grn.view') || $user->can('grns:view')),
            403,
            'You do not have permission to view GRNs.'
        );
    }

    private function assertCanCreateGrn(): void
    {
        abort_unless(
            $this->userCanCreateGrn(Auth::user()),
            403,
            'You do not have permission to create GRNs.'
        );
    }

    private function userCanCreateGrn(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        if ($user->can('grn.create') || $user->can('grns:create')) {
            return true;
        }

        $allowedEmail = strtolower((string) config('procurement.grn_create_email', 'store123@gmail.com'));

        return $allowedEmail !== ''
            && strtolower((string) $user->email) === $allowedEmail;
    }

    private function isPrivileged(?\App\Models\User $user): bool
    {
        try {
            return (bool) $user?->hasAnyRole(['superadmin', 'Super Admin']);
        } catch (\Throwable) {
            return false;
        }
    }
}
