@extends('layouts/contentNavbarLayout')

@section('title', 'GRNs')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">Goods Receipt Notes</h4>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header pb-0"><h5 class="mb-0">Filters</h5></div>
    <div class="card-body pt-2">
        <form method="GET" action="{{ route('grns.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="GRN No., PO No., vendor" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value }}" {{ ($filters['status'] ?? '') === $statusOption->value ? 'selected' : '' }}>
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Received From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Received To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                <a href="{{ route('grns.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

@if ($canCreateGrn ?? false)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Purchase Orders Awaiting GRN</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>PO No</th>
                    <th>Item</th>
                    <th>Vendor</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($awaitingPos as $po)
                    <tr>
                        <td class="text-nowrap text-muted">{{ $loop->iteration }}</td>
                        <td class="text-nowrap">
                            @if ($po)
                                @include('purchase-orders._po-number', ['po' => $po, 'link' => true])
                            @else
                                --
                            @endif
                        </td>
                        <td>@include('partials.item-names-summary', ['names' => $po->itemNameList()])</td>
                        <td>{{ $po->vendor->name ?? '--' }}</td>
                        <td>{{ $po->createdBy->name ?? '--' }}</td>
                        <td><span class="badge bg-label-success">{{ $po->status?->label() ?? $po->status }}</span></td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('grns.create', $po) }}">
                                Create GRN
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No purchase orders waiting for GRN.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Existing GRNs</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>GRN</th>
                    <th>PO</th>
                    <th>Item</th>
                    <th>Vendor</th>
                    <th>Received Date</th>
                    <th>Received By</th>
                    <th>Inspected By</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grns as $grn)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($grns->currentPage() - 1) * $grns->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $grn->grn_no }}</td>
                        <td class="text-nowrap">
                            @if ($grn->purchaseOrder)
                                @include('purchase-orders._po-number', ['po' => $grn->purchaseOrder, 'link' => true])
                            @else
                                -
                            @endif
                        </td>
                        <td>@include('partials.item-names-summary', ['names' => $grn->itemNameList()])</td>
                        <td>{{ $grn->vendor->name ?? '-' }}</td>
                        <td>{{ $grn->received_date?->format('d M Y') }}</td>
                        <td>{{ $grn->receivedBy->name ?? '-' }}</td>
                        <td>{{ $grn->inspectedBy->name ?? 'Pending' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $grn->statusEnum()?->badgeClass() ?? 'secondary' }}">
                                {{ $grn->status->value ?? $grn->status }}
                            </span>
                        </td>
                        <td>
                            @if($grn->items->contains(fn($i) => $i->isApproved()) && $grn->items->flatMap->assets->isEmpty())
                                @if(auth()->user()?->canAny(['grns:approve', 'grn.approve', 'quality-inspection.perform', 'quality-inspections:perform', 'asset:create', 'asset.create']) || auth()->user()?->hasRole(['Quality Inspector', 'Quality Control', 'Store Incharge', 'Store Officer', 'Store Manager', 'Purchase Officer', 'Admin', 'superadmin', 'Super Admin']))
                                    <form method="POST" action="{{ route('grns.create-assets', $grn) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm me-1" onclick="return confirm('Transfer approved items to Asset inventory now?')">
                                            <i class="bx bx-transfer me-1"></i> Transfer to Asset
                                        </button>
                                    </form>
                                @endif
                            @endif
                            <a href="{{ route('grns.show', $grn) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No GRNs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($grns->hasPages())
        <div class="card-footer">{{ $grns->links() }}</div>
    @endif
</div>
@endsection
