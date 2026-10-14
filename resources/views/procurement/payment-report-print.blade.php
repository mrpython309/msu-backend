<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 6px; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 5px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; }
        .text-end { text-align: right; }
        .totals td { font-weight: bold; background: #f7f7f7; }
    </style>
</head>
<body>
    <h2>Payment Report</h2>
    <div class="meta">
        Vendor: {{ $summary['vendor_label'] }}
        @if (!empty($filters['date_from']) || !empty($filters['date_to']))
            | Date:
            {{ $filters['date_from'] ?? '...' }}
            to
            {{ $filters['date_to'] ?? '...' }}
        @endif
        @if (!empty($filters['status']))
            | Status: {{ $filters['status'] }}
        @endif
        <br>
        POs: {{ $summary['po_count'] }}
        | PO Total: {{ number_format((float) $summary['total_po_amount'], 2) }}
        | Total Paid: {{ number_format((float) $summary['total_paid_amount'], 2) }}
        | Remaining: {{ number_format((float) $summary['total_remaining'], 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>PO No</th>
                <th>Vendor</th>
                <th>PO Date</th>
                <th class="text-end">PO Total</th>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Remaining</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $po)
                <tr>
                    <td>{{ $po->po_no }}</td>
                    <td>{{ $po->vendor?->name ?? '--' }}</td>
                    <td>{{ optional($po->purchase_date)->format('d M Y') ?? '--' }}</td>
                    <td class="text-end">{{ number_format((float) $po->orderTotalAmount(), 2) }}</td>
                    <td class="text-end">{{ number_format((float) $po->paidAmount(), 2) }}</td>
                    <td class="text-end">{{ number_format((float) $po->remainingAmount(), 2) }}</td>
                    <td>{{ $po->paymentStatusEnum()->value }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No purchase orders found.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($records->isNotEmpty())
            <tfoot>
                <tr class="totals">
                    <td colspan="3">TOTAL</td>
                    <td class="text-end">{{ number_format((float) $summary['total_po_amount'], 2) }}</td>
                    <td class="text-end">{{ number_format((float) $summary['total_paid_amount'], 2) }}</td>
                    <td class="text-end">{{ number_format((float) $summary['total_remaining'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
