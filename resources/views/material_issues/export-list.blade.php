<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Issues Export</title>
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
    <h2>Material Issues</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $materialIssues->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S/No</th>
                <th>Company</th>
                <th>Branch</th>
                <th>MI Number</th>
                <th>Issue Date</th>
                <th>Employee</th>
                <th>Department</th>
                <th>MR Number</th>
                <th class="text-end">Items</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materialIssues as $mi)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $mi->materialRequest?->company?->name ?? $mi->materialRequest?->branch?->company?->name ?? '--' }}</td>
                <td>{{ $mi->materialRequest?->branch?->name ?? '--' }}</td>
                <td>{{ $mi->mi_number }}</td>
                <td>{{ $mi->issue_date ? \Illuminate\Support\Carbon::parse($mi->issue_date)->format('d-m-Y') : '--' }}</td>
                <td>{{ $mi->employee?->employee_name ?? '--' }}</td>
                <td>{{ $mi->department?->department_name ?? '--' }}</td>
                <td>{{ $mi->materialRequest?->mr_number ?? '--' }}</td>
                <td class="text-end">{{ $mi->items->count() }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $mi->status)) }}</td>
            </tr>
            @empty
            <tr><td colspan="10">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
