<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemDashboardExport implements WithMultipleSheets
{
    public function __construct(private readonly array $payload) {}

    public function sheets(): array
    {
        return [
            new ItemDashboardCategorySheet($this->payload),
            new ItemDashboardLowStockSheet($this->payload['lowStockItems']),
            new ItemDashboardActivitySheet($this->payload['recentActivities']),
        ];
    }
}

class ItemDashboardCategorySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $payload) {}

    public function title(): string
    {
        return 'Category Stock Summary';
    }

    public function headings(): array
    {
        return [
            ['Item Dashboard', 'From', $this->payload['fromDate'], 'To', $this->payload['toDate']],
            [
                'Category',
                'Opening Qty',
                'Opening Value',
                'Received Qty',
                'Received Value',
                'Issued Qty',
                'Issued Value',
                'Closing Qty',
                'Closing Value',
            ],
        ];
    }

    public function array(): array
    {
        $rows = [];
        $totals = [
            'opening_qty' => 0,
            'opening_val' => 0,
            'received_qty' => 0,
            'received_val' => 0,
            'issued_qty' => 0,
            'issued_val' => 0,
            'closing_qty' => 0,
            'closing_val' => 0,
        ];

        foreach ($this->payload['categorySummaries'] as $summary) {
            $rows[] = [
                $summary['name'],
                round((float) $summary['opening_qty'], 3),
                round((float) $summary['opening_val'], 2),
                round((float) $summary['received_qty'], 3),
                round((float) $summary['received_val'], 2),
                round((float) $summary['issued_qty'], 3),
                round((float) $summary['issued_val'], 2),
                round((float) $summary['closing_qty'], 3),
                round((float) $summary['closing_val'], 2),
            ];

            foreach ($totals as $key => $value) {
                $totals[$key] = $value + (float) $summary[$key];
            }
        }

        $rows[] = [
            'Total',
            round($totals['opening_qty'], 3),
            round($totals['opening_val'], 2),
            round($totals['received_qty'], 3),
            round($totals['received_val'], 2),
            round($totals['issued_qty'], 3),
            round($totals['issued_val'], 2),
            round($totals['closing_qty'], 3),
            round($totals['closing_val'], 2),
        ];

        return $rows;
    }
}

class ItemDashboardLowStockSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly Collection $items) {}

    public function title(): string
    {
        return 'Low Stock Alert';
    }

    public function headings(): array
    {
        return ['Item', 'Item Code', 'Current Stock', 'Threshold', 'Status'];
    }

    public function array(): array
    {
        return $this->items->map(fn ($item) => [
            $item->item_name,
            $item->item_code,
            round((float) $item->current_stock, 3),
            round((float) $item->threshold_quantity, 3),
            'Low Stock',
        ])->all();
    }
}

class ItemDashboardActivitySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly array $activities) {}

    public function title(): string
    {
        return 'Recent Item Activity';
    }

    public function headings(): array
    {
        return ['S.No', 'Date Time', 'Item', 'Action'];
    }

    public function array(): array
    {
        $rows = [];

        foreach (array_values($this->activities) as $index => $activity) {
            $rows[] = [
                $index + 1,
                \Carbon\Carbon::parse($activity['date_time'])->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
                $activity['item_name'],
                $activity['action'],
            ];
        }

        return $rows;
    }
}
