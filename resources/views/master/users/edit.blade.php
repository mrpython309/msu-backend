@extends('layouts/contentNavbarLayout')

@section('title', 'Edit User')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit User</h4>
    <div>
      <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Back to Users
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">User Information</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email', $user->email) }}" required>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" id="password" name="password"
                class="form-control @error('password') is-invalid @enderror">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bx bx-hide" id="passwordIcon"></i>
              </button>
            </div>
            <div class="form-text">Password must be at least 8 characters long</div>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <div class="input-group">
              <input type="password" id="password_confirmation" name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror">
              <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                <i class="bx bx-hide" id="passwordConfirmIcon"></i>
              </button>
            </div>
            @error('password_confirmation')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="row">
          <div class="col-12 mb-3">
            <div class="card bg-light">
              <div class="card-body">
                <h6 class="card-title mb-2">Account Information</h6>
                <div class="row">
                  <div class="col-md-4">
                    <strong>User ID:</strong> {{ $user->id }}
                  </div>
                  <div class="col-md-4">
                    <strong>Created:</strong> {{ $user->created_at->format('M d, Y h:i A') }}
                  </div>
                  <div class="col-md-4">
                    <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y h:i A') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          
        </div>

        <div class="row">
          <div class="col-12">
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-check me-1"></i> Update User
              </button>
              <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-x me-1"></i> Cancel
              </a>
              <a href="{{ route('users.roles.edit', $user->id) }}" class="btn btn-warning">
                <i class="bx bx-user-check me-1"></i> Manage Roles
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if ($user->id === auth()->id())
    <div class="alert alert-warning mt-3">
      <i class="bx bx-info-circle me-2"></i>
      <strong>Note:</strong> You are currently editing your own account. Be careful when changing your email or password.
    </div>
  @endif
@endsection

@push('styles')
  <style>
    .dropdown-menu {
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .dropdown-menu .form-check {
      margin: 0;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #f8f9fa;
      transition: background-color 0.15s ease-in-out;
    }

    .dropdown-menu .form-check:last-child {
      border-bottom: none;
    }

    .dropdown-menu .form-check:hover {
      background-color: #f8f9fa;
    }

    .dropdown-menu .form-check-input {
      margin-top: 0.25rem;
      margin-right: 0.5rem;
    }

    .dropdown-menu .form-check-input:checked {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }

    .dropdown-menu .form-check-input:checked + .form-check-label {
      color: #0d6efd;
      font-weight: 500;
    }

    .dropdown-menu .form-check-label {
      cursor: pointer;
      color: #495057;
      font-size: 0.875rem;
    }

    .dropdown-toggle {
      min-height: 38px;
      padding: 0.5rem 0.75rem;
    }

    .dropdown-toggle:focus {
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    #selectedCount {
      font-weight: 500;
      color: #495057;
    }

    .border-bottom {
      border-bottom: 2px solid #dee2e6 !important;
    }
  </style>
@endpush

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle password visibility
      function setupPasswordToggle(buttonId, inputId, iconId) {
        const toggleBtn = document.getElementById(buttonId);
        const passwordInput = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (toggleBtn && passwordInput && icon) {
          toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'bx bx-show' : 'bx bx-hide';
          });
        }
      }

      setupPasswordToggle('togglePassword', 'password', 'passwordIcon');
      setupPasswordToggle('togglePasswordConfirm', 'password_confirmation', 'passwordConfirmIcon');

      // Multi-select stores functionality
      const selectAllCheckbox = document.getElementById('selectAll');
      const storeCheckboxes = document.querySelectorAll('.store-checkbox');
      const selectedCountSpan = document.getElementById('selectedCount');

      function updateCounter() {
        const checkedCount = document.querySelectorAll('.store-checkbox:checked').length;
        selectedCountSpan.textContent = `${checkedCount} Selected`;

        if (checkedCount === storeCheckboxes.length) {
          selectAllCheckbox.checked = true;
          selectAllCheckbox.indeterminate = false;
        } else if (checkedCount > 0) {
          selectAllCheckbox.checked = false;
          selectAllCheckbox.indeterminate = true;
        } else {
          selectAllCheckbox.checked = false;
          selectAllCheckbox.indeterminate = false;
        }
      }

      selectAllCheckbox.addEventListener('change', function() {
        storeCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateCounter();
      });

      storeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCounter);
      });

      document.querySelector('.dropdown-menu').addEventListener('click', function(e) {
        e.stopPropagation();
      });

      updateCounter();

      // Show confirmation if user is changing their own account
      @if ($user->id === auth()->id())
        const form = document.querySelector('form');
        if (form) {
          form.addEventListener('submit', function(e) {
            const passwordChanged = document.getElementById('password').value.length > 0;
            const emailChanged = document.getElementById('email').value !== '{{ $user->email }}';

            if (passwordChanged || emailChanged) {
              if (!confirm('You are updating your own account. Are you sure you want to continue?')) {
                e.preventDefault();
              }
            }
          });
        }
      @endif

      // Multi-select branches functionality
      const selectAllBranches = document.getElementById('selectAllBranches');
      const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
      const branchesSelectedCount = document.getElementById('branchSelectedCount');

      function updateBranchesCounter() {
        const count = document.querySelectorAll('.branch-checkbox:checked').length;
        branchesSelectedCount.textContent = `${count} Selected`;

        if (count === branchCheckboxes.length) {
          selectAllBranches.checked = true;
          selectAllBranches.indeterminate = false;
        } else if (count > 0) {
          selectAllBranches.checked = false;
          selectAllBranches.indeterminate = true;
        } else {
          selectAllBranches.checked = false;
          selectAllBranches.indeterminate = false;
        }
      }

      if (selectAllBranches) {
        selectAllBranches.addEventListener('change', function() {
          branchCheckboxes.forEach(cb => cb.checked = this.checked);
          updateBranchesCounter();
        });

        branchCheckboxes.forEach(cb => cb.addEventListener('change', updateBranchesCounter));

        updateBranchesCounter();
      }

    });
  </script>
@endpush
