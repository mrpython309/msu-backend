@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Shipping Detail')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Edit Shipping Detail</h4>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Edit Shipping Detail</h5>
        <a href="{{ route('shipping-details.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('shipping-details.update', $shippingDetail) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Shipping Name <span class="text-danger">*</span></label>
                    <input type="text" name="shipping_name" class="form-control @error('shipping_name') is-invalid @enderror" value="{{ old('shipping_name', $shippingDetail->shipping_name) }}">
                    @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $shippingDetail->gst_number) }}">
                    @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $shippingDetail->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $shippingDetail->contact_person) }}">
                    @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contact No</label>
                    <input type="text" name="contact_no" class="form-control @error('contact_no') is-invalid @enderror" value="{{ old('contact_no', $shippingDetail->contact_no) }}">
                    @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email Id</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $shippingDetail->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $shippingDetail->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $shippingDetail->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Shipping Detail</button>
            <a href="{{ route('shipping-details.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
