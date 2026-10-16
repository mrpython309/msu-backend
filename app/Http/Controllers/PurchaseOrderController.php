<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\ItemType;
use App\Enums\Procurement\PurchaseOrderStatus;
use App\Http\Requests\Procurement\StorePurchaseOrderPaymentRequest;
use App\Http\Requests\Procurement\StorePurchaseOrderRequest;
use App\Http\Requests\Procurement\UpdatePurchaseOrderRequest;
use App\Models\AssetCategory;
use App\Models\AssetName;
use App\Models\Department;
use App\Models\Indent;
use App\Models\IndentItem;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationItem;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\Procurement\PurchaseOrderApprovalService;
use App\Services\Procurement\PurchaseOrderPaymentService;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $orders,
        private readonly PurchaseOrderApprovalService $poApprovals,
        private readonly PurchaseOrderPaymentService $poPayments,
    ) {}


    public function index(Request $request)
    {
        $this->assertCanView();

        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);

        $orders = PurchaseOrder::query()
            ->with(['vendor', 'indent', 'department', 'store', 'createdBy', 'items:id,po_id,item_name', 'payments', 'quotation.rfq', 'purchaseRequisition.rfqs'])
            ->withSum('payments as payments_sum', 'payment_amount')
            ->filtered($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusOptions = PurchaseOrderStatus::filterOptions();
        $canMakePayment = $this->userCanMakePayment(Auth::user());
        $paymentMethods = PurchaseOrderPayment::METHODS;

        return view('purchase-orders.index', compact(
            'orders',
            'filters',
            'statusOptions',
            'canMakePayment',
            'paymentMethods'
        ));
    }

    public function create()
    {
        $this->assertCanCreate();

        return view('purchase-orders.create', $this->purchaseOrderFormData());
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $request->validated();
        $submit = ($data['action'] ?? '') === 'submit';

        try {
            // Always create as Draft first, then start the dynamic approval workflow.
            $po = $this->orders->createFromModule($request->user(), $data, false);

            if ($submit) {
                $po = $this->poApprovals->submit($po, $request->user(), $data['remarks'] ?? $data['notes'] ?? null);
            }
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $submit
            ? 'Purchase order submitted for approval.'
            : 'Purchase order saved as draft.';

        return redirect()
            ->route('purchase-orders.show', $po)
            ->with('success', $message);
    }

    public function prData(PurchaseRequisition $purchaseRequisition): JsonResponse
    {
        $this->assertCanCreate();

        abort_unless(
            PurchaseRequisition::query()->availableForPurchaseOrder()->whereKey($purchaseRequisition->id)->exists()
                || $purchaseRequisition->purchaseOrders()->exists(),
            404,
            'Purchase requisition is not available for purchase order creation.'
        );

        $purchaseRequisition->load([
            'indent.items.category.parent',
            'indent.department',
            'indent.fromStore',
            'indent.toStore',
            'requisitionStore',
            'vendor:id,name',
            'rfqs.quotations' => fn ($q) => $q->with(['vendor:id,name', 'items'])->latest('id'),
        ]);

        $indent = $purchaseRequisition->indent;
        $store = $purchaseRequisition->requisitionStore ?? $indent?->toStore ?? $indent?->fromStore;

        // Prefer the selected (awarded) quotation or any quotation with negotiated price; fall back to PR vendor.
        $selectedQuotation = $purchaseRequisition->rfqs
            ->flatMap(fn ($rfq) => $rfq->quotations)
            ->first(fn ($q) => (bool) $q->is_selected)
            ?? $purchaseRequisition->rfqs
                ->flatMap(fn ($rfq) => $rfq->quotations)
                ->first(fn ($q) => $q->hasNegotiatedPrice());

        if (! $selectedQuotation) {
            $selectedQuotation = $purchaseRequisition->rfqs
                ->flatMap(fn ($rfq) => $rfq->quotations)
                ->sortByDesc('id')
                ->first();
        }

        $vendorId = $selectedQuotation?->vendor_id ?: $purchaseRequisition->vendor_id;
        $vendorName = $selectedQuotation?->vendor?->name
            ?: $purchaseRequisition->vendor?->name;

        $originalTotal = $selectedQuotation?->originalQuote() ?? 0;
        $effectiveTotal = $selectedQuotation?->effectiveQuote() ?? 0;

        $quoteItems = collect($selectedQuotation?->items ?? []);
        $quoteItemsByIndentId = $quoteItems
            ->filter(fn ($qi) => $qi->indent_item_id)
            ->keyBy(fn ($qi) => (int) $qi->indent_item_id);

        $quoteItemsByName = $quoteItems
            ->keyBy(fn ($qi) => mb_strtolower(trim((string) $qi->item_name)));

        $indentItems = $indent?->items ?? collect();
        $indentSumQty = max(1, (int) $indentItems->sum('quantity'));
        $headerUnitPrice = null;
        if ($selectedQuotation?->hasNegotiatedPrice() && $effectiveTotal > 0) {
            $headerUnitPrice = $selectedQuotation->negotiatedUnitRate();
        } elseif ($selectedQuotation && $effectiveTotal > 0) {
            $headerUnitPrice = round($effectiveTotal / $indentSumQty, 2);
        }

        $itemMastersByName = \App\Models\ItemMaster::query()
            ->with(['assetType', 'category.parent'])
            ->whereIn('item_name', $indentItems->pluck('item_name')->unique()->filter())
            ->get()
            ->keyBy(fn ($im) => mb_strtolower(trim((string) $im->item_name)));

        return response()->json([
            'pr_no' => $purchaseRequisition->pr_no,
            'indent_no' => $indent?->indent_no,
            'department_id' => $indent?->department_id,
            'department_name' => $indent?->department?->department_name,
            'store_id' => $store?->id,
            'store_name' => $store?->department_name,
            'grn_store' => $store?->department_name,
            'remarks' => $purchaseRequisition->remarks ?? $indent?->remarks,
            'vendor_id' => $vendorId,
            'vendor_name' => $vendorName,
            'quotation_id' => $selectedQuotation?->id,
            'negotiated_total' => $selectedQuotation?->hasNegotiatedPrice() ? $effectiveTotal : null,
            'items' => $indentItems->map(function ($item) use ($quoteItemsByIndentId, $quoteItemsByName, $headerUnitPrice, $selectedQuotation, $itemMastersByName) {
                $category = $item->category;
                $assetTypeName = trim((string) ($item->composition ?? ''));
                $quoteItem = $quoteItemsByIndentId->get((int) $item->id)
                    ?: $quoteItemsByName->get(mb_strtolower(trim((string) $item->item_name)));

                $price = null;
                if ($selectedQuotation?->hasNegotiatedPrice()) {
                    // After RFQ negotiation: purchase rate must come from negotiated price only.
                    $price = $selectedQuotation->negotiatedUnitRate($quoteItem)
                        ?? $headerUnitPrice;
                } elseif ($quoteItem && (float) $quoteItem->unit_price > 0) {
                    $price = (float) $quoteItem->unit_price;
                } elseif ($headerUnitPrice !== null) {
                    $price = $headerUnitPrice;
                } else {
                    $price = $item->estimated_price;
                }

                $itemMaster = $itemMastersByName->get(mb_strtolower(trim((string) $item->item_name)));

                $catId = $category?->parent_id ?: $category?->id;
                $subCatId = $category?->parent_id ? $category->id : null;
                if (! $subCatId && $itemMaster?->assetType?->sub_category_id) {
                    $subCatId = (int) $itemMaster->assetType->sub_category_id;
                    $subCat = \App\Models\AssetCategory::query()->find($subCatId);
                    if ($subCat?->parent_id && ! $catId) $catId = $subCat->parent_id;
                }

                return [
                    'indent_item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $itemMaster?->item_code ?? null,
                    'hsn_code' => null,
                    'manufacturer' => $item->manufacturer,
                    'marketer_name' => null,
                    'item_type' => ItemType::Asset->value,
                    'asset_type_name' => $assetTypeName,
                    'quantity' => (int) ($quoteItem?->quantity ?: $item->quantity),
                    'unit' => $quoteItem?->unit ?? $item->unit ?? 'Nos',
                    'purchase_unit' => $quoteItem?->unit ?? $item->unit ?? 'Nos',
                    'price' => $price !== null && (float) $price > 0 ? (float) $price : null,
                    'qoh' => $item->stock_in_hand,
                    'item_category_id' => $catId,
                    'item_sub_category_id' => $subCatId,
                    'item_category_name' => $catId ? ($category?->parent_id ? $category->parent?->category_name : \App\Models\AssetCategory::query()->whereKey($catId)->value('category_name')) : null,
                    'item_sub_category_name' => $subCatId ? \App\Models\AssetCategory::query()->whereKey($subCatId)->value('category_name') : null,
                    'conversion' => $item->conversion ?? null,
                ];
            })->values() ?? [],
        ]);
    }

    public function itemHistory(Request $request): JsonResponse
    {
        $itemName = trim((string) $request->query('item_name', ''));
        $itemCode = trim((string) $request->query('item_code', ''));
        $excludePoId = $request->integer('exclude_po_id');
        $currentPo = $excludePoId ? PurchaseOrder::find($excludePoId) : null;

        if ($itemName === '' && $itemCode === '') {
            return response()->json(['success' => true, 'history' => []]);
        }

        $history = PoItem::query()
            ->where(function ($query) use ($itemName, $itemCode) {
                if ($itemName !== '') {
                    $query->whereRaw('LOWER(TRIM(item_name)) = ?', [mb_strtolower($itemName)]);
                } elseif ($itemCode !== '' && $itemCode !== '--' && !str_starts_with(strtoupper($itemCode), 'IND-')) {
                    $query->whereRaw('LOWER(TRIM(item_code)) = ?', [mb_strtolower($itemCode)]);
                }
            })
            ->when($currentPo, function ($query, $po) {
                $query->whereHas('purchaseOrder', function ($poQuery) use ($po) {
                    $poQuery->where(function ($dateQ) use ($po) {
                        if ($po->purchase_date) {
                            $dateQ->whereDate('purchase_date', '<=', $po->purchase_date)
                                  ->orWhere('created_at', '<=', $po->created_at);
                        } else {
                            $dateQ->where('created_at', '<=', $po->created_at);
                        }
                    });
                });
            })
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNotIn('status', [
                    PurchaseOrderStatus::Cancelled->value,
                    PurchaseOrderStatus::Draft->value,
                    PurchaseOrderStatus::Rejected->value,
                    PurchaseOrderStatus::Submitted->value,
                ]);
            })
            ->whereHas('grnItems.grn', function ($q) {
                $q->whereIn('status', [
                    \App\Enums\Procurement\GrnStatus::Accepted->value,
                    \App\Enums\Procurement\GrnStatus::PartiallyAccepted->value,
                    \App\Enums\Procurement\GrnStatus::Closed->value,
                ]);
            })
            ->with(['purchaseOrder.vendor'])
            ->get()
            ->sortByDesc(fn (PoItem $item) => $item->purchaseOrder?->purchase_date ?? $item->purchaseOrder?->created_at)
            ->values()
            ->map(function (PoItem $item) {
                $po = $item->purchaseOrder;
                $rate = (float) $item->price;
                $qty = (int) $item->quantity;
                $total = (float) ($item->total > 0 ? $item->total : ($qty * $rate));

                return [
                    'item_name' => $item->item_name ?? '--',
                    'item_price' => number_format($rate, 2),
                    'po_no' => $po?->po_no ?? '--',
                    'vendor' => $po?->vendor?->name ?? '--',
                    'date' => $po?->purchase_date?->format('d M Y') ?? $po?->created_at?->format('d M Y') ?? '--',
                    'quantity' => $qty,
                    'rate' => number_format($rate, 2),
                    'total' => number_format($total, 2),
                    'currency' => $po?->currency ?? 'INR',
                    'url' => $po ? route('purchase-orders.show', $po) : '#',
                ];
            });

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function searchItems(Request $request): JsonResponse
    {
        $this->assertCanCreate();

        $q = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 25), 1), 50);
        $results = collect();

        $assetNames = AssetName::query()
            ->active()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit($limit)
            ->get();

        foreach ($assetNames as $assetName) {
            $results->push([
                'id' => 'asn:'.$assetName->id,
                'item_name' => $assetName->name,
                'item_code' => 'ASN-'.str_pad((string) $assetName->id, 6, '0', STR_PAD_LEFT),
                'manufacturer' => null,
                'marketer_name' => null,
                'hsn_code' => null,
                'item_type' => ItemType::Asset->value,
                'purchase_unit' => 'Nos',
                'unit' => 'Nos',
                'qoh' => null,
                'item_category_id' => null,
                'item_sub_category_id' => null,
                'conversion' => null,
            ]);
        }

        $indentItems = IndentItem::query()
            ->with('category.parent')
            ->when($q !== '', fn ($query) => $query->where('item_name', 'like', "%{$q}%"))
            ->latest('id')
            ->limit($limit)
            ->get();

        $itemMastersByName = \App\Models\ItemMaster::query()
            ->with(['assetType', 'category.parent'])
            ->whereIn('item_name', $indentItems->pluck('item_name')->unique()->filter())
            ->get()
            ->keyBy(fn ($im) => mb_strtolower(trim((string) $im->item_name)));

        foreach ($indentItems as $item) {
            $itemMaster = $itemMastersByName->get(mb_strtolower(trim((string) $item->item_name)));
            $category = $item->category;
            $catId = $category?->parent_id ?: $category?->id;
            $subCatId = $category?->parent_id ? $category->id : null;
            if (! $subCatId && $itemMaster?->assetType?->sub_category_id) {
                $subCatId = (int) $itemMaster->assetType->sub_category_id;
                $subCat = \App\Models\AssetCategory::query()->find($subCatId);
                if ($subCat?->parent_id && ! $catId) $catId = $subCat->parent_id;
            }
            $results->push([
                'id' => 'ind:'.$item->id,
                'item_name' => $item->item_name,
                'item_code' => $itemMaster?->item_code ?? null,
                'manufacturer' => $item->manufacturer,
                'marketer_name' => null,
                'hsn_code' => null,
                'item_type' => ItemType::Asset->value,
                'asset_type_name' => trim((string) ($item->composition ?? '')),
                'purchase_unit' => $item->unit ?? 'Nos',
                'unit' => $item->unit ?? 'Nos',
                'qoh' => $item->stock_in_hand,
                'item_category_id' => $catId,
                'item_sub_category_id' => $subCatId,
                'conversion' => $item->conversion ?? null,
            ]);
        }

        return response()->json([
            'results' => $results
                ->unique('item_name')
                ->take($limit)
                ->values(),
        ]);
    }

    public function searchVendorRates(Request $request): JsonResponse
    {
        $this->assertCanCreate();

        $vendorId = (int) $request->query('vendor_id', 0);
        $itemName = trim((string) $request->query('item_name', ''));
        $limit = min(max((int) $request->query('limit', 15), 1), 30);

        if ($vendorId <= 0) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Prefer selected + negotiated quotations for this vendor.
        $quotations = VendorQuotation::query()
            ->where('vendor_id', $vendorId)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('negotiated_total_cost')->where('negotiated_total_cost', '>', 0);
                })->orWhere('is_selected', true);
            })
            ->with(['rfq:id,rfq_no', 'items'])
            ->orderByDesc('is_selected')
            ->latest('quoted_at')
            ->latest('id')
            ->limit($limit)
            ->get();

        foreach ($quotations as $quotation) {
            $lines = $quotation->items;
            if ($itemName !== '') {
                $lines = $lines->filter(
                    fn ($line) => str_contains(mb_strtolower((string) $line->item_name), mb_strtolower($itemName))
                );
            }

            if ($lines->isNotEmpty()) {
                foreach ($lines as $line) {
                    $unitRate = $quotation->hasNegotiatedPrice() 
                        ? $quotation->negotiatedUnitRate($line) 
                        : ((float) $line->unit_price > 0 ? (float) $line->unit_price : null);

                    if ($unitRate === null || $unitRate <= 0) {
                        continue;
                    }

                    $results->push([
                        'rate' => $unitRate,
                        'label' => number_format($unitRate, 2),
                        'title' => $line->item_name ?: 'Negotiated unit price',
                        'meta' => trim(
                            ($quotation->is_selected ? 'Selected quote · ' : '')
                            .'Negotiated'
                            .($quotation->rfq?->rfq_no ? ' · '.$quotation->rfq->rfq_no : '')
                            .($quotation->quoted_at ? ' · '.$quotation->quoted_at->format('d M Y') : '')
                        ),
                        'source' => 'negotiated_unit_price',
                    ]);
                }
            } else {
                $unitRate = $quotation->hasNegotiatedPrice() 
                    ? $quotation->negotiatedUnitRate()
                    : ($quotation->originalQuote() > 0 ? round($quotation->originalQuote() / max(1, (int) $quotation->items->sum('quantity')), 2) : null);

                if ($unitRate === null || $unitRate <= 0) {
                    continue;
                }

                $results->push([
                    'rate' => $unitRate,
                    'label' => number_format($unitRate, 2),
                    'title' => $quotation->hasNegotiatedPrice() ? 'Negotiated purchase rate' : 'Quoted purchase rate',
                    'meta' => trim(
                        ($quotation->is_selected ? 'Selected · ' : '')
                        .($quotation->hasNegotiatedPrice() ? 'Negotiated' : 'Quoted')
                        .($quotation->rfq?->rfq_no ? ' · '.$quotation->rfq->rfq_no : '')
                        .($quotation->quoted_at?->format('d M Y') ?? '')
                    ),
                    'source' => $quotation->hasNegotiatedPrice() ? 'negotiated_total_cost' : 'total_cost',
                ]);
            }
        }

        $results = $results
            ->unique(fn ($row) => $row['source'].':'.$row['rate'].':'.($row['title'] ?? ''))
            ->take($limit)
            ->values();

        return response()->json(['results' => $results]);
    }

    /** @deprecated Use prData() */
    public function indentData(Indent $indent): JsonResponse
    {
        $pr = $indent->purchaseRequisition;
        abort_unless($pr, 404);

        return $this->prData($pr);
    }

    /** Legacy RFQ quotation path — kept for backward compatibility. */
    public function createFromQuotation(VendorQuotation $quotation)
    {
        $this->assertCanCreate();

        $quotation->load(['vendor', 'rfq.purchaseRequisition.indent.items', 'items']);

        return view('purchase-orders.create-from-quotation', compact('quotation'));
    }

    public function storeFromQuotation(Request $request, VendorQuotation $quotation)
    {
        $this->assertCanCreate();

        $data = $request->validate([
            'purchase_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'grn_store' => 'nullable|string|max:255',
            'po_type' => 'nullable|string|max:50',
            'discount_type' => 'nullable|string|max:20',
            'department_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|max:10',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:2000',
            'items' => 'array',
            'items.*.quotation_item_id' => 'nullable|integer',
            'items.*.indent_item_id' => 'nullable|integer',
            'items.*.item_code' => 'nullable|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:50',
            'items.*.manufacturer' => 'nullable|string|max:255',
            'items.*.marketer_name' => 'nullable|string|max:255',
            'items.*.has_free_qty' => 'nullable|boolean',
            'items.*.free_qty' => 'nullable|numeric|min:0',
        ]);

        try {
            $po = $this->orders->createFromSelectedQuotation($quotation, $request->user(), $data);
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('purchase-orders.show', $po)
            ->with('success', 'Purchase order created from quotation.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->assertCanView();

        $purchaseOrder->load([
            'vendor',
            'items',
            'quotation',
            'purchaseRequisition.indent',
            'indent.department',
            'indent.fromStore',
            'indent.toStore',
            'department',
            'store',
            'grns',
            'createdBy',
            'activityLogs.user',
            'approvalSteps.approver',
            'approvalSteps.actedBy',
            'assignedApprover',
            'payments.createdBy',
        ]);

        $user = request()->user();

        $linkedRfq = $purchaseOrder->linkedRfq();
        $vendorQuotations = $linkedRfq
            ? $linkedRfq->quotations()->with('vendor')->get()
            : ($purchaseOrder->quotation ? collect([$purchaseOrder->quotation->load('vendor')]) : collect());

        // Legacy POs that were marked Submitted without starting the approval workflow.
        if ($purchaseOrder->status === PurchaseOrderStatus::Submitted
            && empty($purchaseOrder->assigned_approver_id)
            && $purchaseOrder->approvalSteps->isEmpty()
            && $user
        ) {
            try {
                $purchaseOrder = $this->poApprovals->submit($purchaseOrder, $user);
                $purchaseOrder->load([
                    'approvalSteps.approver',
                    'approvalSteps.actedBy',
                    'assignedApprover',
                    'payments.createdBy',
                ]);
            } catch (ProcurementException $e) {
                return view('purchase-orders.show', [
                    'purchaseOrder' => $purchaseOrder,
                    'timeline' => [],
                    'canActOnCurrentStep' => false,
                    'isCreator' => (int) $purchaseOrder->created_by === (int) $user->id,
                    'workflowError' => $e->getMessage(),
                    'canMakePayment' => $this->userCanMakePayment($user),
                    'paymentMethods' => PurchaseOrderPayment::METHODS,
                    'paymentSummary' => $this->poPayments->summary($purchaseOrder),
                    'vendorQuotations' => $vendorQuotations,
                ]);
            }
        }

        $timeline = $this->poApprovals->timeline($purchaseOrder);

        $isCreator = (int) $purchaseOrder->created_by === (int) $user?->id;
        $canActOnCurrentStep = $user
            ? $this->poApprovals->actorCanActOnPo($purchaseOrder, $user)
            : false;
        $awaitingAssigneeLabel = $purchaseOrder->isPendingApproval()
            ? $this->poApprovals->currentAssigneeDisplay($purchaseOrder)
            : null;

        $canMakePayment = $this->userCanMakePayment($user);
        $paymentMethods = PurchaseOrderPayment::METHODS;
        $paymentSummary = $this->poPayments->summary($purchaseOrder);

        return view('purchase-orders.show', compact(
            'purchaseOrder',
            'timeline',
            'canActOnCurrentStep',
            'isCreator',
            'awaitingAssigneeLabel',
            'canMakePayment',
            'paymentMethods',
            'paymentSummary',
            'vendorQuotations'
        ));
    }

    public function paymentSummary(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->assertCanMakePayment();
        $purchaseOrder->load('vendor');

        return response()->json([
            'success' => true,
            'summary' => $this->poPayments->summary($purchaseOrder),
        ]);
    }

    public function storePayment(StorePurchaseOrderPaymentRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->assertCanMakePayment();

        try {
            $payment = $this->poPayments->recordPayment(
                $purchaseOrder,
                $request->user(),
                $request->validated(),
                $request->file('attachment')
            );
        } catch (ProcurementException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $purchaseOrder->refresh()->load(['vendor', 'payments.createdBy']);
        $summary = $this->poPayments->summary($purchaseOrder);
        $remFormatted = number_format((float) $summary['remaining'], 2);

        return response()->json([
            'success' => true,
            'message' => "Payment saved successfully! Remaining Balance: ₹{$remFormatted}",
            'summary' => $summary,
            'payment' => [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date?->format('d M Y'),
                'payment_amount' => number_format((float) $payment->payment_amount, 2),
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number ?: '--',
                'remarks' => $payment->remarks ?: '--',
                'created_by' => $payment->createdBy->name ?? '--',
                'created_at' => $payment->created_at?->format('d M Y h:i A'),
                'has_attachment' => $payment->hasAttachment(),
                'view_url' => $payment->hasAttachment()
                    ? route('purchase-orders.payments.attachment', [$payment, 'view'])
                    : null,
                'download_url' => $payment->hasAttachment()
                    ? route('purchase-orders.payments.attachment', [$payment, 'download'])
                    : null,
            ],
        ]);
    }

    public function paymentAttachment(PurchaseOrderPayment $payment, string $mode = 'view')
    {
        $this->assertCanView();

        abort_unless($payment->hasAttachment(), 404, 'Attachment not found.');

        $path = $payment->attachment;
        $disk = Storage::disk('public');

        if ($mode === 'download') {
            return $disk->download($path, $payment->attachmentName());
        }

        return $disk->response($path);
    }

    public function submit(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanCreate();
        $request->validate(['remarks' => 'nullable|string|max:2000']);

        try {
            $this->poApprovals->submit($purchaseOrder, $request->user(), $request->input('remarks'));
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order submitted for approval.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanApprove();
        $request->validate(['remarks' => 'nullable|string|max:2000']);

        try {
            $this->poApprovals->approve($purchaseOrder, $request->user(), $request->input('remarks'));
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order approved.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanApprove();
        $request->validate(['remarks' => 'required|string|max:2000']);

        try {
            $this->poApprovals->reject($purchaseOrder, $request->user(), $request->input('remarks'));
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order rejected.');
    }

    public function sendBack(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanApprove();
        $request->validate(['remarks' => 'nullable|string|max:2000']);

        try {
            $this->poApprovals->sendBack($purchaseOrder, $request->user(), $request->input('remarks'));
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order sent back for revision.');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->assertCanEdit();

        if (! $purchaseOrder->canBeEdited()) {
            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot be edited.');
        }

        $purchaseOrder->load(['items', 'vendor', 'purchaseRequisition.indent.department', 'purchaseRequisition.indent.items']);

        return view('purchase-orders.create', $this->purchaseOrderFormData($purchaseOrder));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanEdit();

        if (! $purchaseOrder->canBeEdited()) {
            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot be updated.');
        }

        $data = $request->validated();
        $wasDraft = $purchaseOrder->statusEnum() === PurchaseOrderStatus::Draft;
        $submit = $wasDraft && ($data['action'] ?? '') === 'submit';

        try {
            $po = $this->orders->update($purchaseOrder, $request->user(), $data);

            if ($submit) {
                $po = $this->poApprovals->submit($po, $request->user(), $data['remarks'] ?? $data['notes'] ?? null);
            }
        } catch (ProcurementException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $submit
            ? 'Purchase order submitted for approval.'
            : 'Purchase order updated successfully.';

        return redirect()
            ->route('purchase-orders.show', $po)
            ->with('success', $message);
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $this->assertCanView();

        $purchaseOrder->load(['vendor', 'items', 'indent', 'department', 'store']);

        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanCancel();

        $request->validate(['remarks' => 'nullable|string|max:2000']);

        try {
            $this->orders->cancel($purchaseOrder, $request->user(), $request->input('remarks'));
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order cancelled.');
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->assertCanDelete();

        try {
            $this->orders->delete($purchaseOrder, $request->user());
        } catch (ProcurementException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    private function assertCanView(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && ($user->can('purchase-order.view') || $user->can('purchase-orders:view')),
            403,
            'You do not have permission to view purchase orders.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseOrderFormData(?PurchaseOrder $purchaseOrder = null): array
    {
        $availableRequisitions = PurchaseRequisition::query()
            ->availableForPurchaseOrder()
            ->with(['indent.department', 'indent.items', 'requisitionStore', 'indent.fromStore', 'indent.toStore'])
            ->latest('created_at')
            ->get();

        if ($purchaseOrder?->pr_id) {
            $linkedPr = PurchaseRequisition::query()
                ->with(['indent.department', 'indent.items', 'requisitionStore', 'indent.fromStore', 'indent.toStore'])
                ->find($purchaseOrder->pr_id);

            if ($linkedPr && ! $availableRequisitions->contains('id', $linkedPr->id)) {
                $availableRequisitions->prepend($linkedPr);
            }
        }

        $vendors = Vendor::query()
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'status']);
        if ($purchaseOrder?->vendor_id && ! $vendors->contains('id', $purchaseOrder->vendor_id)) {
            $linkedVendor = Vendor::query()->find($purchaseOrder->vendor_id, ['id', 'name', 'status']);
            if ($linkedVendor) {
                $vendors->prepend($linkedVendor);
            }
        }

        $departments = Department::query()->orderBy('department_name')->get();
        $categories = AssetCategory::query()->active()->whereNull('parent_id')->orderBy('category_name')->get();
        $subCategories = AssetCategory::query()->active()->whereNotNull('parent_id')->orderBy('category_name')->get();
        $itemTypes = ItemType::cases();
        $poTypes = ['Regular', 'Standard', 'Emergency', 'Capital', 'Service', 'Consumable'];

        return compact(
            'availableRequisitions',
            'vendors',
            'departments',
            'categories',
            'subCategories',
            'itemTypes',
            'poTypes',
            'purchaseOrder',
        );
    }

    private function assertCanCreate(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && (
                $user->can('purchase-order.create')
                || $user->can('purchase-orders:create')
                || $user->can('purchase-order.edit')
                || $user->can('purchase-orders:edit')
            ),
            403,
            'You do not have permission to create or submit purchase orders.'
        );
    }

    private function assertCanEdit(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && (
                $user->can('purchase-order.edit')
                || $user->can('purchase-orders:edit')
            ),
            403,
            'You do not have permission to edit purchase orders.'
        );
    }

    private function assertCanApprove(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && (
                $user->can('purchase-order.approve')
                || $user->can('purchase-orders:approve')
                || $user->can('purchase-order.reject')
                || $user->can('purchase-order.send-back')
                || $user->can('purchase-order.view')
                || $user->can('purchase-orders:view')
            ),
            403,
            'You do not have permission to approve purchase orders.'
        );
    }

    private function assertCanCancel(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && $user->can('purchase-order.cancel'),
            403,
            'You do not have permission to cancel purchase orders.'
        );
    }

    private function assertCanDelete(): void
    {
        $user = Auth::user();

        if ($this->isPrivileged($user)) {
            return;
        }

        abort_unless(
            $user && (
                $user->can('purchase-order.delete')
                || $user->can('purchase-orders:delete')
            ),
            403,
            'You do not have permission to delete purchase orders.'
        );
    }

    private function assertCanMakePayment(): void
    {
        abort_unless(
            $this->userCanMakePayment(Auth::user()),
            403,
            'You do not have permission to record purchase order payments.'
        );
    }

    private function userCanMakePayment(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        if ($user->can('purchase-order.payment') || $user->can('purchase-orders:payment') || $user->can('purchase-order.pay')) {
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

    private function isPrivileged(?\App\Models\User $user): bool
    {
        try {
            return (bool) $user?->hasAnyRole(['superadmin', 'Super Admin']);
        } catch (\Throwable) {
            return false;
        }
    }
}
