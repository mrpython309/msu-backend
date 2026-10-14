<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Orders Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
        .badge { font-size: 9px; }
    </style>
</head>
<body>
    <h2>Purchase Orders</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $purchaseOrders->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S/No</th>
                <th>Company</th>
                <th>Branch</th>
                <th>PO Number</th>
                <th>PO Date</th>
                <th>Vendor</th>
                <th>Department</th>
                <th>Payment Terms</th>
                <th>Items</th>
                <th class="text-end">Total Value</th>
                <th class="text-end">Misc Charges</th>
                <th class="text-end">Grand Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $po)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $po->branch?->company?->name ?? '--' }}</td>
                <td>{{ $po->branch?->name ?? '--' }}</td>
                <td>{{ $po->po_number }}</td>
                <td>{{ $po->po_date?->format('d-m-Y') }}</td>
                <td>{{ $po->vendor?->name ?? '--' }}</td>
                <td>{{ $po->department?->department_name ?? '--' }}</td>
                <td>{{ $po->paymentTerm?->term_name ?? '--' }}</td>
                <td>{{ $po->items->count() }}</td>
                <td class="text-end">{{ number_format((float) $po->total_value, 2) }}</td>
                <td class="text-end">{{ number_format((float) $po->miscellaneous_charges, 2) }}</td>
                <td class="text-end">{{ number_format((float) $po->grand_total, 2) }}</td>
                <td>{{ $po->statusEnum()->label() }}</td>
            </tr>
            @empty
            <tr><td colspan="13">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
