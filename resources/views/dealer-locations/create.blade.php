@extends('layouts/contentNavbarLayout')

@section('title', 'Create Dealer Location')

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
        <span class="text-muted fw-light">Create Dealer Location</span>
    </h4>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">New Dealer Location</h5>
                    </div>
                    <a href="{{ route('dealer-locations.index') }}" class="btn btn-label-secondary">
                        <i class="bx bx-chevron-left me-1"></i> Back to list
                    </a>
                </div>
                <div class="card-body">
                    <div class="form-container">
                        <form method="POST" action="{{ route('dealer-locations.store') }}" id="dealerLocationForm">
                            @csrf

                            {{-- Basic Information --}}
                            <div class="form-section-title">
                                <i class="bx bx-store me-2"></i> Location Details
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label form-label-required" for="name">Location Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-required" for="code">Location Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required maxlength="50">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-required" for="company_id">Company</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
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
                                    <label class="form-label" for="address">Full Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Complete address">{{ old('address') }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="city">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="state">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="country">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="{{ old('country') }}">
                                </div>
                            </div>

                            {{-- Contact --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-phone-call me-2"></i> Contact
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="contact_number">Phone</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Status & Actions --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <label class="form-label form-label-required" for="status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 d-flex justify-content-end align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-2"></i> Save Location
                                    </button>
                                    <a href="{{ route('dealer-locations.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="bx bx-x me-2"></i> Cancel
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
    FormValidation.formValidation(document.getElementById('dealerLocationForm'), {
        fields: {
            name: { validators: { notEmpty: { message: 'Location name required' } } },
            code: { validators: { notEmpty: { message: 'Code required' } } },
            company_id: { validators: { notEmpty: { message: 'Company required' } } }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5(),
            submitButton: new FormValidation.plugins.SubmitButton(),
            defaultSubmit: new FormValidation.plugins.DefaultSubmit()
        }
    });
});
</script>
@endsection
