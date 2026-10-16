@extends('layouts/contentNavbarLayout')

@section('title', 'Branch Details')

@section('content')

<div class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
  <div class="mb-2 mb-sm-0">
    <h4 class="mb-1">Branch Details</h4>
  </div>
  <a href="{{ route('branch.index') }}" class="btn btn-secondary">Back to list</a>
</div>

<div class="row">
  <div class="col-12">

    <div class="card mb-6">

      {{-- Header --}}
      <div class="card-header d-flex flex-wrap justify-content-between gap-4">
        <div class="card-title mb-0 me-1">
          <h5 class="mb-2">
            {{ $branch->name }}
            <span class="badge bg-label-secondary">
              {{ $branch->code ?? '--' }}
            </span>
          </h5>

          @if($branch->status === 'active')
            <span class="badge bg-label-success">Active</span>
          @else
            <span class="badge bg-label-danger">Inactive</span>
          @endif
        </div>

        
      </div>

      {{-- Body --}}
      <div class="card-body">
        <div class="row">

          {{-- Branch Code --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-purchase-tag"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Branch Code</small>
                <span class="fw-normal">{{ $branch->code ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Company --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-buildings"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Company</small>
                <span class="fw-normal">
                  {{ $branch->company->name ?? '--' }}
                </span>
              </div>
            </div>
          </div>

          {{-- Email --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-envelope"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Email</small>
                <span class="fw-normal">{{ $branch->email ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Contact Number --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-phone"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Contact Number</small>
                <span class="fw-normal">{{ $branch->contact_number ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Contact Person --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-user"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Contact Person</small>
                <span class="fw-normal">{{ $branch->contact_person ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- GST --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-receipt"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">GST</small>
                <span class="fw-normal">{{ $branch->gst_number ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Address --}}
          <div class="col-md-6">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3 mt-1">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-map"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Address</small>
                <span class="fw-normal">
                  {{ $branch->address ?? '--' }}
                </span>
              </div>
            </div>
          </div>

          {{-- City --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-map-pin"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">City</small>
                <span class="fw-normal">{{ $branch->city ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- State --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-map-alt"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">State</small>
                <span class="fw-normal">{{ $branch->state ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Country --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-globe"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Country</small>
                <span class="fw-normal">{{ $branch->country ?? '--' }}</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

{{-- Tabs --}}
<div class="row">
  <div class="col-md-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2">

        <li class="nav-item">
          <a class="nav-link active">
            <i class="bx bx-map me-1"></i> Locations
            <span class="badge bg-label-primary ms-1">
              {{ $branch->locations->count() }}
            </span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link">
            <i class="bx bx-user me-1"></i> Users
            <span class="badge bg-label-info ms-1">
              {{ $branch->users->count() }}
            </span>
          </a>
        </li>

      </ul>
    </div>

    {{-- Later you can @yield('branch_section') --}}
  </div>
</div>

@endsection
