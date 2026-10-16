<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ count($rows) }}</div>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell === null || $cell === '' ? '-' : $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ max(count($headings), 1) }}">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
