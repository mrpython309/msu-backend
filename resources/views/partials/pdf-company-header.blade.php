@if(!empty($pdfCompany) || !empty($pdfCompanyLogoDataUri))
@php
    $addressLine = collect([
        $pdfCompany?->address_line_1,
        $pdfCompany?->address_line_2,
    ])->filter()->implode(', ');

    $locationLine = collect([
        $pdfCompany?->city,
        $pdfCompany?->state,
        $pdfCompany?->country,
        $pdfCompany?->pincode,
    ])->filter()->implode(', ');
@endphp
<table style="width: 100%; border-collapse: collapse; margin: 0 0 14px 0; padding-bottom: 10px;">
    <tr>
        <td style="vertical-align: middle; padding: 0 16px 10px 0;">
            @if(!empty($pdfCompanyLogoDataUri))
                <img src="{{ $pdfCompanyLogoDataUri }}" alt="Company Logo" style="max-height: 130px; max-width: 300px; height: auto; width: auto; display: block;">
            @endif
        </td>
        @if(!empty($barcodeHtml))
        <td style="vertical-align: middle; padding: 0 0 10px 16px; text-align: right;">
            {!! $barcodeHtml !!}
        </td>
        @endif
    </tr>
</table>
@endif
