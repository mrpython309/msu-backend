<?php

namespace App\Exports;

use App\Models\MaterialRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaterialRequestExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(protected MaterialRequest $materialRequest) {}

    public function view(): View
    {
        return view('material_requests.pdf', [
            'materialRequest' => $this->materialRequest,
            'isExcel' => true,
        ]);
    }

    public function title(): string
    {
        $title = str_replace(['/', '\\', '*', '?', '[', ']', ':'], '-', (string) $this->materialRequest->mr_number);
        return substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 40,
            'C' => 14,
            'D' => 16,
            'E' => 16,
        ];
    }
}
