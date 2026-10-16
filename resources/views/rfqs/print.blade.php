<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        h2 { margin-top: 0; margin-bottom: 5px; color: #111; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .section-title { font-weight: bold; font-size: 13px; margin-top: 15px; margin-bottom: 6px; color: #222; border-bottom: 1px solid #aaa; padding-bottom: 3px; }
        .grid-table td { width: 25%; }
        .label { font-size: 9px; color: #666; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-weight: bold; font-size: 11px; color: #222; }
        .badge { display: inline-block; padding: 2px 6px; background: #e2e3e5; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Request For Quotation - {{ $rfq->rfq_no }}</h2>
    <p style="margin-top: 0; margin-bottom: 15px;">Status: <span class="badge">{{ $rfq->status instanceof \BackedEnum ? $rfq->status->value : ($rfq->status ?? '--') }}</span></p>

    <div class="section-title">Item Details</div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Code</th>
                <th>Category / Type</th>
                <th>Description</th>
                <th>Qty & Unit</th>
                <th>Est. Price</th>
                <th>Total Amount</th>
                <th>Manufacturer</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $master = $item->itemMaster ?? \App\Models\ItemMaster::with(['category', 'assetType'])->where('item_name', $item->item_name)->first();
                    $categoryName = $item->category?->name ?? ($master?->category?->name ?? '--');
                    $assetTypeName = $master?->assetType?->name ?? null;
                    $catType = $assetTypeName ? "{$categoryName} / {$assetTypeName}" : $categoryName;
                @endphp
                <tr>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td>{{ $master?->item_code ?? ('IND-'.str_pad((string) $item->id, 6, '0', STR_PAD_LEFT)) }}</td>
                    <td>{{ $catType }}</td>
                    <td>{{ $item->description ?: ($master?->description ?? '--') }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit ?? ($master?->uom ?? '') }}</td>
                    <td>{{ $item->estimated_price !== null ? number_format($item->estimated_price, 2) : '--' }}</td>
                    <td>{{ $item->total_amount !== null ? number_format($item->total_amount, 2) : '--' }}</td>
                    <td>{{ $item->manufacturer ?: '--' }}</td>
                    <td>{{ $item->item_remarks ?: '--' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #777;">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
