@if(empty($permission) || auth()->user()?->can($permission))
<a href="{{ route($excelRoute, request()->query()) }}" class="btn {{ $btnClass ?? '' }} btn-outline-success">
    <i class="bx bx-export me-1"></i> Excel
</a>
<a href="{{ route($pdfRoute, request()->query()) }}" class="btn {{ $btnClass ?? '' }} btn-outline-danger">
    <i class="bx bxs-file-pdf me-1"></i> PDF
</a>
@endif
