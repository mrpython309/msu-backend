<?php

namespace App\Http\Controllers;

use App\Http\Requests\MsuWorkInvoiceRequest;
use App\Models\MsuWorkInvoice;
use App\Models\MsuWorkOrder;
use App\Services\Procurement\DocumentNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class MsuWorkInvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:msu-work-invoice:view', only: ['index', 'show']),
            new Middleware('permission:msu-work-invoice:create', only: ['create', 'store']),
            new Middleware('permission:msu-work-invoice:edit', only: ['edit', 'update']),
            new Middleware('permission:msu-work-invoice:delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        // No standalone Work Invoice list — manage invoices from Work Orders.
        return redirect()->route('msu-work-orders.index');
    }

    public function create(Request $request)
    {
        $woId = $request->input('msu_work_order_id');
        if (!$woId) {
            return redirect()->route('msu-work-orders.index')->with('error', 'Select a Work Order first.');
        }

        $workOrder = MsuWorkOrder::with(['vendor', 'branch', 'items.invoiceItems'])->findOrFail($woId);

        if ($workOrder->statusEnum() === \App\Enums\Procurement\MsuWorkOrderStatus::Cancelled) {
            return redirect()->route('msu-work-orders.show', $workOrder)->with('error', 'Cannot invoice a cancelled Work Order.');
        }

        $companyCode = 'MSULLP';
        if (session()->has('global_company_id')) {
            $company = \App\Models\Company::find(session('global_company_id'));
            if ($company) {
                $companyCode = $company->code ?: 'MSULLP';
            }
        }
        $branchCode = $workOrder->branch ? ($workOrder->branch->code ?: $workOrder->branch->name) : 'BRN';
        $previewWiNumber = app(DocumentNumberGenerator::class)->previewMsuWiNumber($companyCode, $branchCode, now());

        return view('msu-work-invoices.create', compact('workOrder', 'previewWiNumber'));
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

            return $wi;
        });

        return redirect()
            ->route('msu-work-invoices.show', $wi)
            ->with('success', 'Work Invoice created successfully.');
    }

    public function show(MsuWorkInvoice $msuWorkInvoice)
    {
        $msuWorkInvoice->load(['workOrder.charges', 'vendor', 'branch', 'items.workOrderItem']);
        return view('msu-work-invoices.show', compact('msuWorkInvoice'));
    }

    public function destroy(MsuWorkInvoice $msuWorkInvoice)
    {
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

        return redirect()
            ->route('msu-work-orders.index')
            ->with('success', 'Work Invoice deleted successfully.');
    }

    public function downloadDocument(MsuWorkInvoice $msuWorkInvoice)
    {
        if ($msuWorkInvoice->document_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($msuWorkInvoice->document_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($msuWorkInvoice->document_path);
        }
        abort(404, 'Document not found.');
    }

    public function pdf(MsuWorkInvoice $msuWorkInvoice, \App\Services\ReportPdfExportService $pdfExport)
    {
        $msuWorkInvoice->load(['workOrder', 'vendor', 'branch', 'items.workOrderItem']);
        $filename = str_replace(['/', '\\'], '-', $msuWorkInvoice->wi_number) . '.pdf';

        return $pdfExport->download(
            'msu-work-invoices.pdf',
            [
                'msuWorkInvoice' => $msuWorkInvoice,
                'amountInWords' => \App\Support\AmountInWords::indianRupees($msuWorkInvoice->grand_total),
            ],
            $filename,
            'portrait'
        );
    }
}
