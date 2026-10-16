@extends('assets.asset-layout')

@section('asset_section')

<div class="card card-action mb-6">
    <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Edit Warranty</h5>
        <div class="card-action-element">
            <a class="btn btn-sm btn-primary text-white"
               href="{{ asset_show_section_url($asset, 'warranties') }}">
                Back to Warranties List
            </a>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('asset.warranty.update', $warranty) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Covered Parts --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        Covered Parts <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="covered_parts"
                           class="form-control @error('covered_parts') is-invalid @enderror"
                           value="{{ old('covered_parts', $warranty->covered_parts) }}"
                           required>
                    @error('covered_parts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Warranty Type --}}
                <div class="col-4">
                    <label class="form-label form-label-required">
                        Warranty Type <span class="text-danger">*</span>
                    </label>
                    <select name="warranty_type"
                            class="form-select @error('warranty_type') is-invalid @enderror"
                            required>
                        <option value="">Select</option>
                        @foreach ($warrentytypes as $warrentyType)
                            <option value="{{ $warrentyType->value }}"
                                @selected(old('warranty_type', $warranty->warranty_type->value) == $warrentyType->value)>
                                {{ $warrentyType->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('warranty_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Warranty Number --}}
                <div class="col-4">
                    <label class="form-label">Warranty Number</label>
                    <input type="text"
                           name="warranty_number"
                           class="form-control @error('warranty_number') is-invalid @enderror"
                           value="{{ old('warranty_number', $warranty->warranty_number) }}">
                    @error('warranty_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Expire Date --}}
                <div class="col-4">
                    <label class="form-label">Expire Date</label>
                    <input type="date"
                           name="warranty_expire_date"
                           class="form-control @error('warranty_expire_date') is-invalid @enderror"
                           value="{{ old('warranty_expire_date', optional($warranty->warranty_expire_date)->format('Y-m-d')) }}">
                    @error('warranty_expire_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Max Usage --}}
                <div class="col-4">
                    <label class="form-label">Max Usage</label>
                    <div class="input-group">
                        <input type="number"
                               name="max_usage"
                               placeholder="Ex. 1000"
                               value="{{ old('max_usage', $warranty->max_usage) }}"
                               class="form-control @error('max_usage') is-invalid @enderror">
                        <input type="text"
                               name="usage_metric"
                               placeholder="Ex. hours, km, days"
                               value="{{ old('usage_metric', $warranty->usage_metric) }}"
                               class="form-control @error('usage_metric') is-invalid @enderror">
                    </div>
                    @error('max_usage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('usage_metric') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Notes --}}
                <div class="col-4">
                    <label class="form-label">Notes</label>
                    <textarea name="notes"
                              rows="2"
                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $warranty->notes) }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>

            {{-- Buttons --}}
            <div class="pt-5 float-end">
                <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ asset_show_section_url($asset, 'warranties') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
