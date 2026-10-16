<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Adjustments Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Stock Adjustments</h2>
    <div class="meta">
        Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $adjustments->count() }}
        @if(!empty($filters['item_name'])) | Item: {{ $filters['item_name'] }} @endif
        @if(!empty($filters['from_date'])) | From: {{ \Illuminate\Support\Carbon::parse($filters['from_date'])->format('d-m-Y') }} @endif
        @if(!empty($filters['to_date'])) | To: {{ \Illuminate\Support\Carbon::parse($filters['to_date'])->format('d-m-Y') }} @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Number</th>
                <th>Date</th>
                <th>Type</th>
                <th>Item</th>
                <th>Unit</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
                <th class="text-end">GST %</th>
                <th class="text-end">GST Amount</th>
                <th class="text-end">Grand Total</th>
                <th>Employee</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            @forelse($adjustments as $adjustment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $adjustment->adjustment_number }}</td>
                <td>{{ $adjustment->adjustment_date?->format('d-m-Y') }}</td>
                <td>{{ ucfirst($adjustment->adjustment_type) }}</td>
                <td>{{ $adjustment->itemMaster?->item_code ? $adjustment->itemMaster->item_code.' - ' : '' }}{{ $adjustment->itemMaster?->item_name ?? '--' }}</td>
                <td>{{ $adjustment->unit ?: '--' }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->price, 2) }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->total, 2) }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->gst_percentage, 2) }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->gst_amount, 2) }}</td>
                <td class="text-end">{{ number_format((float) $adjustment->grand_total, 2) }}</td>
                <td>{{ $adjustment->employee?->employee_name ?? '--' }}</td>
                <td>{{ $adjustment->department?->department_name ?? '--' }}</td>
            </tr>
            @empty
            <tr><td colspan="14">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
