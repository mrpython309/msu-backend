@extends('layouts/contentNavbarLayout')

@section('title', 'Quality Inspections')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">Quality Inspections</h4>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header border-bottom">
        <h5 class="mb-0">Filter & Search GRNs for Inspection</h5>
    </div>
    <div class="card-body mt-3">
        <form method="GET" action="{{ route('quality-inspections.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="search">Search GRN / Vendor</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" name="search" id="search" value="{{ request('search') }}" placeholder="GRN number or vendor name...">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">GRN Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Statuses</option>
                    @foreach(['Pending Inspection', 'Under Inspection', 'Partially Accepted', 'Accepted', 'Rejected'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                <a href="{{ route('quality-inspections.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0">GRN Quality Inspections Directory</h5>
        <span class="badge bg-primary">{{ $grns->total() }} Record(s)</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>GRN No & Date</th>
                    <th>Vendor / Supplier</th>
                    <th>Received By</th>
                    <th>Inspected By</th>
                    <th>Item Inspection Summary</th>
                    <th>Overall Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grns as $grn)
                    @php
                        $summary = $grn->inspectionSummary();
                        $badgeClass = match ($grn->status) {
                            'Accepted', 'Approved' => 'success',
                            'Partially Accepted', 'Under Inspection' => 'warning',
                            'Rejected' => 'danger',
                            default => 'secondary',
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('quality-inspections.show', $grn) }}" class="fw-semibold text-primary d-block">
                                {{ $grn->grn_no }}
                            </a>
                            <small class="text-muted">{{ $grn->received_date?->format('d M Y') ?? '--' }}</small>
                        </td>
                        <td>
                            <strong>{{ $grn->vendor?->name ?? '--' }}</strong>
                            <div class="small text-muted">{{ $grn->vendor?->code }}</div>
                        </td>
                        <td>
                            <span>{{ $grn->receivedBy?->name ?? '--' }}</span>
                        </td>
                        <td>
                            @php
                                $inspectedByNames = $grn->inspectedBy?->name ? collect([$grn->inspectedBy->name]) : $grn->items->map->inspectedBy->filter()->pluck('name')->merge($grn->qualityInspections->map->inspector->filter()->pluck('name'))->unique();
                            @endphp
                            <span>{{ $inspectedByNames->isNotEmpty() ? $inspectedByNames->join(', ') : '--' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-dark me-1" title="Total Items">{{ $summary['total'] }} Total</span>
                            @if($summary['approved'] > 0)
                                <span class="badge bg-label-success me-1">{{ $summary['approved'] }} Approved</span>
                            @endif
                            @if($summary['rejected_or_damaged'] > 0)
                                <span class="badge bg-label-danger me-1">{{ $summary['rejected_or_damaged'] }} Rejected/Damaged</span>
                            @endif
                            @if($summary['hold'] > 0)
                                <span class="badge bg-label-info me-1">{{ $summary['hold'] }} On Hold</span>
                            @endif
                            @if($summary['pending'] > 0 && ($summary['approved'] === 0 && $summary['rejected_or_damaged'] === 0 && $summary['hold'] === 0))
                                <span class="badge bg-label-secondary me-1">{{ $summary['pending'] }} Pending</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $badgeClass }}">{{ $grn->status }}</span>
                        </td>
                        <td class="text-center">
                            @if($summary['approved'] > 0 && $grn->items->flatMap->assets->isEmpty() && $canPerform)
                                <form method="POST" action="{{ route('quality-inspections.create-assets', $grn) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success me-1" onclick="return confirm('Transfer approved items to Asset inventory now?')">
                                        <i class="bx bx-transfer me-1"></i> Transfer to Asset
                                    </button>
                                </form>
                            @endif
                            @if($summary['pending'] === 0)
                                <a href="{{ route('quality-inspections.show', $grn) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-show me-1"></i> View Inspected
                                </a>
                            @else
                                <a href="{{ route('quality-inspections.show', $grn) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-check-shield me-1"></i> {{ $canPerform ? 'Inspect / View' : 'View' }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bx bx-shield-x fs-3 d-block mb-2"></i>
                            No GRNs awaiting quality inspection found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($grns->hasPages())
        <div class="card-footer border-top d-flex justify-content-end py-2">
            {{ $grns->links() }}
        </div>
    @endif
</div>
@endsection
