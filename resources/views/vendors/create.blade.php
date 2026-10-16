@extends('layouts/contentNavbarLayout')

@section('title', 'Create Vendor')

@section('page-style')
<style>
    .form-container {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 2rem;
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }
    .form-section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light">Create Vendor</span>
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ isset($vendor) ? 'Edit Vendor' : 'New Vendor' }}</h5>
        <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="card-body">
        <div class="form-container">
            <form action="{{ isset($vendor) ? route('vendor.update', $vendor->id) : route('vendor.store') }}" method="POST">
                @csrf
                @if(isset($vendor))
                    @method('PUT')
                @endif

                {{-- VENDOR DETAILS --}}
                <div class="form-section-title">Vendor Details</div>
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Vendor Code</label>
                        <input type="text" class="form-control"
                               value="{{ $vendor->company_code ?? 'Auto-generated (A001...)' }}"
                               disabled>
                        <small class="text-muted">First letter of name + sequence (Avi → A001, John → J001).</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" required
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $vendor->name ?? '') }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" required
                               class="form-control @error('phone_number') is-invalid @enderror"
                               value="{{ old('phone_number', $vendor->phone_number ?? '') }}">
                        @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person"
                               class="form-control @error('contact_person') is-invalid @enderror"
                               value="{{ old('contact_person', $vendor->contact_person ?? '') }}">
                        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" inputmode="email" autocomplete="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $vendor->email ?? '') }}"
                               placeholder="Optional">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number"
                               class="form-control @error('gst_number') is-invalid @enderror"
                               value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                        @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">PAN Number</label>
                        <input type="text" name="pan_number"
                               class="form-control @error('pan_number') is-invalid @enderror"
                               value="{{ old('pan_number', $vendor->pan_number ?? '') }}"
                               placeholder="e.g. ABCDE1234F"
                               maxlength="20">
                        @error('pan_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $vendor->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <select name="country" id="country" class="form-select @error('country') is-invalid @enderror">
                            <option value="">Select Country</option>
                        </select>
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <select name="state" id="state" class="form-select @error('state') is-invalid @enderror">
                            <option value="">Select State</option>
                        </select>
                        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <select name="city" id="city" class="form-select @error('city') is-invalid @enderror">
                            <option value="">Select City</option>
                        </select>
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" required
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', $vendor->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $vendor->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vendor->notes ?? '') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    

                </div>

                {{-- ACTIONS --}}
                <div class="mt-4 text-end">
                    <button class="btn btn-primary">{{ isset($vendor) ? 'Update Vendor' : 'Save Vendor' }}</button>
                    <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')

<script>
$(document).ready(function() {
    // Initialize Select2 individually

    $('#country').select2({
        placeholder: "Select Country",
        allowClear: true,
        dropdownParent: $('.form-container')
    });

    $('#state').select2({
        placeholder: "Select State",
        allowClear: true,
        dropdownParent: $('.form-container')
    });

    $('#city').select2({
        placeholder: "Select City",
        allowClear: true,
        dropdownParent: $('.form-container')
    });

    const oldCountry = "{{ old('country', $vendor->country ?? '') }}";
    const defaultCountry = oldCountry ? oldCountry : "India";
    const oldState = "{{ old('state', $vendor->state ?? '') }}";
    const oldCity = "{{ old('city', $vendor->city ?? '') }}";

    let countriesLoaded = false;
    let initialLoadComplete = false;

    // Load Countries
    function loadCountries() {
        $.ajax({
            url: 'https://countriesnow.space/api/v0.1/countries',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    let countrySelect = $('#country');
                    countrySelect.empty().append('<option value="">Select Country</option>');
                    
                    response.data.forEach(function(item) {
                        let selected = (item.country.toLowerCase() === defaultCountry.toLowerCase()) ? 'selected' : '';
                        countrySelect.append(`<option value="${item.country}" ${selected}>${item.country}</option>`);
                    });
                    
                    countriesLoaded = true;
                    countrySelect.trigger('change');
                }
            },
            error: function() {
                // Fallback in case API is down, add India and default option
                let countrySelect = $('#country');
                countrySelect.empty().append('<option value="">Select Country</option>');
                countrySelect.append(`<option value="India" selected>India</option>`);
                countrySelect.trigger('change');
            }
        });
    }

    // Load States when Country changes
    $('#country').on('change', function() {
        let country = $(this).val();
        let stateSelect = $('#state');
        let citySelect = $('#city');

        // If countries are not loaded yet, do nothing
        if (!countriesLoaded) return;

        stateSelect.empty().append('<option value="">Loading...</option>').trigger('change');
        citySelect.empty().append('<option value="">Select City</option>').trigger('change');

        if (!country) {
            stateSelect.empty().append('<option value="">Select State</option>').trigger('change');
            return;
        }

        $.ajax({
            url: 'https://countriesnow.space/api/v0.1/countries/states',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ country: country }),
            success: function(response) {
                stateSelect.empty().append('<option value="">Select State</option>');
                if (!response.error && response.data && response.data.states && response.data.states.length > 0) {
                    response.data.states.forEach(function(state) {
                        let isSelected = false;
                        if (!initialLoadComplete && oldState) {
                            isSelected = (state.name.toLowerCase() === oldState.toLowerCase());
                        }
                        let selectedAttr = isSelected ? 'selected' : '';
                        stateSelect.append(`<option value="${state.name}" ${selectedAttr}>${state.name}</option>`);
                    });
                    stateSelect.trigger('change');
                } else {
                    // No states returned, fallback: load all cities for this country directly
                    stateSelect.empty().append('<option value="N/A">No States (Direct to Cities)</option>').trigger('change');
                    loadCitiesDirectly(country);
                }
            },
            error: function() {
                stateSelect.empty().append('<option value="">Select State</option>').trigger('change');
            }
        });
    });

    // Load Cities when State changes
    $('#state').on('change', function() {
        let country = $('#country').val();
        let state = $(this).val();
        let citySelect = $('#city');

        if (!countriesLoaded) return;
        if (state === 'N/A') return; // Already loading cities directly

        citySelect.empty().append('<option value="">Loading...</option>').trigger('change');

        if (!state || !country) {
            citySelect.empty().append('<option value="">Select City</option>').trigger('change');
            return;
        }

        $.ajax({
            url: 'https://countriesnow.space/api/v0.1/countries/state/cities',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ country: country, state: state }),
            success: function(response) {
                citySelect.empty().append('<option value="">Select City</option>');
                if (!response.error && response.data && response.data.length > 0) {
                    response.data.forEach(function(city) {
                        let isSelected = false;
                        if (!initialLoadComplete && oldCity) {
                            isSelected = (city.toLowerCase() === oldCity.toLowerCase());
                        }
                        let selectedAttr = isSelected ? 'selected' : '';
                        citySelect.append(`<option value="${city}" ${selectedAttr}>${city}</option>`);
                    });
                    citySelect.trigger('change');
                    initialLoadComplete = true; // Mark initial load complete after cities load
                } else {
                    // If no cities for this state, fallback to all cities of the country
                    loadCitiesDirectly(country);
                }
            },
            error: function() {
                // Try fallback to all country cities
                loadCitiesDirectly(country);
            }
        });
    });

    function loadCitiesDirectly(country) {
        let citySelect = $('#city');
        $.ajax({
            url: 'https://countriesnow.space/api/v0.1/countries/cities',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ country: country }),
            success: function(response) {
                citySelect.empty().append('<option value="">Select City</option>');
                if (!response.error && response.data && response.data.length > 0) {
                    response.data.forEach(function(city) {
                        let isSelected = false;
                        if (!initialLoadComplete && oldCity) {
                            isSelected = (city.toLowerCase() === oldCity.toLowerCase());
                        }
                        let selectedAttr = isSelected ? 'selected' : '';
                        citySelect.append(`<option value="${city}" ${selectedAttr}>${city}</option>`);
                    });
                    citySelect.trigger('change');
                    initialLoadComplete = true; // Mark initial load complete
                }
            },
            error: function() {
                citySelect.empty().append('<option value="">Select City</option>').trigger('change');
                initialLoadComplete = true;
            }
        });
    }

    // Trigger Initial Load
    loadCountries();
});
</script>
    
@endpush
