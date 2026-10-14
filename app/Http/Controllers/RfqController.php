<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\RfqService;
use App\Services\ReportPdfExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class RfqController extends Controller
{
    public function __construct(
        private readonly RfqService $rfqs,
        private readonly ReportPdfExportService $pdf,
    ) {}

    public function index()
    {
        $rfqs = Rfq::with(['purchaseRequisition.indent', 'rfqVendors.vendor', 'selectedQuotation.vendor'])
            ->withCount('quotations')
            ->latest()
            ->paginate(15);

        return view('rfqs.index', compact('rfqs'));
    }

    public function create(Request $request)
    {
        $pr = $request->integer('pr_id')
            ? PurchaseRequisition::with(['indent.items', 'indent.department'])->findOrFail($request->integer('pr_id'))
            : null;

        if ($pr && $pr->rfqs()->exists()) {
            return redirect()
                ->route('rfqs.index')
                ->with('error', 'An RFQ already exists for purchase requisition '.$pr->pr_no.'.');
        }

        $prs = PurchaseRequisition::query()
            ->with(['indent.department', 'indent.items'])
            ->whereDoesntHave('rfqs')
            ->whereDoesntHave('purchaseOrders', function ($query) {
                $query->whereNotIn('status', [
                    \App\Enums\Procurement\PurchaseOrderStatus::Cancelled->value,
                    \App\Enums\Procurement\PurchaseOrderStatus::Rejected->value,
                ]);
            })
            ->latest()
            ->get()
            ->filter(fn (PurchaseRequisition $row) => $row->allowsProcurement() && ! $row->statusEnum()?->isAwaitingPurchaseOfficer())
            ->values();

        if ($pr && ! $prs->contains('id', $pr->id) && $pr->allowsProcurement() && ! $pr->statusEnum()?->isAwaitingPurchaseOfficer() && ! $pr->rfqs()->exists()) {
            $prs->prepend($pr);
        }

        $vendors = Vendor::query()
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $departments = Department::query()->active()->orderBy('department_name')->get();
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $currencies = ['INR', 'USD', 'EUR', 'GBP'];

        return view('rfqs.create', compact('pr', 'prs', 'vendors', 'departments', 'priorities', 'currencies'));
    }

    public function prData(PurchaseRequisition $purchaseRequisition): JsonResponse
    {
        abort_if(
            $purchaseRequisition->rfqs()->exists() || ! $purchaseRequisition->allowsProcurement() || $purchaseRequisition->statusEnum()?->isAwaitingPurchaseOfficer(),
            422,
            'An RFQ already exists for this purchase requisition, procurement is closed, or it has not been transferred to RFQ yet.'
        );

        $purchaseRequisition->load(['indent.items', 'indent.department']);

        $indent = $purchaseRequisition->indent;
        $indentItems = $indent?->items ?? collect();
        $itemMastersByName = \App\Models\ItemMaster::query()
            ->whereIn('item_name', $indentItems->pluck('item_name')->unique()->filter())
            ->get()
            ->keyBy(fn ($im) => mb_strtolower(trim((string) $im->item_name)));

        return response()->json([
            'pr_id' => $purchaseRequisition->id,
            'pr_no' => $purchaseRequisition->pr_no,
            'department' => $indent?->department?->department_name,
            'priority' => $indent?->priority,
            'expected_delivery_date' => optional($purchaseRequisition->required_date ?? $indent?->required_date)?->format('Y-m-d'),
            'items' => $indentItems->map(function ($item) use ($itemMastersByName) {
                $itemMaster = $itemMastersByName->get(mb_strtolower(trim((string) $item->item_name)));
                return [
                    'item_name' => $item->item_name,
                    'item_code' => $itemMaster?->item_code ?? null,
                    'hsn_code' => null,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'manufacturer' => $item->manufacturer,
                    'remarks' => $item->item_remarks,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
        $send = $request->input('action') === 'send';

        $data = $request->validate([
            'pr_id' => ['required', 'exists:purchase_requisitions,id'],
            'vendor_ids' => [$send ? 'required' : 'nullable', 'array', $send ? 'min:1' : 'nullable'],
            'vendor_ids.*' => ['exists:vendors,id'],
            'rfq_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:50'],
            'closes_at' => ['nullable', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'delivery_store' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'delivery_terms' => ['nullable', 'string', 'max:255'],
            'warranty_required' => ['nullable', 'boolean'],
            'amc_required' => ['nullable', 'boolean'],
            'tax_included' => ['nullable', 'boolean'],
            'freight_included' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'notes_to_vendor' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'action' => ['required', Rule::in(['draft', 'send'])],
        ]);

        try {
            $rfq = $this->rfqs->createForPurchaseRequisition(
                PurchaseRequisition::findOrFail($data['pr_id']),
                $data['vendor_ids'] ?? [],
                $request->user(),
                $data['notes_to_vendor'] ?? $data['notes'] ?? null,
                isset($data['closes_at']) ? Carbon::parse($data['closes_at']) : null,
                [
                    'rfq_date' => $data['rfq_date'] ?? now()->toDateString(),
                    'priority' => $data['priority'] ?? null,
                    'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                    'delivery_store' => $data['delivery_store'] ?? null,
                    'currency' => $data['currency'] ?? 'INR',
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'delivery_terms' => $data['delivery_terms'] ?? null,
                    'warranty_required' => (bool) ($data['warranty_required'] ?? false),
                    'amc_required' => (bool) ($data['amc_required'] ?? false),
                    'tax_included' => (bool) ($data['tax_included'] ?? false),
                    'freight_included' => (bool) ($data['freight_included'] ?? false),
                    'notes_to_vendor' => $data['notes_to_vendor'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ],
                $send,
                $request->file('attachments', []) ?? [],
            );

            $message = $send ? 'RFQ created and sent to vendors.' : 'RFQ saved as draft.';

            return redirect()->route('rfqs.show', $rfq)->with('success', $message);
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Rfq $rfq)
    {
        $rfq->load([
            'purchaseRequisition.indent.items',
            'purchaseRequisition.indent.department',
            'rfqVendors.vendor',
            'quotations.vendor',
            'quotations.items',
            'quotations.purchaseOrder',
            'selectedQuotation.purchaseOrder',
            'attachments',
        ]);

        $quotationsByVendor = $rfq->quotations->keyBy('vendor_id');
        $isDraft = $rfq->statusEnum() === \App\Enums\Procurement\RfqStatus::Draft;
        $hasPoFromQuotation = $rfq->quotations->contains(fn ($quotation) => $quotation->isNegotiationLocked());
        $quotedVendorIds = $rfq->quotations->pluck('vendor_id')->map(fn ($id) => (int) $id)->all();
        $hasSelectedVendor = $rfq->quotations->contains('is_selected', true);
        $canRecordQuotation = $rfq->rfqVendors
            ->pluck('vendor_id')
            ->map(fn ($id) => (int) $id)
            ->contains(fn (int $vendorId) => ! in_array($vendorId, $quotedVendorIds, true));

        return view('rfqs.show', compact('rfq', 'quotationsByVendor', 'isDraft', 'hasPoFromQuotation', 'canRecordQuotation', 'hasSelectedVendor'));
    }

    public function send(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'exists:vendors,id',
        ]);

        $vendorIds = $data['vendor_ids'] ?? [];
        if ($vendorIds === []) {
            $vendorIds = $rfq->rfqVendors()->pluck('vendor_id')->all();
        }

        try {
            $this->rfqs->send($rfq, $vendorIds, $request->user());

            return redirect()->route('rfqs.show', $rfq)->with('success', 'RFQ sent to selected vendors.');
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function invite(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'exists:vendors,id',
        ]);

        try {
            $this->rfqs->inviteVendors($rfq, $data['vendor_ids']);

            return back()->with('success', 'Vendors invited.');
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function close(Request $request, Rfq $rfq)
    {
        $this->rfqs->close($rfq, $request->user(), $request->input('comments'));

        return back()->with('success', 'RFQ closed.');
    }

    public function edit(Rfq $rfq)
    {
        $status = $rfq->statusEnum();
        abort_unless($status && in_array($status, [
            \App\Enums\Procurement\RfqStatus::Draft,
            \App\Enums\Procurement\RfqStatus::Open,
        ], true), 403, 'Only draft or open RFQs can be edited.');

        $rfq->load(['purchaseRequisition.indent.items', 'purchaseRequisition.indent.department', 'rfqVendors']);

        $vendors = Vendor::query()
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $departments = Department::query()->active()->orderBy('department_name')->get();
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $currencies = ['INR', 'USD', 'EUR', 'GBP'];
        $selectedVendorIds = $rfq->rfqVendors->pluck('vendor_id')->all();

        return view('rfqs.edit', compact('rfq', 'vendors', 'departments', 'priorities', 'currencies', 'selectedVendorIds'));
    }

    public function update(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['exists:vendors,id'],
            'rfq_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:50'],
            'closes_at' => ['nullable', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'delivery_store' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'notes_to_vendor' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        try {
            $this->rfqs->update(
                $rfq,
                [
                    'rfq_date' => $data['rfq_date'] ?? null,
                    'priority' => $data['priority'] ?? null,
                    'closes_at' => isset($data['closes_at']) ? Carbon::parse($data['closes_at']) : null,
                    'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                    'delivery_store' => $data['delivery_store'] ?? null,
                    'currency' => $data['currency'] ?? 'INR',
                    'notes_to_vendor' => $data['notes_to_vendor'] ?? null,
                    'notes' => $data['notes'] ?? $data['notes_to_vendor'] ?? null,
                ],
                $data['vendor_ids'] ?? [],
                $request->user(),
                $request->file('attachments', []) ?? [],
            );

            return redirect()->route('rfqs.show', $rfq)->with('success', 'RFQ updated successfully.');
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Rfq $rfq)
    {
        try {
            $this->rfqs->delete($rfq, $request->user());
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');
    }

    public function attachment(Rfq $rfq, \App\Models\RfqAttachment $attachment, string $mode = 'view')
    {
        abort_unless((int) $attachment->rfq_id === (int) $rfq->id, 404);

        $path = ltrim((string) $attachment->file_path, '/');
        // Normalize legacy paths that may include a "public/" or "storage/" prefix.
        $path = preg_replace('#^(public|storage)/#', '', $path) ?: $path;

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($path !== '' && $disk->exists($path), 404, 'Attachment not found.');

        $name = $attachment->original_name ?: basename($path);

        if ($mode === 'download') {
            return $disk->download($path, $name);
        }

        return $disk->response($path, $name, [
            'Content-Type' => $attachment->mime_type ?: $disk->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }

    public function pdf(Rfq $rfq)
    {
        $this->assertBeforeQuotations($rfq);
        $rfq->load([
            'purchaseRequisition.indent.items.category',
            'purchaseRequisition.indent.department',
            'rfqVendors.vendor',
        ]);
        $indent = $rfq->purchaseRequisition?->indent;
        $items = $indent?->items ?? collect();
        $items->each(function ($item) {
            $item->itemMaster = \App\Models\ItemMaster::with(['category', 'assetType'])
                ->where('item_name', $item->item_name)
                ->first();
        });

        return $this->pdf->download('rfqs.print', compact('rfq', 'indent', 'items'), "RFQ-{$rfq->rfq_no}.pdf", 'portrait');
    }

    public function excel(Rfq $rfq)
    {
        $this->assertBeforeQuotations($rfq);
        $rfq->load([
            'purchaseRequisition.indent.items.category',
            'purchaseRequisition.indent.department',
            'rfqVendors.vendor',
        ]);

        return Excel::download(new class($rfq) implements FromCollection {
            public function __construct(private readonly Rfq $rfq) {}

            public function collection(): Collection
            {
                $indent = $this->rfq->purchaseRequisition?->indent;
                $items = $indent?->items ?? collect();
                $status = $this->rfq->status instanceof \BackedEnum ? $this->rfq->status->value : $this->rfq->status;

                $rows = collect();
                $rows->push(['RFQ No.', $this->rfq->rfq_no, 'Status', $status]);
                $rows->push([]); // blank row
                $rows->push(['Item Details']);
                $rows->push([
                    'Item Name',
                    'Item Code',
                    'Category',
                    'Asset Type',
                    'Description',
                    'Quantity',
                    'Unit (UoM)',
                    'Estimated Unit Price',
                    'Estimated Total Amount',
                    'Manufacturer',
                    'Composition',
                    'Remarks',
                ]);
                if ($items->isEmpty()) {
                    $rows->push(['No items found.', '', '', '', '', '', '', '', '', '', '', '']);
                } else {
                    foreach ($items as $item) {
                        $itemMaster = \App\Models\ItemMaster::with(['category', 'assetType'])->where('item_name', $item->item_name)->first();
                        $rows->push([
                            $item->item_name,
                            $itemMaster?->item_code ?? '--',
                            $item->category?->name ?? ($itemMaster?->category?->name ?? '--'),
                            $itemMaster?->assetType?->name ?? '--',
                            $item->description ?: ($itemMaster?->description ?? '--'),
                            $item->quantity,
                            $item->unit ?? ($itemMaster?->uom ?? '--'),
                            $item->estimated_price !== null ? (number_format($item->estimated_price, 2)) : '--',
                            $item->total_amount !== null ? (number_format($item->total_amount, 2)) : '--',
                            $item->manufacturer ?: '--',
                            $item->composition ?: '--',
                            $item->item_remarks ?: '--',
                        ]);
                    }
                }

                return $rows;
            }
        }, "RFQ-{$rfq->rfq_no}.xlsx");
    }

    private function assertBeforeQuotations(Rfq $rfq): void
    {
        // Allow downloading RFQ details at any workflow stage
    }
}
