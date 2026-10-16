<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class ReportPdfExportService
{
    public function download(string $view, array $data, string $filename, string $orientation = 'landscape', string $paper = 'A4'): Response
    {
        $logoService = app(CompanyLogoService::class);
        $pdfCompany = $logoService->resolveCompany($data);
        $pdfCompanyLogoDataUri = $logoService->pdfDataUri($pdfCompany, $data);

        $data['pdfCompany'] = $pdfCompany;
        $data['pdfCompanyLogoDataUri'] = $pdfCompanyLogoDataUri;

        $html = View::make($view, $data)->render();

        if (empty($data['skipCompanyHeader']) && ($pdfCompany || $pdfCompanyLogoDataUri)) {
            $headerHtml = View::make('partials.pdf-company-header', [
                'pdfCompany' => $pdfCompany,
                'pdfCompanyLogoDataUri' => $pdfCompanyLogoDataUri,
                'barcodeHtml' => $data['barcodeHtml'] ?? null,
            ])->render();

            $html = preg_replace('/<body([^>]*)>/i', '<body$1>'.$headerHtml, $html, 1);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename) ?: 'report.pdf';
        if (! str_ends_with(strtolower($safeFilename), '.pdf')) {
            $safeFilename .= '.pdf';
        }

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$safeFilename.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
