@extends('assets.asset-layout')

@section('asset_section')

<div class="card card-action mb-6">
    <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Edit Asset Replacement</h5>
        <div class="card-action-element">
            <a class="btn btn-sm btn-primary text-white"
               href="{{ asset_show_section_url($asset, 'replacements') }}">
                Back to Replacement List
            </a>
        </div>
    </div>

    <div class="card-body">

        <form action="{{ route('asset.replacement.update', $asset_replacement) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Replacement Date --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        Replacement Date <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="replacement_date"
                           class="form-control @error('replacement_date') is-invalid @enderror"
                           value="{{ old('replacement_date', $asset_replacement->replacement_date?->format('Y-m-d')) }}"
                           required>
                    @error('replacement_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- New Serial --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        New Serial No <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="new_serial_no"
                           class="form-control @error('new_serial_no') is-invalid @enderror"
                           value="{{ old('new_serial_no', $asset_replacement->new_serial_no) }}"
                           required>
                    @error('new_serial_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- New Condition --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        New Condition <span class="text-danger">*</span>
                    </label>
                    <select name="new_condition"
                            class="form-select @error('new_condition') is-invalid @enderror"
                            required>
                        <option value="">Select</option>
                        @foreach ($conditions as $condition)
                            <option value="{{ $condition->value }}"
                                {{ old('new_condition', $asset_replacement->new_condition) == $condition->value ? 'selected' : '' }}>
                                {{ $condition->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('new_condition')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- New Operational Status --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        New Operational Status <span class="text-danger">*</span>
                    </label>
                    <select name="new_operational_status"
                            class="form-select @error('new_operational_status') is-invalid @enderror"
                            required>
                        <option value="">Select</option>
                        @foreach ($operations_status as $operational_status)
                            <option value="{{ $operational_status->value }}"
                                {{ old('new_operational_status', $asset_replacement->new_operational_status) == $operational_status->value ? 'selected' : '' }}>
                                {{ $operational_status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('new_operational_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Replacement Reason --}}
                <div class="col-8">
                    <label class="form-label form-label-required">
                        Replacement Reason <span class="text-danger">*</span>
                    </label>
                    <textarea name="replacement_reason"
                              rows="3"
                              class="form-control @error('replacement_reason') is-invalid @enderror"
                              required>{{ old('replacement_reason', $asset_replacement->replacement_reason) }}</textarea>
                    @error('replacement_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="pt-5 float-end">
                <input type="hidden" name="asset_id" value="{{ $asset->id }}">

                <button type="submit" class="btn btn-primary">
                    Update Replacement
                </button>

                <a href="{{ asset_show_section_url($asset, 'replacements') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>
</div>

@endsection
