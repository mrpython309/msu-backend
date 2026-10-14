@extends('layouts/contentNavbarLayout')

@section('title', 'View Item')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Master / <a href="{{ route('item-masters.index') }}">Item Master</a> /</span> View Item
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Item Details ({{ $msuItemMaster->item_code }})</h5>
                <div>
                    @can('item-master:edit')
                    <a href="{{ route('item-masters.edit', $msuItemMaster) }}" class="btn btn-warning btn-sm">Edit</a>
                    @endcan
                    @can('item-master:delete')
                    <form action="{{ route('item-masters.destroy', $msuItemMaster) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    @endcan
                    <a href="{{ route('item-masters.index') }}" class="btn btn-secondary btn-sm">Back to list</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Item Code</label>
                        <div class="fw-medium">{{ $msuItemMaster->item_code }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Item Name</label>
                        <div class="fw-medium">{{ $msuItemMaster->item_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Product Category</label>
                        <div class="fw-medium">{{ $msuItemMaster->productCategory?->category_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Product Sub Category</label>
                        <div class="fw-medium">{{ $msuItemMaster->productSubCategory?->sub_category_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Unit of Measurement</label>
                        <div class="fw-medium">{{ $msuItemMaster->unit?->unit_name ?? ($msuItemMaster->uom ?: '--') }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">HSN</label>
                        <div class="fw-medium">
                            @if($msuItemMaster->hsn)
                                {{ $msuItemMaster->hsn->hsn_code }} - {{ $msuItemMaster->hsn->description }}
                                ({{ number_format((float) $msuItemMaster->hsn->tax_rate, 2) }}%)
                            @else
                                --
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Threshold Quantity</label>
                        <div class="fw-medium">
                            {{ $msuItemMaster->threshold_quantity !== null ? number_format((float) $msuItemMaster->threshold_quantity, 3) : '--' }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Stock Status</label>
                        <div>
                            <span class="badge bg-label-{{ $msuItemMaster->stockStatusBadgeClass() }}">
                                {{ $msuItemMaster->stock_status }}
                            </span>
                            <small class="text-muted ms-1">Current stock: {{ number_format((float) $msuItemMaster->current_stock, 3) }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="badge bg-label-{{ $msuItemMaster->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($msuItemMaster->status) }}
                            </span>
                        </div>
                    </div>
                    @if($msuItemMaster->image_url)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Image</label>
                        <div><img src="{{ $msuItemMaster->image_url }}" alt="Item" style="max-height:120px;border-radius:4px;"></div>
                    </div>
                    @endif
                </div>

                <h6 class="mt-3">Rate Details</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rate</th>
                                <th>Tax (%)</th>
                                <th>Effective From</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($msuItemMaster->rates as $rate)
                                <tr>
                                    <td>{{ number_format((float) $rate->rate, 2) }}</td>
                                    <td>{{ number_format((float) $rate->tax, 2) }}</td>
                                    <td>{{ $rate->effective_from?->format('d-m-Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No rates configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
