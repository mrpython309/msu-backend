<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetReportExportService
{
    private const COLUMN_ORDER = [
        'sub_category_id',
        'serial_no',
        'asset_code',
        'status',
        'asset_type_id',
        'company_id',
        'branch_id',
        'brand_id',
        'location_id',
        'vendor_id',
        'purchase_date',
        'purchase_cost',
        'condition',
        'operational_status',
    ];

    private const COLUMN_LABELS = [
        'sub_category_id' => 'Category',
        'serial_no' => 'Serial No.',
        'asset_code' => 'Asset Code',
        'status' => 'Status',
        'asset_type_id' => 'Asset Type',
        'company_id' => 'Company',
        'branch_id' => 'Branch',
        'brand_id' => 'Brand',
        'location_id' => 'Physical Location',
        'vendor_id' => 'Vendor',
        'purchase_date' => 'Purchase Date',
        'purchase_cost' => 'Purchase Cost',
        'condition' => 'Condition',
        'operational_status' => 'Operational Status',
    ];

    public function headers(array $tableColumns): array
    {
        $headers = ['#', 'Name'];

        foreach (self::COLUMN_ORDER as $column) {
            if (in_array($column, $tableColumns, true)) {
                $headers[] = self::COLUMN_LABELS[$column];
            }
        }

        return $headers;
    }

    public function row(Asset $asset, int $index, array $tableColumns): array
    {
        $row = [$index, $asset->asset_name];

        foreach (self::COLUMN_ORDER as $column) {
            if (! in_array($column, $tableColumns, true)) {
                continue;
            }

            $row[] = $this->cellValue($asset, $column);
        }

        return $row;
    }

    public function downloadCsv(Collection $assets, array $tableColumns, string $filename): StreamedResponse
    {
        $headers = $this->headers($tableColumns);

        return response()->streamDownload(function () use ($assets, $tableColumns, $headers) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);

            foreach ($assets as $index => $asset) {
                fputcsv($handle, $this->row($asset, $index + 1, $tableColumns));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function cellValue(Asset $asset, string $column): string
    {
        return match ($column) {
            'sub_category_id' => (string) ($asset->subCategory?->category_name ?? ''),
            'serial_no' => (string) ($asset->serial_no ?? ''),
            'asset_code' => (string) ($asset->asset_code ?? ''),
            'status' => (string) ($asset->status?->label() ?? ''),
            'asset_type_id' => (string) ($asset->assetType?->asset_type_name ?? ''),
            'company_id' => (string) ($asset->company?->name ?? ''),
            'branch_id' => (string) ($asset->branch?->name ?? ''),
            'brand_id' => (string) ($asset->brand?->brand_name ?? ''),
            'location_id' => (string) ($asset->location?->location_name ?? ''),
            'vendor_id' => (string) ($asset->vendor?->name ?? ''),
            'purchase_date' => $asset->purchase_date?->format('d M Y') ?? '',
            'purchase_cost' => $asset->purchase_cost !== null
                ? number_format((float) $asset->purchase_cost, 2, '.', '')
                : '',
            'condition' => strip_tags((string) ($asset->condition_label ?? '')),
            'operational_status' => strip_tags((string) ($asset->operational_status_label ?? '')),
            default => '',
        };
    }
}
