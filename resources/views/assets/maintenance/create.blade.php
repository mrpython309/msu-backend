@extends('assets.asset-layout')

@section('asset_section')

<div class="card card-action mb-6">
  <div class="card-header align-items-center py-6">
    <h5 class="card-action-title mb-0">Add Maintenance</h5>
    <div class="card-action-element">
      <a class="btn btn-sm btn-primary text-white"
         href="{{ asset_show_section_url($asset, 'maintenance') }}">
        Back to Maintenance List
      </a>
    </div>
  </div>

  <div class="card-body">

    <form action="{{ route('asset.maintenance.store') }}" method="POST">
      @csrf

      <div class="row g-3">

        {{-- Maintenance Date --}}
        <div class="col-4">
          <label class="form-label form-label-required">
            Maintenance Date <span class="text-danger">*</span>
          </label>
          <input type="date"
                 name="maintenance_date"
                 class="form-control @error('maintenance_date') is-invalid @enderror"
                 value="{{ old('maintenance_date', date('Y-m-d')) }}">
          @error('maintenance_date')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>         

        {{-- Reported By --}}
        <div class="col-4">
          <label class="form-label">Reported By <span class="text-danger">*</span></label>
          <select name="reported_by"
                  class="form-select @error('reported_by') is-invalid @enderror" required>
            <option value="">Select User</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}"
                {{ old('reported_by') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
              </option>
            @endforeach
          </select>
          @error('reported_by')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>


        {{-- Vendor --}}
        <div class="col-4">
          <label class="form-label">Vendor <span class="text-danger">*</span></label>
          <select name="vendor_id"
                  class="form-select @error('vendor_id') is-invalid @enderror" required>
            <option value="">Select Vendor</option>
            @foreach($vendors as $vendor)
              <option value="{{ $vendor->id }}"
                {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                {{ $vendor->name }}
              </option>
            @endforeach
          </select>
          @error('vendor_id')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Priority --}}
        <div class="col-4">
          <label class="form-label form-label-required">
            Priority <span class="text-danger">*</span>
          </label>
          <select name="priority"
                  class="form-select @error('priority') is-invalid @enderror">
            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
          </select>
          @error('priority')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>


        {{-- Status --}}
        <div class="col-4">
          <label class="form-label form-label-required">
            Status <span class="text-danger">*</span>
          </label>
          <select name="status"
                  class="form-select @error('status') is-invalid @enderror">
            <option value="pending" {{ old('status') == 'low' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ old('status') == 'medium' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ old('status') == 'high' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ old('status') == 'critical' ? 'selected' : '' }}>Cancelled</option>
          </select>
          @error('status')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Cost --}}
        <div class="col-4">
          <label class="form-label">Maintenance Cost</label>
          <input type="number"
                 step="0.01"
                 name="maintenance_cost"
                 class="form-control @error('maintenance_cost') is-invalid @enderror"
                 value="{{ old('maintenance_cost') }}">
          @error('maintenance_cost')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Start Date --}}
        <div class="col-4">
          <label class="form-label">Start Date</label>
          <input type="date"
                 name="start_date"
                 class="form-control @error('start_date') is-invalid @enderror"
                 value="{{ old('start_date') }}">
          @error('start_date')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- End Date --}}
        <div class="col-4">
          <label class="form-label">End Date</label>
          <input type="date"
                 name="end_date"
                 class="form-control @error('end_date') is-invalid @enderror"
                 value="{{ old('end_date') }}">
          @error('end_date')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Notes --}}
        <div class="col-6">
          <label class="form-label">Maintenance Notes</label>
          <textarea name="maintenance_notes"
                    rows="3"
                    class="form-control @error('maintenance_notes') is-invalid @enderror">{{ old('maintenance_notes') }}</textarea>
          @error('maintenance_notes')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Remarks --}}
        <div class="col-6">
          <label class="form-label">Remarks</label>
          <textarea name="remarks"
                    rows="3"
                    class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks') }}</textarea>
          @error('remarks')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

      </div>

      <div class="pt-5 float-end">
        <input type="hidden" name="asset_id" value="{{ $asset->id }}">

        <button type="submit" class="btn btn-primary">
          Save Maintenance
        </button>

        <a href="{{ asset_show_section_url($asset, 'maintenance') }}"
           class="btn btn-secondary">
          Cancel
        </a>
      </div>

    </form>

  </div>
</div>

@endsection
