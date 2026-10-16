<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Requisitions</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
        h2 { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .meta { margin-bottom: 12px; color: #555; }
    </style>
</head>
<body>
    <h2>Purchase Requisitions</h2>
    <p class="meta">Generated: {{ now()->format('d M Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>PR No.</th>
                <th>Indent No.</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Store</th>
                <th>Processed By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requisitions as $pr)
                <tr>
                    <td>{{ $pr->pr_no }}</td>
                    <td>{{ $pr->indent->indent_no ?? '-' }}</td>
                    <td>{{ $pr->statusEnum()?->label() ?? $pr->status }}</td>
                    <td>{{ $pr->createdByUser->name ?? '-' }}</td>
                    <td>{{ $pr->created_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $pr->requisitionStore->department_name ?? '-' }}</td>
                    <td>{{ $pr->purchaseOfficer->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
