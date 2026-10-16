<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Requests Export</title>
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
    <h2>Material Requests</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $materialRequests->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S/No</th>
                <th>Company</th>
                <th>Branch</th>
                <th>MR Number</th>
                <th>Request Date</th>
                <th>Department</th>
                <th>Employee</th>
                <th>Branch</th>
                <th>Company</th>
                <th class="text-end">Items</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materialRequests as $mr)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $mr->company?->name ?? $mr->branch?->company?->name ?? '--' }}</td>
                <td>{{ $mr->branch?->name ?? '--' }}</td>
                <td>{{ $mr->mr_number }}</td>
                <td>{{ $mr->request_date ? \Illuminate\Support\Carbon::parse($mr->request_date)->format('d-m-Y') : '--' }}</td>
                <td>{{ $mr->department?->department_name ?? '--' }}</td>
                <td>{{ $mr->employee?->employee_name ?? '--' }}</td>
                <td class="text-end">{{ $mr->items->count() }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $mr->status)) }}</td>
            </tr>
            @empty
            <tr><td colspan="9">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
