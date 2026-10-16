@extends('layouts/contentNavbarLayout')

@section('title', 'Edit RFQ')

@section('vendor-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
@php
    $indent = $rfq->purchaseRequisition?->indent;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Edit RFQ {{ $rfq->rfq_no }}</h4>
    </div>
    <a class="btn btn-secondary btn-sm" href="{{ route('rfqs.show', $rfq) }}">Back</a>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('rfqs.update', $rfq) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">RFQ No.</label>
                <input class="form-control" value="{{ $rfq->rfq_no }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Purchase Requisition</label>
                <input class="form-control" value="{{ $rfq->purchaseRequisition->pr_no ?? '--' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Department</label>
                <input class="form-control" value="{{ $indent?->department?->department_name ?? '--' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">RFQ Date</label>
                <input type="date" name="rfq_date" class="form-control"
                       value="{{ old('rfq_date', optional($rfq->rfq_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">Select priority</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $rfq->priority) === $priority)>{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Closing Date</label>
                <input type="datetime-local" name="closes_at" class="form-control"
                       value="{{ old('closes_at', optional($rfq->closes_at)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Expected Delivery</label>
                <input type="date" name="expected_delivery_date" class="form-control"
                       value="{{ old('expected_delivery_date', optional($rfq->expected_delivery_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Delivery Store</label>
                <select name="delivery_store" class="form-select">
                    <option value="">Select delivery store</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_name }}" @selected(old('delivery_store', $rfq->delivery_store) === $dept->department_name)>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select">
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', $rfq->currency ?: 'INR') === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Vendor Information</h5></div>
        <div class="card-body">
            <label class="form-label">Select Vendors</label>
            <select name="vendor_ids[]" id="vendor_ids" class="form-select select2" multiple="multiple" data-placeholder="Select vendors">
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}"
                            @selected(collect(old('vendor_ids', $selectedVendorIds))->contains($vendor->id))>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Terms &amp; Attachments</h5></div>
        <div class="card-body row g-3">
            <div class="col-12">
                <label class="form-label">Notes to Vendor</label>
                <textarea name="notes_to_vendor" class="form-control" rows="4">{{ old('notes_to_vendor', $rfq->notes_to_vendor ?? $rfq->notes) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Upload Additional Files</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('rfqs.show', $rfq) }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $vendorSelect = $('#vendor_ids');
    if ($vendorSelect.hasClass('select2-hidden-accessible')) {
        $vendorSelect.select2('destroy');
    }
    $vendorSelect.select2({
        theme: 'bootstrap-5',
        placeholder: 'Select vendors',
        allowClear: true,
        width: '100%',
        dropdownParent: $(document.body)
    });
});
</script>
@endsection
