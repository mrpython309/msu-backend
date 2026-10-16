@extends('layouts/contentNavbarLayout')

@section('title', 'Create Payment Term')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Create Payment Term</h4>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Payment Term</h5>
        <a href="{{ route('payment-terms.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('payment-terms.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <input type="text" name="payment_mode" class="form-control @error('payment_mode') is-invalid @enderror"
                           value="{{ old('payment_mode') }}" placeholder="e.g. Cash, Cheque, NEFT, UPI" list="payment-mode-suggestions">
                    <datalist id="payment-mode-suggestions">
                        <option value="Cash">
                        <option value="Cheque">
                        <option value="UPI">
                        <option value="Bank Transfer">
                        <option value="NEFT">
                        <option value="RTGS">
                        <option value="Card">
                    </datalist>
                    @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Payment Description <span class="text-danger">*</span></label>
                    <input type="text" name="term_name" class="form-control @error('term_name') is-invalid @enderror"
                           value="{{ old('term_name') }}" placeholder="e.g. Net 30, 50% Advance">
                    @error('term_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Active <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Short Name</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code') }}" placeholder="Optional e.g. NET30">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Payment Term</button>
            <a href="{{ route('payment-terms.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
