<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Receipt Notes Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
        .badge { font-size: 10px; }
    </style>
</head>
<body>
    <h2>Goods Receipt Notes</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $WIs->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>WI Number</th>
                <th>Receipt Number</th>
                <th>WI Date</th>
                <th>PO Number</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Invoice No</th>
                <th class="text-end">Items</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($WIs as $WI)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $WI->WI_number }}</td>
                <td>{{ $WI->receipt_number ?: '--' }}</td>
                <td>{{ $WI->WI_date?->format('d-m-Y') }}</td>
                <td>{{ $WI->workOrder?->wo_number ?? '--' }}</td>
                <td>{{ $WI->vendor?->name ?? '--' }}</td>
                <td>{{ $WI->department?->department_name ?? '--' }}</td>
                <td>{{ $WI->vendor_invoice_number ?: '--' }}</td>
                <td class="text-end">{{ $WI->items->count() }}</td>
                <td>{{ $WI->statusEnum()->label() }}</td>
            </tr>
            @empty
            <tr><td colspan="10">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

