<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PO Items Export</title>
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
    <h2>Purchase Order Items</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $items->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Branch</th>
                <th>PO Number</th>
                <th>PO Date</th>
                <th>Vendor</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>HSN</th>
                <th>Product Category</th>
                <th>Product Sub Category</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Gross Value</th>
                <th class="text-end">Tax %</th>
                <th class="text-end">Tax Value</th>
                <th class="text-end">Net Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php $po = $item->purchaseOrder; @endphp
            <tr>
                <td>{{ $po?->branch?->company?->name ?? '--' }}</td>
                <td>{{ $po?->branch?->name ?? '--' }}</td>
                <td>{{ $po?->po_number ?? '--' }}</td>
                <td>{{ $po?->po_date?->format('d-m-Y') ?? '--' }}</td>
                <td>{{ $po?->vendor?->name ?? '--' }}</td>
                <td>{{ $item->item_code ?: '--' }}</td>
                <td>{{ $item->item_name ?: '--' }}</td>
                <td>{{ $item->hsn ?: '--' }}</td>
                <td>{{ $item->itemMaster?->productCategory?->category_name ?? '--' }}</td>
                <td>{{ $item->itemMaster?->productSubCategory?->sub_category_name ?? '--' }}</td>
                <td class="text-end">{{ number_format((float) $item->quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->price, 2) }}</td>
                <td class="text-end">{{ number_format($item->gross_value, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->tax, 2) }}</td>
                <td class="text-end">{{ number_format($item->tax_value, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->net_value, 2) }}</td>
                <td>{{ $po?->statusEnum()?->label() ?? '--' }}</td>
            </tr>
            @empty
            <tr><td colspan="17">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
