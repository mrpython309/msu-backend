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
    <p>{{ method_exists($tickets, 'total') ? $tickets->total() : $tickets->count() }} total tickets</p>
    @include('reports.partials.tickets', ['tickets' => $tickets, 'tableColumns' => $tableColumns])
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
