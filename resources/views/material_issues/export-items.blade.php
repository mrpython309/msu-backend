<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Issue Items Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #222; }
        h2 { margin: 0 0 8px; font-size: 14px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 2px 3px; }
        th { background: #f3f3f3; text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Material Issue Items</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $rows->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Issue No.</th>
                <th>Issue Date</th>
                <th>Company</th>
                <th>Branch Name</th>
                <th>Issued To</th>
                <th>Department</th>
                <th>Material Req Nos.</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Product Cat</th>
                <th>Product Sub Category</th>
                <th class="text-end">Issue Qty</th>
                <th>Unit</th>
                <th class="text-end">Price</th>
                <th class="text-end">Gross Amount</th>
                <th class="text-end">Tax (%)</th>
                <th class="text-end">Tax Price</th>
                <th class="text-end">Net Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td>{{ $row['sno'] }}</td>
                <td>{{ $row['issue_no'] !== '' ? $row['issue_no'] : '--' }}</td>
                <td>{{ $row['issue_date'] !== '' ? $row['issue_date'] : '--' }}</td>
                <td>{{ $row['company'] !== '' ? $row['company'] : '--' }}</td>
                <td>{{ $row['branch'] !== '' ? $row['branch'] : '--' }}</td>
                <td>{{ $row['issued_to'] !== '' ? $row['issued_to'] : '--' }}</td>
                <td>{{ $row['department'] !== '' ? $row['department'] : '--' }}</td>
                <td>{{ $row['mr_nos'] !== '' ? $row['mr_nos'] : '--' }}</td>
                <td>{{ $row['item_code'] !== '' ? $row['item_code'] : '--' }}</td>
                <td>{{ $row['item_name'] !== '' ? $row['item_name'] : '--' }}</td>
                <td>{{ $row['product_cat'] !== '' ? $row['product_cat'] : '--' }}</td>
                <td>{{ $row['product_sub_cat'] !== '' ? $row['product_sub_cat'] : '--' }}</td>
                <td class="text-end">{{ number_format((float) $row['issue_qty'], 3) }}</td>
                <td>{{ $row['unit'] !== '' ? $row['unit'] : '--' }}</td>
                <td class="text-end">{{ number_format((float) $row['price'], 2) }}</td>
                <td class="text-end">{{ number_format((float) $row['gross'], 2) }}</td>
                <td class="text-end">{{ number_format((float) $row['tax_percent'], 2) }}</td>
                <td class="text-end">{{ number_format((float) $row['tax_price'], 2) }}</td>
                <td class="text-end">{{ number_format((float) $row['net'], 2) }}</td>
                <td>{{ $row['status'] !== '' ? $row['status'] : '--' }}</td>
            </tr>
            @empty
            <tr><td colspan="20">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
