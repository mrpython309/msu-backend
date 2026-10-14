<?php

namespace App\Exports;

use App\Models\MsuPurchaseOrder;
use App\Support\AmountInWords;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithTitle;

class MsuPurchaseOrderInvoiceExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly MsuPurchaseOrder $msuPurchaseOrder)
    {
    }

    public function view(): View
    {
        $this->msuPurchaseOrder->load(['vendor', 'branch', 'paymentTerm', 'miscellaneousCharge', 'shippingDetail', 'charges', 'items.itemMaster.unit']);

        return view('msu-purchase-orders.pdf', [
            'msuPurchaseOrder' => $this->msuPurchaseOrder,
            'amountInWords' => AmountInWords::indianRupees($this->msuPurchaseOrder->grand_total),
            'isExcel' => true,
        ]);
    }

    public function title(): string
    {
        $title = str_replace(['/', '\\', '*', '?', '[', ']', ':'], '-', (string) $this->msuPurchaseOrder->po_number);
        return substr($title, 0, 31);
    }
}
