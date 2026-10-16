@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Dealer Location')

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
        <span class="text-muted fw-light">Edit Dealer Location</span>
    </h4>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Update Dealer Location</h5>
                    </div>
                    <a href="{{ route('dealer-locations.index') }}" class="btn btn-label-secondary">
                        <i class="bx bx-chevron-left me-1"></i> Back to list
                    </a>
                </div>

                <div class="card-body">
                    <div class="form-container">

                        <form method="POST"
                              action="{{ route('dealer-locations.update', $dealerLocation) }}"
                              id="dealerLocationForm">

                            @csrf
                            @method('PUT')

                            {{-- Location Details --}}
                            <div class="form-section-title">
                                <i class="bx bx-store me-2"></i> Location Details
                            </div>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label form-label-required">Location Name</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $dealerLocation->name) }}"
                                           required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label form-label-required">Location Code</label>
                                    <input type="text"
                                           name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code', $dealerLocation->code) }}"
                                           required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label form-label-required">Company</label>
                                    <select name="company_id"
                                            class="form-select @error('company_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('company_id', $dealerLocation->company_id) == $company->id ? 'selected' : '' }}>
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
                                    <textarea name="address"
                                              class="form-control"
                                              rows="2">{{ old('address', $dealerLocation->address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="city" class="form-control"
                                           value="{{ old('city', $dealerLocation->city) }}" placeholder="City">
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="state" class="form-control"
                                           value="{{ old('state', $dealerLocation->state) }}" placeholder="State">
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="country" class="form-control"
                                           value="{{ old('country', $dealerLocation->country) }}" placeholder="Country">
                                </div>
                            </div>

                            {{-- Contact --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-phone-call me-2"></i> Contact
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="tel" name="contact_number" class="form-control"
                                           value="{{ old('contact_number', $dealerLocation->contact_number) }}"
                                           placeholder="Phone">
                                </div>

                                <div class="col-md-6">
                                    <input type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $dealerLocation->email) }}"
                                           placeholder="Email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <label class="form-label form-label-required">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active"
                                            {{ old('status', $dealerLocation->status) === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $dealerLocation->status) === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-flex justify-content-end gap-2">
                                    <button class="btn btn-primary">
                                        <i class="bx bx-save me-2"></i> Update
                                    </button>
                                    <a href="{{ route('dealer-locations.index') }}" class="btn btn-outline-secondary">
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
