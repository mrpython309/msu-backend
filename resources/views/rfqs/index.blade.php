@extends('layouts/contentNavbarLayout')

@section('title', 'RFQs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Requests for Quotation</h4>
    @canany(['rfq.create', 'rfqs:create'])
        <a class="btn btn-primary" href="{{ route('rfqs.create') }}">
            <i class="bx bx-plus me-1"></i> Create RFQ
        </a>
    @endcanany
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>RFQ</th>
                    <th>PR</th>
                    <th>Vendors</th>
                    <th>Vendor Status</th>
                    <th>Selected Vendor</th>
                    <th style="width:70px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rfqs as $rfq)
                    @php
                        $statusValue = $rfq->status->value ?? (string) $rfq->status;
                        $canEdit = in_array($statusValue, ['Draft', 'Open'], true);
                        $canDelete = in_array($statusValue, ['Draft', 'Cancelled'], true)
                            || ($statusValue === 'Open' && $rfq->quotations_count === 0);

                        $selectedQuotation = $rfq->selectedQuotation;
                        if ($selectedQuotation) {
                            $vendorStatus = 'Selected';
                            $vendorStatusClass = 'success';
                            $selectedVendorName = $selectedQuotation->vendor->name ?? '--';
                        } elseif ($rfq->rfqVendors->contains(fn ($pivot) => strcasecmp((string) ($pivot->status ?? ''), 'Invited') === 0)) {
                            $vendorStatus = 'Invited';
                            $vendorStatusClass = 'primary';
                            $selectedVendorName = '--';
                        } elseif ($rfq->quotations_count > 0) {
                            $vendorStatus = 'Quoted';
                            $vendorStatusClass = 'info';
                            $selectedVendorName = '--';
                        } else {
                            $vendorStatus = '--';
                            $vendorStatusClass = 'secondary';
                            $selectedVendorName = '--';
                        }
                    @endphp
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($rfqs->currentPage() - 1) * $rfqs->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $rfq->rfq_no }}</td>
                        <td>{{ $rfq->purchaseRequisition->pr_no ?? '-' }}</td>
                        <td>{{ $rfq->rfqVendors->count() }}</td>
                        <td>
                            @if ($vendorStatus !== '--')
                                <span class="badge bg-label-{{ $vendorStatusClass }}">{{ $vendorStatus }}</span>
                            @else
                                --
                            @endif
                        </td>
                        <td>{{ $selectedVendorName }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('rfqs.show', $rfq) }}">
                                            <i class="bx bx-show me-1"></i> View
                                        </a>
                                    </li>
                                    @canany(['rfq.edit', 'rfqs:edit'])
                                        @if ($canEdit)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('rfqs.edit', $rfq) }}">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                        @endif
                                    @endcanany
                                    @canany(['rfq.delete', 'rfqs:delete'])
                                        @if ($canDelete)
                                            <li>
                                                <form method="POST" action="{{ route('rfqs.destroy', $rfq) }}"
                                                      onsubmit="return confirm('Delete RFQ {{ $rfq->rfq_no }}? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcanany
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No RFQs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $rfqs->links() }}</div>
</div>
@endsection
