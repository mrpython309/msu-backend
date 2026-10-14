@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Segment')

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
        .form-section { padding: 2rem 2.5rem; border-bottom: 1px solid #f3f4f6; }
        .form-section:last-child { border-bottom: none; }
        .section-title {
            font-size: 1.125rem; font-weight: 600; color: #111827;
            margin-bottom: 1.5rem; padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .input-modern {
            border: 2px solid #e5e7eb; border-radius: 12px;
            padding: 0.875rem 1.25rem; background: #fafbfc;
        }
        .input-modern:focus {
            border-color: #3b82f6; background: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
        }
        .status-card {
            background: linear-gradient(135deg,#f8fafc,#f1f5f9);
            border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.75rem;
        }
        .btn-primary-modern {
            background: linear-gradient(135deg,#3b82f6,#1d4ed8);
            border-radius: 12px; padding: .875rem 2rem;
            color: #fff; font-weight: 600;
        }
        .preview-simple {
            background: #3b82f6; color: #fff;
            padding: .75rem 1.25rem; border-radius: 10px;
            font-weight: 600; margin-top: .75rem;
        }
    </style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-xxl-9">

        <div class="form-modern mb-4">
            <div class="p-4 border-bottom" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-3 align-items-center">
                        <div style="width:48px;height:48px;background:#3b82f6;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="bx bx-edit text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Edit Segment</h5>
                        </div>
                    </div>
                    <a href="{{ route('segments.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back
                    </a>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('segments.update', $segment) }}"
                  id="segmentForm">
                @csrf
                @method('PUT')

                {{-- Basic Info --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="bx bx-category"></i> Basic Information
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="fw-medium mb-2">Segment Code *</label>
                            <input type="text"
                                   name="code"
                                   class="form-control input-modern @error('code') is-invalid @enderror"
                                   value="{{ old('code', $segment->code) }}"
                                   maxlength="10" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="codePreview" class="preview-simple">{{ $segment->code }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-medium mb-2">Segment Name *</label>
                            <input type="text"
                                   name="name"
                                   class="form-control input-modern @error('name') is-invalid @enderror"
                                   value="{{ old('name', $segment->name) }}"
                                   required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="bx bx-toggle-right"></i> Status
                    </div>

                    <div class="status-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="statusSwitch"
                                           {{ old('status', $segment->status) === 'active' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold">
                                        Segment Active
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="status" id="status" class="form-select">
                                    <option value="active" {{ old('status',$segment->status)=='active'?'selected':'' }}>Active</option>
                                    <option value="inactive" {{ old('status',$segment->status)=='inactive'?'selected':'' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="p-4 border-top">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('segments.index') }}" class="btn btn-outline-secondary px-5">Cancel</a>
                        <button class="btn btn-primary-modern px-5">
                            <i class="bx bx-save me-2"></i>Update Segment
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {

    $('#code').on('input', function () {
        $('#codePreview').text($(this).val().toUpperCase());
    });

    $('#statusSwitch').on('change', function () {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

});
</script>
@endsection
