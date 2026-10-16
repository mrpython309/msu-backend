<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Master Export</title>
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
    <h2>Item Master</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $items->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Product Category</th>
                <th>Product Sub Category</th>
                <th>Unit</th>
                <th>HSN</th>
                <th>Status</th>
                <th>Threshold Quantity</th>
                <th>Stock Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->item_code }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->productCategory?->category_name ?? '-' }}</td>
                <td>{{ $item->productSubCategory?->sub_category_name ?? '-' }}</td>
                <td>{{ $item->unit?->unit_name ?? ($item->uom ?: '--') }}</td>
                <td>{{ $item->hsn?->hsn_code ?? '-' }}</td>
                <td>{{ ucfirst($item->status ?? '-') }}</td>
                <td>{{ $item->threshold_quantity !== null ? number_format((float) $item->threshold_quantity, 3) : '--' }}</td>
                <td>{{ $item->stock_status }}</td>
            </tr>
            @empty
            <tr><td colspan="10">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
