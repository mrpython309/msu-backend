<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Depreciation Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; vertical-align: top; word-wrap: break-word; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    @php
        $headings = app(\App\Http\Controllers\Assets\AssetController::class)->depreciationListHeadings();
        $amountIndexes = [];
        foreach ($headings as $index => $heading) {
            if (in_array($heading, ['Invoice Amount', 'Current Value'], true)) {
                $amountIndexes[] = $index;
            }
        }
    @endphp
    <h2>Asset Depreciation Report</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $assets->count() }}</div>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th class="{{ in_array($heading, ['Invoice Amount', 'Current Value'], true) ? 'text-end' : '' }}">{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
                @php $row = app(\App\Http\Controllers\Assets\AssetController::class)->depreciationListColumns($asset, $loop->iteration); @endphp
                <tr>
                    @foreach($row as $index => $cell)
                        <td class="{{ in_array($index, $amountIndexes, true) ? 'text-end' : '' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">No depreciation data available.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
