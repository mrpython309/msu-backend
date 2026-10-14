@extends('layouts/contentNavbarLayout')

@section('title', 'Create Company')

@section('vendor-style')
    {{-- FormValidation --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
    <style>
        /* Full width form containers */
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
        .avatar-preview {
            width: 80px;
            height: 80px;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <h4 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"> Create Company</span>
    </h4>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">New Company Details</h5>
                    </div>
                    <a href="{{ route('companies.index') }}" class="btn btn-label-secondary">
                        <i class="bx bx-chevron-left me-1"></i> Back to list
                    </a>
                </div>

                <div class="card-body">
                    {{-- Full Width Form Container --}}
                    <div class="form-container">
                        <form method="POST" action="{{ route('companies.store') }}" id="companyForm" enctype="multipart/form-data">
                            @csrf

                            {{-- Basic Information Section --}}
                            <div class="form-section-title">
                                <i class="bx bx-building-house me-2"></i> Basic Information
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-required" for="name">Company Name</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name"
                                           value="{{ old('name') }}"
                                           required
                                           placeholder="Enter full company name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="code">Company Code</label>
                                    <input type="text"
                                           class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code"
                                           value="{{ old('code') }}"
                                           required
                                           maxlength="50"
                                           placeholder="Enter unique code (e.g., COMP001)">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="logo">Upload Company Logo</label>
                                    <input type="file"
                                           class="form-control @error('logo') is-invalid @enderror"
                                           id="logo" name="logo"
                                           accept="image/jpeg,image/png,image/webp,image/jpg">
                                    <small class="text-muted">JPG, PNG, or WEBP. Max 10MB.</small>
                                    @error('logo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Address Section --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-map me-2"></i> Address Details
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="address_line_1">Address Line 1</label>
                                    <input type="text"
                                           class="form-control"
                                           id="address_line_1" name="address_line_1"
                                           value="{{ old('address_line_1') }}"
                                           placeholder="Street address">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="address_line_2">Address Line 2</label>
                                    <input type="text"
                                           class="form-control"
                                           id="address_line_2" name="address_line_2"
                                           value="{{ old('address_line_2') }}"
                                           placeholder="Building/Area">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="city">City</label>
                                    <input type="text"
                                           class="form-control"
                                           id="city" name="city"
                                           value="{{ old('city') }}"
                                           placeholder="e.g., Chennai">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="state">State</label>
                                    <input type="text"
                                           class="form-control"
                                           id="state" name="state"
                                           value="{{ old('state') }}"
                                           placeholder="e.g., Tamil Nadu">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label" for="country">Country</label>
                                    <input type="text"
                                           class="form-control"
                                           id="country" name="country"
                                           value="{{ old('country') }}"
                                           placeholder="e.g., India">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label" for="pincode">Pincode</label>
                                    <input type="text"
                                           class="form-control"
                                           id="pincode" name="pincode"
                                           value="{{ old('pincode') }}"
                                           placeholder="600001">
                                </div>
                            </div>

                            {{-- Contact Section --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-phone-call me-2"></i> Contact Information
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="contact_person">Contact Person</label>
                                    <input type="text"
                                           class="form-control"
                                           id="contact_person" name="contact_person"
                                           value="{{ old('contact_person') }}"
                                           placeholder="Primary contact name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="contact_number">Contact Number</label>
                                    <input type="tel"
                                           class="form-control"
                                           id="contact_number" name="contact_number"
                                           value="{{ old('contact_number') }}"
                                           placeholder="+91 9876543210">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email"
                                           value="{{ old('email') }}"
                                           placeholder="company@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Status Section --}}
                            <div class="form-section-title mt-4">
                                <i class="bx bx-toggle-right me-2"></i> Company Status
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-required" for="status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bx bx-save me-2"></i> Save Company
                                        </button>
                                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary px-4">
                                            <i class="bx bx-x me-2"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/bootstrap5.min.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            // FormValidation integration
            const fv = FormValidation.formValidation(
                document.getElementById('companyForm'),
                {
                    fields: {
                        name: {
                            validators: {
                                notEmpty: { message: 'Company name is required' },
                                stringLength: { min: 2, max: 255, message: 'Name must be 2-255 characters' }
                            }
                        },
                        code: {
                            validators: {
                                notEmpty: { message: 'Company code is required' },
                                regexp: { regexp: /^[A-Z0-9]{3,50}$/, message: 'Code must be 3-50 uppercase letters/numbers' }
                            }
                        },
                        email: {
                            validators: {
                                emailAddress: { message: 'Please enter valid email' }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.col-md-6'
                        }),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
                }
            );

            // Auto-generate code preview
            $('#name').on('input', function() {
                const name = $(this).val();
                if (name && !$('#code').val()) {
                    const code = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8) || 'COMP';
                    $('#code').attr('placeholder', code + '001');
                }
            });
        });
    </script>
@endsection
