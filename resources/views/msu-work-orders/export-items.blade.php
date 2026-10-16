<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WO Items Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Work Order Items</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $items->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S/No</th>
                <th>Company</th>
                <th>Branch</th>
                <th>WO Number</th>
                <th>WO Date</th>
                <th>Work Description</th>
                <th>HSN</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Gross Value</th>
                <th class="text-end">Tax %</th>
                <th class="text-end">Tax Value</th>
                <th class="text-end">Net Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php $wo = $item->workOrder; @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $wo?->branch?->company?->name ?? '--' }}</td>
                <td>{{ $wo?->branch?->name ?? '--' }}</td>
                <td>{{ $wo?->wo_number ?? '--' }}</td>
                <td>{{ $wo?->wo_date?->format('d-m-Y') ?? '--' }}</td>
                <td>{{ $item->work_description ?: '--' }}</td>
                <td>{{ $item->hsn ?: '--' }}</td>
                <td class="text-end">{{ number_format((float) $item->quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->price, 2) }}</td>
                <td class="text-end">{{ number_format($item->gross_value, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->tax, 2) }}</td>
                <td class="text-end">{{ number_format($item->tax_value, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->net_value, 2) }}</td>
                <td>{{ $wo?->statusEnum()?->label() ?? '--' }}</td>
            </tr>
            @empty
            <tr><td colspan="14">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
