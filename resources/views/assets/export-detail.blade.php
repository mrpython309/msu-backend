<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset {{ $asset->asset_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h2 { margin: 0 0 4px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; vertical-align: top; }
        th { width: 35%; background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>{{ $asset->asset_name }}</h2>
    <div class="meta">{{ $asset->asset_code }} | Exported: {{ now()->format('d-m-Y H:i') }}</div>
    <table>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <th>{{ $row[0] }}</th>
                    <td>{{ $row[1] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
