<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterListExportService
{
    public function excel(Collection $rows, array $headings, string $basename): BinaryFileResponse
    {
        $export = new class($rows->values(), $headings) implements FromCollection, WithHeadings
        {
            public function __construct(
                private readonly Collection $rows,
                private readonly array $headings
            ) {}

            public function collection(): Collection
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return Excel::download($export, $this->filename($basename, 'xlsx'));
    }

    public function pdf(string $title, array $headings, iterable $rows, string $basename): Response
    {
        $body = collect($rows)->map(function ($row) {
            return array_values(is_array($row) ? $row : (array) $row);
        })->all();

        return app(ReportPdfExportService::class)->download(
            'master.export-list',
            [
                'title' => $title,
                'headings' => $headings,
                'rows' => $body,
            ],
            $this->filename($basename, 'pdf'),
            'landscape'
        );
    }

    private function filename(string $basename, string $extension): string
    {
        return $basename.'-'.now()->format('Ymd_His').'.'.$extension;
    }
}
