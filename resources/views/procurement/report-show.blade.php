@extends('layouts/contentNavbarLayout')

@section('title', 'Procurement Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ ucwords(str_replace('-', ' ', $report)) }}</h4>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-success btn-sm"
           href="{{ route('procurement.reports.show', ['report' => $report, 'format' => 'excel']) }}">
            Export Excel
        </a>
        <a class="btn btn-outline-primary btn-sm"
           href="{{ route('procurement.reports.show', ['report' => $report, 'format' => 'pdf']) }}">
            Export PDF
        </a>
        <a class="btn btn-secondary btn-sm" href="{{ route('procurement.reports') }}">Back</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Details</th>
                    <th>Status / Metric</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>
                            @if ($report === 'vendor-performance')
                                {{ $record->name }}
                            @elseif ($report === 'asset-creation')
                                {{ $record->asset_code }}
                            @else
                                {{ $record->indent_no ?? $record->pr_no ?? $record->rfq_no ?? $record->po_no ?? $record->grn_no ?? ('#'.$record->id) }}
                            @endif
                        </td>
                        <td>
                            @if ($report === 'vendor-performance')
                                Quotes: {{ $record->quotations_count }} · POs: {{ $record->purchase_orders_count }} · GRNs: {{ $record->grns_count }}
                            @elseif ($report === 'asset-creation')
                                {{ $record->asset_name }} · GRN {{ $record->grnItem->grn->grn_no ?? '--' }}
                            @else
                                {{ $record->department->department_name ?? $record->vendor->name ?? $record->indent->indent_no ?? $record->purchaseRequisition->pr_no ?? '--' }}
                            @endif
                        </td>
                        <td>
                            @if ($report === 'vendor-performance')
                                Avg Total: {{ number_format((float) ($record->avg_quote_total ?? 0), 2) }}
                            @else
                                {{ $record->status instanceof \BackedEnum ? $record->status->value : ($record->status ?? '--') }}
                            @endif
                        </td>
                        <td>{{ $record->created_at?->format('d M Y') ?? '--' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
