@extends('layouts/contentNavbarLayout')

@section('title', 'Create User')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New User</h4>
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
      <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name') }}" required autofocus>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email') }}" required>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" id="password" name="password"
                class="form-control @error('password') is-invalid @enderror" required>
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
            <label for="password_confirmation" class="form-label">Confirm Password <span
                class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" id="password_confirmation" name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror" required>
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
         

        
        </div>
      </div>

        <div class="row">
          <div class="col-12">
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-check me-1"></i> Create User
              </button>
              <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-x me-1"></i> Cancel
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
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


      // Password strength indicator (optional enhancement)
      const passwordInput = document.getElementById('password');
      if (passwordInput) {
        passwordInput.addEventListener('input', function() {
          const password = this.value;
          let strength = 0;

          if (password.length >= 8) strength++;
          if (password.match(/[a-z]/)) strength++;
          if (password.match(/[A-Z]/)) strength++;
          if (password.match(/[0-9]/)) strength++;
          if (password.match(/[^a-zA-Z0-9]/)) strength++;

          // You can add visual feedback here if needed
        });
      }


    });
  </script>
@endpush
