<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MsuWorkInvoiceRequest;
use App\Models\MsuWorkInvoice;
use App\Models\MsuWorkOrder;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MsuWorkInvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = MsuWorkInvoice::with(['workOrder', 'vendor', 'branch']);

        if ($request->filled('wi_number')) {
            $query->where('wi_number', 'like', '%' . $request->wi_number . '%');
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if (session()->has('global_company_id') || $request->filled('company_id')) {
            $companyId = $request->input('company_id', session('global_company_id'));
            $query->whereHas('branch', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        $perPage = $request->input('per_page', 20);
        $invoices = $query->orderByDesc('id')->paginate($perPage);

        return $this->successWithPagination($invoices);
    }

    public function store(MsuWorkInvoiceRequest $request, DocumentNumberGenerator $numberGenerator)
    {
        $wi = DB::transaction(function () use ($request, $numberGenerator) {
            $validated = $request->validated();
            
            $workOrder = MsuWorkOrder::with('branch.company')->findOrFail($validated['msu_work_order_id']);

            $branchCode = $workOrder->branch ? ($workOrder->branch->code ?: $workOrder->branch->name) : 'BRN';
            $companyCode = $workOrder->branch?->company?->code ?: 'MSULLP';
            
            $wiNumber = $numberGenerator->nextMsuWiNumber($companyCode, $branchCode, $validated['invoice_date'] ?? now());

            $documentPath = null;
            if ($request->hasFile('document_path')) {
                $documentPath = $request->file('document_path')->store('work-invoices', 'public');
            }

            $wi = MsuWorkInvoice::create([
                'wi_number' => $wiNumber,
                'invoice_date' => $validated['invoice_date'],
                'invoice_number' => $validated['invoice_number'] ?? null,
                'msu_work_order_id' => $workOrder->id,
                'vendor_id' => $validated['vendor_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'document_path' => $documentPath,
                'status' => 'partially_passed',
                'created_by' => auth()->id(),
            ]);

            $totalValue = 0;
            $totalTax = 0;
            $touchedItems = [];

            foreach ($validated['items'] as $itemData) {
                $woItem = \App\Models\MsuWorkOrderItem::query()
                    ->where('msu_work_order_id', $workOrder->id)
                    ->lockForUpdate()
                    ->findOrFail($itemData['msu_work_order_item_id']);
                $woItem->load('invoiceItems');

                $qty = (float) $itemData['quantity'];
                $price = isset($itemData['price']) && $itemData['price'] !== '' && $itemData['price'] !== null
                    ? (float) $itemData['price']
                    : (float) $woItem->price;

                $line = $woItem->buildInvoiceLine($qty, $price);
                if ($line === null) {
                    continue;
                }

                $taxPercent = (float) $woItem->tax;
                $taxAmt = round($line['gross'] * ($taxPercent / 100), 2);
                $net = round($line['gross'] + $taxAmt, 2);

                $wi->items()->create([
                    'msu_work_order_item_id' => $woItem->id,
                    'work_description' => $woItem->work_description,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'tax' => $taxPercent,
                    'net_value' => $net,
                ]);

                $touchedItems[$woItem->id] = $woItem;
                $totalValue += $line['gross'];
                $totalTax += $taxAmt;
            }

            if ($touchedItems === []) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'Nothing left to invoice for this work order.',
                ]);
            }

            foreach ($touchedItems as $woItem) {
                $woItem->syncReceivedQuantityFromInvoices();
            }

            $isInterState = false;
            $wi->loadMissing(['branch', 'vendor']);
            if ($wi->branch && $wi->vendor) {
                $branchState = trim(strtolower((string)$wi->branch->state));
                $vendorState = trim(strtolower((string)$wi->vendor->state));
                if ($branchState === '') $branchState = 'karnataka';
                if ($vendorState === '') $vendorState = 'karnataka';
                if ($branchState !== $vendorState) {
                    $isInterState = true;
                }
            }

            $isFirstInvoice = $workOrder->invoices()->count() === 1;

            $miscBaseAmount = 0;
            $miscTaxAmount = 0;
            if ($isFirstInvoice) {
                if ($workOrder->charges && $workOrder->charges->count() > 0) {
                    foreach ($workOrder->charges as $c) {
                        $miscBaseAmount += (float)$c->amount;
                        $miscTaxAmount += (float)$c->tax_amount;
                    }
                } else {
                    $miscBaseAmount = (float) $workOrder->miscellaneous_charges;
                    $miscTaxAmount = (float) $workOrder->miscellaneous_tax;
                }
            }

            $totalTax += $miscTaxAmount;

            $halfTax = round($totalTax / 2, 2);
            if ($isInterState) {
                $wi->igst = round($totalTax, 2);
            } else {
                $wi->sgst = $halfTax;
                $wi->cgst = round($totalTax - $halfTax, 2);
            }

            $wi->total_value = round($totalValue, 2);
            $wi->miscellaneous_charges = round($miscBaseAmount, 2);
            $wi->grand_total = round($totalValue + $miscBaseAmount + $totalTax, 2);
            $wi->save();

            $workOrder->syncStatusFromInvoices();
            $workOrder->refresh();
            $wi->update(['status' => $workOrder->statusEnum()->value]);

            return $wi->load(['workOrder', 'vendor', 'branch', 'items']);
        });

        return $this->success($wi);
    }

    public function show(string $id)
    {
        $wi = MsuWorkInvoice::with(['workOrder', 'vendor', 'branch', 'items.workOrderItem'])->find($id);

        if (!$wi) {
            return $this->error('Work Invoice not found');
        }

        return $this->success($wi);
    }

    public function destroy(string $id)
    {
        $msuWorkInvoice = MsuWorkInvoice::find($id);

        if (!$msuWorkInvoice) {
            return $this->error('Work Invoice not found');
        }

        DB::transaction(function () use ($msuWorkInvoice) {
            $msuWorkInvoice->load('items.workOrderItem');
            $workOrder = $msuWorkInvoice->workOrder;
            $woItems = $msuWorkInvoice->items
                ->map(fn ($item) => $item->workOrderItem)
                ->filter()
                ->unique('id');

            $msuWorkInvoice->delete();

            foreach ($woItems as $woItem) {
                $woItem->syncReceivedQuantityFromInvoices();
            }

            if ($workOrder) {
                $workOrder->syncStatusFromInvoices();
            }
        });

        return $this->success('Work Invoice deleted successfully');
    }
}
