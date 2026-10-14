<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardExport implements WithMultipleSheets
{
    public function __construct(private readonly array $payload) {}

    public function sheets(): array
    {
        return [
            new DashboardSummarySheet($this->payload),
            new DashboardPurchaseOrderSheet($this->payload),
            new DashboardWorkOrderSheet($this->payload),
            new DashboardGrnSheet($this->payload),
            new DashboardStockMovementSheet($this->payload),
        ];
    }
}

class DashboardSummarySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return [
            ['Dashboard', 'Company', $this->payload['companyName'], 'Branch', $this->payload['branchName']],
            ['Metric', 'Value'],
        ];
    }

    public function array(): array
    {
        $progress = $this->payload['poApprovalProgress'];
        $rows = [
            ['Purchase Orders', $this->payload['poCount']],
            ['PO Pending Approval', $this->payload['pendingPoApprovals']],
            ['Goods Receipts', $this->payload['grnCount']],
            ['GRNs Pending Stock', $this->payload['pendingStockGrns']],
            ['Active Items', $this->payload['itemCount']],
            ['Current Stock', round((float) $this->payload['currentStockQty'], 2)],
            ['Work Orders', $this->payload['woCount']],
            ['WO Pending Approval', $this->payload['pendingWoApprovals']],
            ['Pending Approvals', $this->payload['pendingApprovals']],
            ['PO Spend', round((float) $this->payload['totalPoSpend'], 2)],
            ['Work Order Spend', round((float) $this->payload['totalWoSpend'], 2)],
            ['Total Order Value', round((float) $this->payload['totalPoSpend'] + (float) $this->payload['totalWoSpend'], 2)],
            [],
            ['Active Purchase Order', $progress['po_no']],
            ['Vendor', $progress['vendor_name']],
            ['Status', $progress['status']],
            ['Progress %', $progress['progress']],
            ['Estimated Value', round((float) $progress['amount'], 2)],
            [],
            ['Month', 'PO Spend', 'Work Order Spend'],
        ];

        foreach ($this->payload['spendTrend'] as $point) {
            $rows[] = [
                $point['label'],
                round((float) $point['po_total'], 2),
                round((float) $point['wo_total'], 2),
            ];
        }

        return $rows;
    }
}

class DashboardPurchaseOrderSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Purchase Orders';
    }

    public function headings(): array
    {
        return ['PO Number', 'Vendor', 'Date', 'Amount', 'Status'];
    }

    public function array(): array
    {
        return $this->payload['recentPurchaseOrders']->map(function ($po) {
            return [
                $po->po_number,
                $po->vendor?->name ?? 'N/A',
                $po->po_date?->format('d-m-Y') ?? 'N/A',
                round((float) $po->grand_total, 2),
                $po->statusEnum()->label(),
            ];
        })->all();
    }
}

class DashboardWorkOrderSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Work Orders';
    }

    public function headings(): array
    {
        return ['WO Number', 'Vendor', 'Date', 'Amount', 'Status'];
    }

    public function array(): array
    {
        return $this->payload['recentWorkOrders']->map(function ($wo) {
            return [
                $wo->wo_number,
                $wo->vendor?->name ?? 'N/A',
                $wo->wo_date?->format('d-m-Y') ?? 'N/A',
                round((float) $wo->grand_total, 2),
                $wo->statusEnum()->label(),
            ];
        })->all();
    }
}

class DashboardGrnSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Goods Receipts';
    }

    public function headings(): array
    {
        return ['GRN Number', 'PO Number', 'Vendor', 'Date', 'Status'];
    }

    public function array(): array
    {
        return $this->payload['recentGrns']->map(function ($grn) {
            return [
                $grn->grn_number,
                $grn->purchaseOrder?->po_number ?? 'N/A',
                $grn->vendor?->name ?? 'N/A',
                $grn->grn_date?->format('d-m-Y') ?? 'N/A',
                $grn->statusEnum()->label(),
            ];
        })->all();
    }
}

class DashboardStockMovementSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Stock Movements';
    }

    public function headings(): array
    {
        return ['Item', 'Type', 'Qty', 'Date'];
    }

    public function array(): array
    {
        return $this->payload['recentStockMovements']->map(function ($movement) {
            $type = match ($movement->transaction_type) {
                'in' => 'In',
                'out' => 'Out',
                default => 'Adj',
            };

            return [
                $movement->itemMaster?->item_name ?? 'Unknown Item',
                $type,
                round((float) $movement->quantity, 2),
                $movement->created_at?->timezone('Asia/Kolkata')->format('d-m-Y') ?? 'N/A',
            ];
        })->all();
    }
}
