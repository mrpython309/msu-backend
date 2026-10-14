<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Stock Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Item Stock</h2>
    <div class="meta">
        Stock Date: {{ \Carbon\Carbon::parse($filterDate)->format('d-m-Y') }}
        | Exported: {{ now()->format('d-m-Y H:i') }}
        | Records: {{ $rows->count() }}
    </div>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>HSN</th>
                <th>Unit</th>
                <th class="text-end">Price</th>
                <th>Product Category</th>
                <th>Product Sub Category</th>
                <th class="text-end">Opening Stock</th>
                <th class="text-end">Adjusted Stocks</th>
                <th class="text-end">Received Stocks</th>
                <th class="text-end">Issued Stock</th>
                <th class="text-end">Closing Stock</th>
                <th>Stock Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td>{{ $row->sno }}</td>
                <td>{{ $row->item_code }}</td>
                <td>{{ $row->item_name }}</td>
                <td>{{ $row->hsn }}</td>
                <td>{{ $row->unit }}</td>
                <td class="text-end">{{ number_format($row->price, 2) }}</td>
                <td>{{ $row->category }}</td>
                <td>{{ $row->sub_category }}</td>
                <td class="text-end">{{ number_format($row->opening, 3) }}</td>
                <td class="text-end">{{ number_format($row->adjusted, 3) }}</td>
                <td class="text-end">{{ number_format($row->received, 3) }}</td>
                <td class="text-end">{{ number_format($row->issued, 3) }}</td>
                <td class="text-end">{{ number_format($row->closing, 3) }}</td>
                <td>{{ $row->stock_status }}</td>
            </tr>
            @empty
            <tr><td colspan="14">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
