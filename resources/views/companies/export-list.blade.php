<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Companies Export</title>
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
    <h2>Companies</h2>
    <div class="meta">Exported: {{ now()->format('d-m-Y H:i') }} | Records: {{ $companies->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Address</th>
                <th>City</th>
                <th>State</th>
                <th>Country</th>
                <th>Pincode</th>
                <th>Contact Person</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $company->name }}</td>
                <td>{{ $company->code }}</td>
                <td>
                    {{ $company->address_line_1 ?? '' }}
                    @if($company->address_line_2)
                        , {{ $company->address_line_2 }}
                    @endif
                </td>
                <td>{{ $company->city ?? '-' }}</td>
                <td>{{ $company->state ?? '-' }}</td>
                <td>{{ $company->country ?? '-' }}</td>
                <td>{{ $company->pincode ?? '-' }}</td>
                <td>{{ $company->contact_person ?? '-' }}</td>
                <td>{{ $company->contact_number ?? '-' }}</td>
                <td>{{ $company->email ?? '-' }}</td>
                <td>{{ ucfirst($company->status ?? '-') }}</td>
            </tr>
            @empty
            <tr><td colspan="12">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
