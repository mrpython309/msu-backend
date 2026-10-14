<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Receipt Notes Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
        .badge { font-size: 9px; }
    </style>
</head>
<body>
    <h2>Goods Receipt Notes</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $grns->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Branch</th>
                <th>GRN Number</th>
                <th>Receipt Number</th>
                <th>GRN Date</th>
                <th>PO Number</th>
                <th>Vendor Name</th>
                <th>Invoice No</th>
                <th class="text-end">Items</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th class="text-end">Misc Exp</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grns as $grn)
            @php
                $total = 0.0;
                $tax = 0.0;
                foreach ($grn->items as $item) {
                    $poItem = $item->purchaseOrderItem;
                    $rate = $poItem ? (float) $poItem->price : 0;
                    $taxPct = $poItem ? (float) $poItem->tax : 0;
                    $lineTotal = round((float) $item->received_quantity * $rate, 2);
                    $total += $lineTotal;
                    $tax += round($lineTotal * $taxPct / 100, 2);
                }
                $miscExp = (float) ($grn->miscellaneous_charges ?? 0);
                $tax += (float) ($grn->miscellaneous_tax ?? 0);
                $total = round($total, 2);
                $tax = round($tax, 2);
                $grandTotal = round($total + $miscExp + $tax, 2);
            @endphp
            <tr>
                <td>{{ $grn->branch?->company?->name ?? $grn->purchaseOrder?->branch?->company?->name ?? '--' }}</td>
                <td>{{ $grn->branch?->name ?? $grn->purchaseOrder?->branch?->name ?? '--' }}</td>
                <td>{{ $grn->grn_number }}</td>
                <td>{{ $grn->receipt_number ?: '--' }}</td>
                <td>{{ $grn->grn_date?->format('d-m-Y') ?? '--' }}</td>
                <td>{{ $grn->purchaseOrder?->po_number ?? '--' }}</td>
                <td>{{ $grn->vendor?->name ?? '--' }}</td>
                <td>{{ $grn->vendor_invoice_number ?: '--' }}</td>
                <td class="text-end">{{ $grn->items->count() }}</td>
                <td>{{ $grn->statusEnum()->label() }}</td>
                <td class="text-end">{{ number_format($total, 2) }}</td>
                <td class="text-end">{{ number_format($miscExp, 2) }}</td>
                <td class="text-end">{{ number_format($tax, 2) }}</td>
                <td class="text-end">{{ number_format($grandTotal, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="14">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
