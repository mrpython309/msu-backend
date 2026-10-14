<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 6px; font-size: 16px; }
        h3 { margin: 16px 0 6px; font-size: 13px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
        .kpi td:first-child { width: 28%; font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h2>Dashboard</h2>
    <div class="meta">
        Company: {{ $companyName }}
        | Branch: {{ $branchName }}
        | Exported: {{ now()->timezone('Asia/Kolkata')->format('d-m-Y H:i') }}
    </div>

    <h3>Summary</h3>
    <table class="kpi">
        <tbody>
            <tr>
                <td>Purchase Orders</td>
                <td>{{ number_format($poCount) }} ({{ $pendingPoApprovals }} pending approval)</td>
                <td>Goods Receipts</td>
                <td>{{ number_format($grnCount) }} ({{ $pendingStockGrns }} pending stock)</td>
            </tr>
            <tr>
                <td>Active Items</td>
                <td>{{ number_format($itemCount) }}</td>
                <td>Current Stock</td>
                <td>{{ number_format($currentStockQty, 2) }}</td>
            </tr>
            <tr>
                <td>Work Orders</td>
                <td>{{ number_format($woCount) }} ({{ $pendingWoApprovals }} pending approval)</td>
                <td>Pending Approvals</td>
                <td>{{ number_format($pendingApprovals) }}</td>
            </tr>
            <tr>
                <td>PO Spend</td>
                <td>{{ number_format($totalPoSpend, 2) }}</td>
                <td>Work Order Spend</td>
                <td>{{ number_format($totalWoSpend, 2) }}</td>
            </tr>
            <tr>
                <td>Total Order Value</td>
                <td colspan="3">{{ number_format($totalPoSpend + $totalWoSpend, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Active Purchase Order</h3>
    <table>
        <tbody>
            <tr>
                <th>PO Number</th>
                <td>{{ $poApprovalProgress['po_no'] }}</td>
                <th>Vendor</th>
                <td>{{ $poApprovalProgress['vendor_name'] }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $poApprovalProgress['status'] }} ({{ $poApprovalProgress['progress'] }}%)</td>
                <th>Estimated Value</th>
                <td>{{ number_format($poApprovalProgress['amount'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>PO &amp; Work Order Spend Trend</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-end">PO Spend</th>
                <th class="text-end">Work Order Spend</th>
            </tr>
        </thead>
        <tbody>
            @forelse($spendTrend as $point)
            <tr>
                <td>{{ $point['label'] }}</td>
                <td class="text-end">{{ number_format($point['po_total'], 2) }}</td>
                <td class="text-end">{{ number_format($point['wo_total'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3">No spend data available.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Recent Purchase Orders</h3>
    <table>
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Vendor</th>
                <th>Date</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPurchaseOrders as $po)
            <tr>
                <td>{{ $po->po_number }}</td>
                <td>{{ $po->vendor?->name ?? 'N/A' }}</td>
                <td>{{ $po->po_date?->format('d M Y') ?? 'N/A' }}</td>
                <td class="text-end">{{ number_format((float) $po->grand_total, 2) }}</td>
                <td>{{ $po->statusEnum()->label() }}</td>
            </tr>
            @empty
            <tr><td colspan="5">No purchase orders found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Recent Work Orders</h3>
    <table>
        <thead>
            <tr>
                <th>WO Number</th>
                <th>Vendor</th>
                <th>Date</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentWorkOrders as $wo)
            <tr>
                <td>{{ $wo->wo_number }}</td>
                <td>{{ $wo->vendor?->name ?? 'N/A' }}</td>
                <td>{{ $wo->wo_date?->format('d M Y') ?? 'N/A' }}</td>
                <td class="text-end">{{ number_format((float) $wo->grand_total, 2) }}</td>
                <td>{{ $wo->statusEnum()->label() }}</td>
            </tr>
            @empty
            <tr><td colspan="5">No work orders found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Recent Goods Receipts</h3>
    <table>
        <thead>
            <tr>
                <th>GRN Number</th>
                <th>PO Number</th>
                <th>Vendor</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentGrns as $grn)
            <tr>
                <td>{{ $grn->grn_number }}</td>
                <td>{{ $grn->purchaseOrder?->po_number ?? 'N/A' }}</td>
                <td>{{ $grn->vendor?->name ?? 'N/A' }}</td>
                <td>{{ $grn->grn_date?->format('d M Y') ?? 'N/A' }}</td>
                <td>{{ $grn->statusEnum()->label() }}</td>
            </tr>
            @empty
            <tr><td colspan="5">No goods receipts found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Recent Stock Movements</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th class="text-end">Qty</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentStockMovements as $movement)
            @php
                $typeLabel = $movement->transaction_type === 'in' ? 'In' : ($movement->transaction_type === 'out' ? 'Out' : 'Adj');
            @endphp
            <tr>
                <td>{{ $movement->itemMaster?->item_name ?? 'Unknown Item' }}</td>
                <td>{{ $typeLabel }}</td>
                <td class="text-end">{{ number_format((float) $movement->quantity, 2) }}</td>
                <td>{{ $movement->created_at?->timezone('Asia/Kolkata')->format('d M Y') ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr><td colspan="4">No stock movements found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
