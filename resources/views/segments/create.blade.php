@extends('layouts/contentNavbarLayout')

@section('title', 'Create Segment')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
    <style>
        .form-modern {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .form-section {
            padding: 2rem 2.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .input-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fafbfc;
        }
        .input-modern:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .status-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.75rem;
            margin: 1.5rem 0;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.2s ease;
        }
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .preview-simple {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 0.75rem;
        }

        .sidebar-help {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            height: fit-content;
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <!-- Main Form Card -->
            <div class="form-modern mb-4">
                <div class="p-4 border-bottom" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bx bx-plus text-white" style="font-size: 1.25rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Create New Segment</h5>
                            </div>
                        </div>
                        <a href="{{ route('segments.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('segments.store') }}" id="segmentForm" class="p-0">
                    @csrf

                    <!-- Section 1: Basic Info -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-category text-blue-600"></i>
                            Basic Information
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium mb-2">Segment Code *</label>
                                <input type="text"
                                       class="form-control input-modern @error('code') is-invalid @enderror"
                                       id="code" name="code"
                                       value="{{ old('code') }}"
                                       required maxlength="10"
                                       placeholder="SUV">
                                @error('code')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                                <div id="codePreview" class="preview-simple d-none"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium mb-2">Segment Name *</label>
                                <input type="text"
                                       class="form-control input-modern @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="Sport Utility Vehicle">
                                @error('name')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Status -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-toggle-right text-green-600"></i>
                            Status
                        </div>

                        <div class="status-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="statusSwitch" checked>
                                        <label class="form-check-label fw-semibold fs-5" for="statusSwitch">
                                            Make segment active
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-lg @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-4 border-top bg-gray-50">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('segments.index') }}" class="btn btn-outline-secondary px-5 py-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary-modern px-5 py-2">
                                <i class="bx bx-check me-2"></i>Create Segment
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Simple Help Sidebar -->
            <div class="row">
                <div class="col-md-6">
                    <div class="sidebar-help p-4">
                        <h6 class="fw-semibold mb-3">💡 Quick Tips</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 p-2 rounded bg-blue-25"><i class="bx bx-check text-blue-600 me-2"></i>Short codes: SUV, XUV500, Thar</li>
                            <li class="mb-2 p-2 rounded bg-green-25"><i class="bx bx-check text-green-600 me-2"></i>Active = dropdown visible</li>
                            <li class="p-2 rounded bg-gray-25"><i class="bx bx-check text-gray-600 me-2"></i>Max 10 chars code</li>
                        </ul>
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
    // Simple clean preview
    $('#code').on('input', function() {
        const code = $(this).val().toUpperCase().trim();
        const preview = $('#codePreview');
        if (code) {
            preview.removeClass('d-none').text(code);
        } else {
            preview.addClass('d-none');
        }
    });

    // Status sync
    $('#statusSwitch').on('change', function() {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

    FormValidation.formValidation(document.getElementById('segmentForm'), {
        fields: {
            code: { validators: { notEmpty: true, stringLength: {min: 2, max: 10} } },
            name: { validators: { notEmpty: true } }
        }
    });
});
</script>
@endsection
