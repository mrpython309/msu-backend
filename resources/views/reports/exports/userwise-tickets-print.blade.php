<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 16px; margin: 0; }
        h3 { margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        thead { background: #f5f5f5; }
        .badge { font-size: 11px; }
    </style>
</head>
<body>
    <h3>{{ $reportTitle }}</h3>
    <p>{{ $totalTickets }} total tickets</p>
    <table>
        <thead>
            <tr>
                <th class="text-nowrap">#</th>
                <th>Emp Code</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Company</th>
                <th>Branch</th>
                <th>Accepted</th>
                <th>WIP</th>
                <th>On hold</th>
                <th>Completed</th>
                <th>Closed</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$user->emp_code}}</td>
                <td>{{$user->name}}</td>
                <td>{{$user->designation->designation_name ?? '--'}}</td>
                <td>{{$user->departments->pluck('department_name')->implode(', ') ?: '-'}}</td>
                <td>{{$user->companies->pluck('name')->implode(', ') ?: '-'}}</td>
                <td>{{$user->branches->pluck('name')->implode(', ') ?: '-'}}</td>
                <td align="center">{{$user->accepted_count ?? 0 }}</td>
                <td align="center">{{$user->work_in_progress_count ?? 0 }}</td>
                <td align="center">{{$user->hold_count ?? 0}}</td>
                <td align="center">{{$user->completed_count ?? 0}}</td>
                <td align="center">{{$user->closed_count ?? 0}}</td>
                <td align="center">{{$user->total_count ?? 0}}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                    No Users found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
