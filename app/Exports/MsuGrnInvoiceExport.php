<?php

namespace App\Exports;

use App\Models\MsuGrn;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class MsuGrnInvoiceExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithTitle
{
    protected $msuGrn;

    public function __construct(MsuGrn $msuGrn)
    {
        $this->msuGrn = $msuGrn;
    }

    public function view(): View
    {
        return view('msu-grns.pdf', [
            'grn' => $this->msuGrn,
            'isExcel' => true
        ]);
    }

    public function title(): string
    {
        $title = str_replace(['/', '\\', '*', '?', '[', ']', ':'], '-', (string) $this->msuGrn->grn_number);
        return substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 45,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
}
