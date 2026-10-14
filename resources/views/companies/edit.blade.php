@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Company')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
<style>
  .form-container {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 2rem;
    border: 1px solid #e3e6f0;
    margin-bottom: 1.5rem;
  }
  .form-label-required::after {
    content: " *";
    color: #dc3545;
  }
  .form-section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
  }
</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Edit Company</span>
</h4>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Edit Company Details</h5>
        </div>
        <a href="{{ route('companies.index') }}" class="btn btn-label-secondary">
          <i class="bx bx-chevron-left me-1"></i> Back to list
        </a>
      </div>

      <div class="card-body">
        <div class="form-container">
          <form method="POST"
                action="{{ route('companies.update', $company->id) }}"
                id="companyForm"
                enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <div class="form-section-title">
              <i class="bx bx-building-house me-2"></i> Basic Information
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label form-label-required">Company Name</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $company->name) }}"
                       required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label form-label-required">Company Code</label>
                <input type="text"
                       name="code"
                       class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $company->code) }}"
                       required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Upload Company Logo</label>
                @if($company->logo_url)
                    <div class="mb-2">
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }} logo" style="max-height: 64px; max-width: 160px; object-fit: contain;">
                    </div>
                @endif
                <input type="file"
                       name="logo"
                       class="form-control @error('logo') is-invalid @enderror"
                       accept="image/jpeg,image/png,image/webp,image/jpg">
                <small class="text-muted">Leave empty to keep current logo. JPG, PNG, or WEBP. Max 10MB.</small>
                @error('logo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            {{-- Address --}}
            <div class="form-section-title mt-4">
              <i class="bx bx-map me-2"></i> Address Details
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Address Line 1</label>
                <input type="text" name="address_line_1"
                       class="form-control"
                       value="{{ old('address_line_1', $company->address_line_1) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">Address Line 2</label>
                <input type="text" name="address_line_2"
                       class="form-control"
                       value="{{ old('address_line_2', $company->address_line_2) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">City</label>
                <input type="text" name="city"
                       class="form-control"
                       value="{{ old('city', $company->city) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">State</label>
                <input type="text" name="state"
                       class="form-control"
                       value="{{ old('state', $company->state) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Country</label>
                <input type="text" name="country"
                       class="form-control"
                       value="{{ old('country', $company->country) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Pincode</label>
                <input type="text" name="pincode"
                       class="form-control"
                       value="{{ old('pincode', $company->pincode) }}">
              </div>
            </div>

            {{-- Contact --}}
            <div class="form-section-title mt-4">
              <i class="bx bx-phone-call me-2"></i> Contact Information
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person"
                       class="form-control"
                       value="{{ old('contact_person', $company->contact_person) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number"
                       class="form-control"
                       value="{{ old('contact_number', $company->contact_number) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email"
                       class="form-control"
                       value="{{ old('email', $company->email) }}">
              </div>
            </div>

            {{-- Status --}}
            <div class="form-section-title mt-4">
              <i class="bx bx-toggle-right me-2"></i> Company Status
            </div>

            <div class="col-md-6">
              <label class="form-label form-label-required">Status</label>
              <select name="status" class="form-select" required>
                <option value="active" {{ $company->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $company->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            {{-- Buttons --}}
            <div class="row mt-4 pt-3 border-top">
              <div class="col-12 d-flex justify-content-end gap-2">
                <button class="btn btn-primary">
                  <i class="bx bx-save me-2"></i> Update Company
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
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
