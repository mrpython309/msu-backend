@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Branch')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
    <style>
        .form-container { background: #f8f9fa; border-radius: 0.5rem; padding: 2rem; border: 1px solid #e3e6f0; }
        .form-label-required::after { content: " *"; color: #dc3545; }
        .form-section-title { color: #495057; font-weight: 600; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
    </style>
@endsection

@section('content')
    <h4 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Edit Branch</span>
    </h4>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Update Branch</h5>
                    </div>
                    <a href="{{ route('dealer-locations.index') }}" class="btn btn-label-secondary">
                        <i class="bx bx-chevron-left me-1"></i> Back to list
                    </a>
                </div>

                <div class="card-body">
                    <div class="form-container">

                        <form method="POST"
                              action="{{ route('branch.update', $branch) }}"
                              id="dealerLocationForm">

                            @csrf
                            @method('PUT')

                            {{-- Location Details --}}
                            <div class="form-section-title">
                                <i class="bx bx-store me-2"></i> Branch Details
                            </div>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label form-label-required">Branch Name</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $branch->name) }}"
                                           required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label form-label-required">Branch Code</label>
                                    <input type="text"
                                           name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code', $branch->code) }}"
                                           required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label form-label-required">Company</label>
                                    <select name="company_id"
                                            id="company_id"
                                            class="form-select select2 @error('company_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('company_id', $branch->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }} ({{ $company->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-map me-2"></i> Address
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address"
                                              class="form-control"
                                              rows="2">{{ old('address', $branch->address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control"
                                           value="{{ old('city', $branch->city) }}" placeholder="City">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control"
                                           value="{{ old('state', $branch->state) }}" placeholder="State">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control"
                                           value="{{ old('country', $branch->country) }}" placeholder="Country">
                                </div>
                            </div>

                            {{-- Contact --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-phone-call me-2"></i> Contact
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control"
                                           value="{{ old('contact_person', $branch->contact_person) }}"
                                           placeholder="Contact Person">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="contact_number" class="form-control"
                                           value="{{ old('contact_number', $branch->contact_number) }}"
                                           placeholder="Phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $branch->email) }}"
                                           placeholder="Email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">GST</label>
                                    <input type="text" name="gst_number"
                                           class="form-control @error('gst_number') is-invalid @enderror"
                                           value="{{ old('gst_number', $branch->gst_number) }}"
                                           placeholder="GST">
                                    @error('gst_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <label class="form-label form-label-required">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active"
                                            {{ old('status', $branch->status) === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $branch->status) === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-flex justify-content-end gap-2">
                                    <button class="btn btn-primary">
                                        <i class="bx bx-save me-2"></i> Update
                                    </button>
                                    <a href="{{ route('branch.index') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
$(function() {
    if (typeof $.fn.select2 === 'function') {
        $('#company_id').select2({ placeholder: 'Select Company', allowClear: true, width: '100%' });
    }
});
</script>
@endsection
