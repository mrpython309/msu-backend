<?php

namespace App\Exports;

use App\Models\MaterialIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaterialIssueExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(protected MaterialIssue $materialIssue) {}

    public function view(): View
    {
        return view('material_issues.pdf', [
            'materialIssue' => $this->materialIssue,
            'isExcel' => true,
        ]);
    }

    public function title(): string
    {
        return str_replace(['/', '\\', '*', '?', '[', ']'], '-', $this->materialIssue->mi_number);
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
            'D' => 14,
            'E' => 16,
            'F' => 16,
        ];
    }
}
