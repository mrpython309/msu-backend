<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Receipt Note Items Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Goods Receipt Note Items</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $items->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>WI Number</th>
                <th>WI Date</th>
                <th>PO Number</th>
                <th>Branch</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>HSN</th>
                <th class="text-end">Ordered Qty</th>
                <th class="text-end">Prev. Received Qty</th>
                <th class="text-end">Received Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            @php $WI = $item->WI; @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $WI?->WI_number }}</td>
                <td>{{ $WI?->WI_date?->format('d-m-Y') }}</td>
                <td>{{ $WI?->workOrder?->wo_number ?? '--' }}</td>
                <td>{{ $WI?->vendor?->name ?? '--' }}</td>
                <td>{{ $item->item_code }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->hsn }}</td>
                <td class="text-end">{{ number_format((float) $item->ordered_quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->previously_received_quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->received_quantity, 3) }}</td>
            </tr>
            @empty
            <tr><td colspan="11">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

