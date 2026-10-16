<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Dashboard Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 6px; font-size: 16px; }
        h3 { margin: 16px 0 6px; font-size: 13px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
        .total td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    @php
        $totalOpeningQty = 0;
        $totalOpeningVal = 0;
        $totalReceivedQty = 0;
        $totalReceivedVal = 0;
        $totalIssuedQty = 0;
        $totalIssuedVal = 0;
        $totalClosingQty = 0;
        $totalClosingVal = 0;
        foreach ($categorySummaries as $summary) {
            $totalOpeningQty += $summary['opening_qty'];
            $totalOpeningVal += $summary['opening_val'];
            $totalReceivedQty += $summary['received_qty'];
            $totalReceivedVal += $summary['received_val'];
            $totalIssuedQty += $summary['issued_qty'];
            $totalIssuedVal += $summary['issued_val'];
            $totalClosingQty += $summary['closing_qty'];
            $totalClosingVal += $summary['closing_val'];
        }
    @endphp

    <h2>Item Dashboard</h2>
    <div class="meta">
        Period: {{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}
        | Exported: {{ now()->timezone('Asia/Kolkata')->format('d-m-Y H:i') }}
    </div>

    <h3>Category Stock Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-end">Opening Qty</th>
                <th class="text-end">Opening Value</th>
                <th class="text-end">Received Qty</th>
                <th class="text-end">Received Value</th>
                <th class="text-end">Issued Qty</th>
                <th class="text-end">Issued Value</th>
                <th class="text-end">Closing Qty</th>
                <th class="text-end">Closing Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categorySummaries as $summary)
            <tr>
                <td>{{ $summary['name'] }}</td>
                <td class="text-end">{{ number_format($summary['opening_qty'], 3) }}</td>
                <td class="text-end">{{ number_format($summary['opening_val'], 2) }}</td>
                <td class="text-end">{{ number_format($summary['received_qty'], 3) }}</td>
                <td class="text-end">{{ number_format($summary['received_val'], 2) }}</td>
                <td class="text-end">{{ number_format($summary['issued_qty'], 3) }}</td>
                <td class="text-end">{{ number_format($summary['issued_val'], 2) }}</td>
                <td class="text-end">{{ number_format($summary['closing_qty'], 3) }}</td>
                <td class="text-end">{{ number_format($summary['closing_val'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="9">No category data available.</td></tr>
            @endforelse
            @if(count($categorySummaries))
            <tr class="total">
                <td>Total</td>
                <td class="text-end">{{ number_format($totalOpeningQty, 3) }}</td>
                <td class="text-end">{{ number_format($totalOpeningVal, 2) }}</td>
                <td class="text-end">{{ number_format($totalReceivedQty, 3) }}</td>
                <td class="text-end">{{ number_format($totalReceivedVal, 2) }}</td>
                <td class="text-end">{{ number_format($totalIssuedQty, 3) }}</td>
                <td class="text-end">{{ number_format($totalIssuedVal, 2) }}</td>
                <td class="text-end">{{ number_format($totalClosingQty, 3) }}</td>
                <td class="text-end">{{ number_format($totalClosingVal, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <h3>Low Stock Alert</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Item Code</th>
                <th class="text-end">Current Stock</th>
                <th class="text-end">Threshold</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStockItems as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_code }}</td>
                <td class="text-end">{{ number_format((float) $item->current_stock, 3) }}</td>
                <td class="text-end">{{ number_format((float) $item->threshold_quantity, 3) }}</td>
                <td>Low Stock</td>
            </tr>
            @empty
            <tr><td colspan="5">No items are at or below their threshold.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Recent Item Activity</h3>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Date Time</th>
                <th>Item</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentActivities as $activity)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($activity['date_time'])->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                <td>{{ $activity['item_name'] }}</td>
                <td>{{ $activity['action'] }}</td>
            </tr>
            @empty
            <tr><td colspan="4">No recent item activity.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
