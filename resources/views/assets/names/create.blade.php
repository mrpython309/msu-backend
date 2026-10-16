@extends('layouts/contentNavbarLayout')

@section('title', 'Create Asset Name')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Create Asset Name
</h4>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Asset Name</h5>
        <a href="{{ route('asset-names.index') }}" class="btn btn-secondary">Back to list</a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('asset-names.store') }}">
          @csrf
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Asset Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="pt-2">
            <button type="submit" class="btn btn-primary">Create Asset Name</button>
            <a href="{{ route('asset-names.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

