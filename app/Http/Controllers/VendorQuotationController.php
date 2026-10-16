<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\NegotiationStatus;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\VendorQuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorQuotationController extends Controller
{
    public function __construct(private readonly VendorQuotationService $quotations) {}

    public function create(Rfq $rfq)
    {
        $rfq->load(['purchaseRequisition.indent.items', 'quotations']);

        $quotedVendorIds = $rfq->quotations->pluck('vendor_id')->map(fn ($id) => (int) $id)->all();

        $vendors = $rfq->invitedVendors()
            ->orderBy('name')
            ->get()
            ->reject(fn (Vendor $vendor) => in_array((int) $vendor->id, $quotedVendorIds, true))
            ->values();

        if ($vendors->isEmpty() && $quotedVendorIds === []) {
            $vendors = Vendor::query()
                ->active()
                ->orderBy('name')
                ->get()
                ->reject(fn (Vendor $vendor) => in_array((int) $vendor->id, $quotedVendorIds, true))
                ->values();
        }

        if ($vendors->isEmpty()) {
            return redirect()
                ->route('rfqs.show', $rfq)
                ->with('error', 'All invited vendors already have quotations recorded for this RFQ.');
        }

        return view('quotations.create', compact('rfq', 'vendors'));
    }

    public function store(Request $request, Rfq $rfq)
    {
        $quotedVendorIds = $rfq->quotations()->pluck('vendor_id')->map(fn ($id) => (int) $id)->all();

        $data = $request->validate([
            'quotations' => 'required|array',
            'quotations.*.vendor_id' => [
                'required',
                'exists:vendors,id',
                Rule::notIn($quotedVendorIds),
            ],
            'quotations.*.contact_person' => 'nullable|string|max:255',
            'quotations.*.contact_phone' => 'nullable|string|max:50',
            'quotations.*.contact_email' => 'nullable|email|max:255',
            'quotations.*.price' => 'nullable|numeric|min:0',
            'quotations.*.tax' => 'nullable|numeric|min:0',
            'quotations.*.shipping' => 'nullable|numeric|min:0',
            'quotations.*.delivery_days' => 'nullable|integer|min:0',
            'quotations.*.warranty' => 'nullable|string|max:255',
            'quotations.*.notes' => 'nullable|string|max:2000',
            'quotations.*.items' => 'nullable|array',
            'quotations.*.items.*.indent_item_id' => 'nullable|integer|exists:indent_items,id',
            'quotations.*.items.*.item_name' => 'nullable|string|max:255',
            'quotations.*.items.*.quantity' => 'nullable|integer|min:1',
            'quotations.*.items.*.unit' => 'nullable|string|max:50',
            'quotations.*.items.*.unit_price' => 'required|numeric|gt:0',
        ], [
            'quotations.*.vendor_id.not_in' => 'A quotation is already recorded for this vendor on this RFQ.',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $recordedCount = 0;
            foreach ($data['quotations'] as $quoteData) {
                $this->quotations->recordQuotation(
                    $rfq,
                    Vendor::findOrFail($quoteData['vendor_id']),
                    $quoteData,
                    $request->user()
                );
                $recordedCount++;
            }
            \Illuminate\Support\Facades\DB::commit();

            return redirect()
                ->route('rfqs.show', $rfq)
                ->with('success', "{$recordedCount} quotation(s) recorded successfully.");
        } catch (ProcurementException $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while saving quotations.');
        }
    }

    public function show(Rfq $rfq, VendorQuotation $quotation)
    {
        abort_unless((int) $quotation->rfq_id === (int) $rfq->id, 404);

        $quotation->load(['vendor', 'items', 'rfq', 'purchaseOrder']);

        return view('quotations.show', compact('rfq', 'quotation'));
    }

    public function select(Request $request, VendorQuotation $quotation)
    {
        try {
            $this->quotations->selectQuotation($quotation, $request->user());

            return back()->with('success', 'Vendor quotation selected.');
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function compare(Rfq $rfq)
    {
        $rfq->load([
            'purchaseRequisition.indent.items',
            'quotations.vendor',
            'quotations.items',
            'quotations.purchaseOrder',
            'quotations.negotiationHistories.negotiatedBy',
            'selectedQuotation',
        ]);

        $quotations = $rfq->quotations
            ->sortBy(fn (VendorQuotation $q) => $q->effectiveQuote())
            ->values();

        $lowestEffective = $quotations->isNotEmpty()
            ? $quotations->first()->effectiveQuote()
            : null;

        return view('quotations.compare', compact('rfq', 'quotations', 'lowestEffective'));
    }

    public function negotiationData(VendorQuotation $quotation): JsonResponse
    {
        $quotation->load(['vendor', 'rfq', 'purchaseOrder', 'negotiationHistories.negotiatedBy', 'items.indentItem']);

        return response()->json([
            'success' => true,
            'quotation' => $this->serializeQuotation($quotation),
            'history' => $quotation->negotiationHistories->map(fn ($h) => $this->serializeHistory($h))->values(),
            'locked' => $quotation->isNegotiationLocked(),
            'statuses' => NegotiationStatus::values(),
        ]);
    }

    public function saveNegotiation(Request $request, VendorQuotation $quotation): JsonResponse
    {
        $data = $request->validate([
            'negotiated_total_cost' => ['required', 'numeric', 'min:0'],
            'buyer_remarks' => ['nullable', 'string', 'max:2000'],
            'vendor_response' => ['nullable', 'string', 'max:2000'],
            'negotiation_status' => ['nullable', 'string', Rule::in(NegotiationStatus::values())],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'exists:vendor_quotation_items,id'],
            'items.*.negotiated_unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $saved = $this->quotations->saveNegotiation($quotation, $data, $request->user());
            $rfq = Rfq::query()
                ->with(['quotations.vendor', 'quotations.purchaseOrder', 'quotations.negotiationHistories.negotiatedBy', 'quotations.items.indentItem'])
                ->findOrFail($saved->rfq_id);

            $rows = $rfq->quotations
                ->sortBy(fn (VendorQuotation $q) => $q->effectiveQuote())
                ->values();

            $lowest = $rows->isNotEmpty() ? $rows->first()->effectiveQuote() : null;

            return response()->json([
                'success' => true,
                'message' => 'Negotiation saved successfully.',
                'quotation' => $this->serializeQuotation($saved),
                'history' => $saved->negotiationHistories->map(fn ($h) => $this->serializeHistory($h))->values(),
                'rows' => $rows->map(fn (VendorQuotation $q) => $this->serializeCompareRow($q, $lowest))->values(),
                'lowest_effective' => $lowest,
            ]);
        } catch (ProcurementException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function serializeQuotation(VendorQuotation $quotation): array
    {
        $quotation->loadMissing('items.indentItem');

        return [
            'id' => $quotation->id,
            'rfq_no' => $quotation->rfq?->rfq_no,
            'vendor_name' => $quotation->vendor?->name,
            'original_quote' => $quotation->originalQuote(),
            'negotiated_quote' => $quotation->hasNegotiatedPrice()
                ? (float) $quotation->negotiated_total_cost
                : null,
            'current_negotiated_price' => $quotation->effectiveQuote(),
            'buyer_remarks' => $quotation->buyer_remarks,
            'vendor_response' => $quotation->vendor_response,
            'negotiation_status' => $quotation->negotiation_status ?: NegotiationStatus::Pending->value,
            'locked' => $quotation->isNegotiationLocked(),
            'items' => $quotation->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->indentItem?->item_name ?: ($item->item_name ?: 'Item #'.$item->id),
                    'quantity' => (int) $item->quantity,
                    'unit' => $item->unit ?: 'Nos',
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                    'negotiated_unit_price' => $item->negotiated_unit_price !== null ? (float) $item->negotiated_unit_price : (float) $item->unit_price,
                    'negotiated_total' => $item->negotiated_total !== null ? (float) $item->negotiated_total : (float) $item->total,
                ];
            })->values(),
        ];
    }

    private function serializeHistory($history): array
    {
        return [
            'round' => $history->round,
            'old_price' => (float) $history->old_price,
            'new_price' => (float) $history->new_price,
            'buyer_remarks' => $history->buyer_remarks,
            'vendor_response' => $history->vendor_response,
            'status' => $history->status,
            'negotiated_by' => $history->negotiatedBy?->name ?? '--',
            'negotiated_date' => $history->negotiated_at?->format('d M Y H:i') ?? '--',
        ];
    }

    private function serializeCompareRow(VendorQuotation $quotation, ?float $lowestEffective): array
    {
        $effective = $quotation->effectiveQuote();
        $status = $quotation->negotiation_status ?: NegotiationStatus::Pending->value;
        $statusEnum = NegotiationStatus::tryFrom($status);

        return [
            'id' => $quotation->id,
            'vendor_name' => $quotation->vendor?->name ?? '--',
            'contact_person' => $quotation->contact_person,
            'original_quote' => $quotation->originalQuote(),
            'negotiated_quote' => $quotation->hasNegotiatedPrice()
                ? (float) $quotation->negotiated_total_cost
                : $quotation->originalQuote(),
            'has_negotiated' => $quotation->hasNegotiatedPrice(),
            'savings' => $quotation->savingsAmount(),
            'negotiation_status' => $status,
            'negotiation_status_badge' => $statusEnum?->badgeClass() ?? 'bg-label-secondary',
            'is_selected' => (bool) $quotation->is_selected,
            'is_lowest' => $lowestEffective !== null && abs($effective - $lowestEffective) < 0.005,
            'locked' => $quotation->isNegotiationLocked(),
            'delivery_days' => $quotation->delivery_days,
            'warranty' => $quotation->warranty,
            'select_url' => route('vendor-quotations.select', $quotation),
            'negotiation_url' => route('vendor-quotations.negotiation.show', $quotation),
            'save_url' => route('vendor-quotations.negotiation.save', $quotation),
        ];
    }
}
