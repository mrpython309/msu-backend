<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Request Items Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Material Request Items</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $items->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Branch</th>
                <th>MR Number</th>
                <th>Request Date</th>
                <th>Department</th>
                <th>Employee</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>HSN</th>
                <th>Unit</th>
                <th class="text-end">Requested Qty</th>
                <th class="text-end">Issued Qty</th>
                <th class="text-end">Remaining Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php $mr = $item->materialRequest; @endphp
            <tr>
                <td>{{ $mr?->company?->name ?? $mr?->branch?->company?->name ?? '--' }}</td>
                <td>{{ $mr?->branch?->name ?? '--' }}</td>
                <td>{{ $mr?->mr_number ?? '--' }}</td>
                <td>{{ $mr?->request_date ? \Illuminate\Support\Carbon::parse($mr->request_date)->format('d-m-Y') : '--' }}</td>
                <td>{{ $mr?->department?->department_name ?? '--' }}</td>
                <td>{{ $mr?->employee?->employee_name ?? '--' }}</td>
                <td>{{ $item->itemMaster?->item_code ?? '--' }}</td>
                <td>{{ $item->itemMaster?->item_name ?? '--' }}</td>
                <td>{{ $item->itemMaster?->hsn?->hsn_code ?? $item->itemMaster?->hsn?->code ?? '--' }}</td>
                <td>{{ $item->unit?->unit_name ?? $item->itemMaster?->unit?->unit_name ?? '--' }}</td>
                <td class="text-end">{{ number_format((float) $item->requested_qty, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->issued_qty, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->remaining_qty, 3) }}</td>
                <td>{{ $mr ? ucfirst(str_replace('_', ' ', $mr->status)) : '--' }}</td>
            </tr>
            @empty
            <tr><td colspan="14">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
