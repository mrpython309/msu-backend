@extends('layouts/contentNavbarLayout')

@section('title', 'Vendor Details')

@section('content')

<div class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
  <div class="mb-2 mb-sm-0">
    <h4 class="mb-1">Vendor Details</h4>
  </div>
  <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Back to list</a>
</div>

<div class="row">
  <div class="col-12">

    <div class="card mb-6">

      {{-- Header --}}
      <div class="card-header d-flex flex-wrap justify-content-between gap-4">
        <div class="card-title mb-0 me-1">
          <h5 class="mb-2">
            {{ $vendor->name }}
          </h5>
          @if($vendor->company_code)
            <div class="text-muted mb-2">Vendor Code: <strong>{{ $vendor->company_code }}</strong></div>
          @endif

          @if($vendor->status === 'active')
            <span class="badge bg-label-success">Active</span>
          @else
            <span class="badge bg-label-danger">Inactive</span>
          @endif
        </div>

         
      </div>

      {{-- Body --}}
      <div class="card-body">
        <div class="row">

          {{-- Vendor Code --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-barcode"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Vendor Code</small>
                <span class="fw-normal">{{ $vendor->company_code ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Vendor Name --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-buildings"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Vendor Name</small>
                <span class="fw-normal">{{ $vendor->name ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Phone Number --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-phone"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Phone Number</small>
                <span class="fw-normal">{{ $vendor->phone_number ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Contact Person --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-user-circle"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Contact Person</small>
                <span class="fw-normal">{{ $vendor->contact_person ?? '--' }}</span>
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
                <span class="fw-normal">{{ $vendor->email ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- GST Number --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-receipt"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">GST Number</small>
                <span class="fw-normal">{{ $vendor->gst_number ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- PAN Number --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-id-card"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">PAN Number</small>
                <span class="fw-normal">{{ $vendor->pan_number ?? '--' }}</span>
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
                  {{ $vendor->address ?? '--' }}
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
                <span class="fw-normal">{{ $vendor->city ?? '--' }}</span>
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
                <span class="fw-normal">{{ $vendor->state ?? '--' }}</span>
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
                <span class="fw-normal">{{ $vendor->country ?? '--' }}</span>
              </div>
            </div>
          </div>

          {{-- Notes --}}
          <div class="col-md-6">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3 mt-1">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-note"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Notes</small>
                <span class="fw-normal">
                  {{ $vendor->notes ?? '--' }}
                </span>
              </div>
            </div>
          </div>

          {{-- Created By --}}
          <div class="col-md-3">
            <div class="d-flex align-items-center mb-4">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-user"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block mb-1">Created By</small>
                <span class="fw-normal">
                  {{ $vendor->user->name ?? '--' }}
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

@endsection
