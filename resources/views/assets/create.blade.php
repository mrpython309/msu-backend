@extends('layouts/contentNavbarLayout')

@section('title', 'Create Asset')

@section('page-style')
<style>
    .form-container {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 2rem;
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }
    /* .form-label-required::after {
        content: " *";
        color: #dc3545;
    } */
    .form-section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .input-group > .select2-container {
        flex: 1 1 auto;
        width: 1% !important;
    }
    .input-group > .select2-container .select2-selection--single {
        height: 100%;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    #salvage_value::-webkit-outer-spin-button,
    #salvage_value::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #salvage_value {
        -moz-appearance: textfield;
        appearance: textfield;
    }
</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light">Create Asset</span>
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Asset</h5>
        <a href="{{route('asset.index')}}" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="card-body">
        <div class="form-container">
            <form id="assetMainForm" action="{{ route('asset.store') }}" method="POST" enctype="multipart/form-data"> 
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger" id="assetFormErrors">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ASSET DETAILS --}}
    <div class="form-section-title">Asset Details</div>
    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Asset Description <span class="text-danger">*</span></label>
            <input type="text" name="asset_name" id="asset_name" required
                   class="form-control @error('asset_name') is-invalid @enderror"
                   value="{{ old('asset_name') }}"
                   list="asset-name-options"
                   autocomplete="off">
            <datalist id="asset-name-options"></datalist>
            @error('asset_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

         <div class="col-md-4">
            <label class="form-label">SL No / Any ID's <span class="text-danger">*</span></label>
            <input type="text" name="serial_no" required
                   class="form-control @error('serial_no') is-invalid @enderror"
                   value="{{ old('serial_no') }}">
            @error('serial_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">HSN Code</label>
            <select name="hsn_code" class="form-select select2 @error('hsn_code') is-invalid @enderror">
                <option value="">Select HSN Code</option>
                @foreach ($hsns as $hsn)
                    <option value="{{ $hsn->hsn_code }}" {{ (string) old('hsn_code') === (string) $hsn->hsn_code ? 'selected' : '' }}>
                        {{ $hsn->hsn_code }}
                    </option>
                @endforeach
            </select>
            @error('hsn_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Asset Code <span class="text-danger">*</span></label>
            <input type="text" name="asset_code" id="asset_code" readonly
                   class="form-control @error('asset_code') is-invalid @enderror"
                   value="{{ old('asset_code', $assetCode) }}"
                   placeholder="CompanyCode/BranchCode/0001">
            <small class="text-muted" id="asset_code_hint">Filled when company and branch are selected.</small>
            @error('asset_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
        <label class="form-label">Asset Structure <span class="text-danger">*</span> </label>
            <select name="asset_structure" id="asset_structure" required class="form-select @error('asset_structure') is-invalid @enderror">
                <option value="">Select Structure</option>
                <option value="single" {{ old('asset_structure','single') == 'single' ? 'selected' : '' }}> Single Asset </option>
                <option value="group" {{ old('asset_structure') == 'group' ? 'selected' : '' }}>  Group Asset </option>
            </select>
            @error('asset_structure') <div class="invalid-feedback">{{ $message }}</div>  @enderror
        </div>

        @php
            $createStructure = old('asset_structure', 'single');
            $createIsSingle = $createStructure !== 'group';
            $createQty = $createIsSingle ? 1 : max(2, (int) old('items_count', 2));
        @endphp
        <div class="col-md-4" id="itemsCountWrapper">
            <label class="form-label">Qty <span class="text-danger">*</span></label>
            <input type="number" name="items_count" id="items_count" required
                min="{{ $createIsSingle ? 1 : 2 }}"
                @if($createIsSingle) max="1" readonly @endif
                class="form-control @error('items_count') is-invalid @enderror {{ $createIsSingle ? 'bg-light' : '' }}"
                value="{{ $createQty }}">
            @error('items_count') <div class="invalid-feedback">{{ $message }}</div>  @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">UOM</label>
            <select name="uom" class="form-select select2 @error('uom') is-invalid @enderror">
                <option value="">Select Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->unit_name }}" {{ old('uom') == $unit->unit_name ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                @endforeach
            </select>
            @error('uom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Product Categories <span class="text-danger">*</span></label>
            <select name="product_category_id" id="product_category_id" required class="form-select select2 @error('product_category_id') is-invalid @enderror">
                <option value="">Select Product Category</option>
                @foreach ($productCategories as $category)
                 <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                @endforeach
            </select>
            @error('product_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Product Sub Categories <span class="text-danger">*</span></label>
            <select name="product_sub_category_id" id="product_sub_category_id" required class="form-select select2 @error('product_sub_category_id') is-invalid @enderror">
                <option value="">Select Product Sub Category</option>
            </select>
            @error('product_sub_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Type of Purchase</label>
            <select name="purchase_type" class="form-select @error('purchase_type') is-invalid @enderror">
                <option value="">Select Type of Purchase</option>
                <option value="purchased" {{ old('purchase_type', 'purchased') == 'purchased' ? 'selected' : '' }}>Purchased</option>
                <option value="transferred" {{ old('purchase_type') == 'transferred' ? 'selected' : '' }}>Transferred</option>
            </select>
            @error('purchase_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Asset Type</label>
            <select name="ownership_type" class="form-select @error('ownership_type') is-invalid @enderror">
                <option value="">Select Asset Type</option>
                <option value="owned" {{ old('ownership_type', 'owned') == 'owned' ? 'selected' : '' }}>Owned</option>
                <option value="rent" {{ old('ownership_type') == 'rent' ? 'selected' : '' }}>Rent</option>
            </select>
            @error('ownership_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

       

        <div class="col-md-4">
         <label class="form-label"> Status <span class="text-danger">*</span> </label>
            <select name="status" required
                    class="form-select @error('status') is-invalid @enderror">
                <option value="">Select Status</option>
                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}> Available </option>
                <option value="assigned" {{ old('status') == 'assigned' ? 'selected' : '' }}> Assigned </option>                
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
       </div>

       <div class="col-md-4">
         <label class="form-label"> Operational Status <span class="text-danger">*</span> </label>
            <select name="operational_status" required
                    class="form-select @error('operational_status') is-invalid @enderror">
                <option value="">Select Operational Status</option>
                @foreach ($operations_status as $operationStatus)
                    <option value="{{$operationStatus->value}}" {{ old('operational_status') == $operationStatus->value ? 'selected' : '' }}>{{$operationStatus->label()}}</option>
                @endforeach
            </select>
            @error('operational_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
       </div>

       <div class="col-md-4">
    <label class="form-label">
        Condition <span class="text-danger">*</span>
    </label>

    <select name="condition" required
            class="form-select @error('condition') is-invalid @enderror">
        <option value="">Select Condition</option>

        @foreach ($conditions as $condition)
            <option value="{{ $condition->value }}"
                {{ old('condition') == $condition->value ? 'selected' : '' }}>
                {{ $condition->label() }}
            </option>
        @endforeach
    </select>

    @error('condition')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>




    <div class="col-12">
    <div class="form-section-title mt-2">Location</div>
    <div class="row g-3">

<div class="col-md-3">
            <label class="form-label" id="company_label">Company <span class="text-danger">*</span></label>
            <select id="company_id" name="company_id" required
                    class="form-select select2 @error('company_id') is-invalid @enderror">
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ ($repopulateForm ?? false) && old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

<div class="col-md-3">
            <label class="form-label" id="branch_label">Branch <span class="text-danger">*</span></label>
            <select name="branch_id" id="branch_id" required class="form-select select2 @error('branch_id') is-invalid @enderror">
                <option value="">Select Branch</option>
            </select>
            @error('branch_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label" id="location_label">Physical Location</label>
            <div class="input-group">
                <input type="text"
                       id="location_display"
                       class="form-control @error('location_id') is-invalid @enderror"
                       list="location-options"
                       autocomplete="off"
                       placeholder="Type or pick physical location">
                <button type="button" class="btn btn-outline-primary" id="quickCreateLocationBtn" title="Add physical location">+</button>
            </div>
            <input type="hidden" name="location_id" id="location_id" value="{{ ($repopulateForm ?? false) ? old('location_id') : '' }}">
            <datalist id="location-options"></datalist>
            @error('location_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        @php
            $oldCustodianIdCreate = old('custodian_id');
            $defaultCustodianNameCreate = old('custodian_display');
            if ($defaultCustodianNameCreate === null && $oldCustodianIdCreate !== null && $oldCustodianIdCreate !== '') {
                $defaultCustodianNameCreate = optional(collect($custodians)->first(fn ($c) => (string) $c->id === (string) $oldCustodianIdCreate))->employee_name;
            }
        @endphp
        <div class="col-md-3">
            <label class="form-label">Asset Custodian</label>
            <div class="input-group">
                <input type="text"
                       id="custodian_display"
                       class="form-control @error('custodian_id') is-invalid @enderror"
                       value="{{ $defaultCustodianNameCreate ?? '' }}"
                       list="custodian-options"
                       autocomplete="off"
                       placeholder="Type or pick asset custodian">
                <button type="button" class="btn btn-outline-primary" id="quickCreateCustodianBtn" title="Add asset custodian">+</button>
            </div>
            <input type="hidden" name="custodian_id" id="custodian_id" value="{{ old('custodian_id') }}">
            <datalist id="custodian-options">
                @foreach ($custodians as $custodian)
                    <option value="{{ $custodian->employee_name }}"></option>
                @endforeach
            </datalist>
            @error('custodian_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
            </div>

        <div id="assigned_to_location_row" class="d-none mt-3">
            <div class="row g-3">
            <div class="col-12">
                <small class="text-muted">Assign asset from the location above to the destination below.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">To Company <span class="text-danger">*</span></label>
                <select id="to_company_id" name="to_company_id" class="form-select select2 @error('to_company_id') is-invalid @enderror">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ ($repopulateForm ?? false) && old('to_company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
                @error('to_company_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">To Branch <span class="text-danger">*</span></label>
                <select name="to_branch" id="to_branch" class="form-select select2 @error('to_branch') is-invalid @enderror">
                    <option value="">Select Branch</option>
                </select>
                @error('to_branch')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">To Physical Location</label>
                <div class="input-group">
                    <input type="text"
                           id="to_location_display"
                           class="form-control @error('to_location') is-invalid @enderror"
                           list="to-location-options"
                           autocomplete="off"
                           placeholder="Type or pick physical location">
                    <button type="button" class="btn btn-outline-primary" id="quickCreateToLocationBtn" title="Add physical location">+</button>
                </div>
                <input type="hidden" name="to_location" id="to_location" value="{{ ($repopulateForm ?? false) ? old('to_location') : '' }}">
                <datalist id="to-location-options"></datalist>
                @error('to_location')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            @php
                $oldToCustodianIdCreate = ($repopulateForm ?? false) ? old('to_custodian_id') : null;
                $defaultToCustodianNameCreate = null;
                if ($oldToCustodianIdCreate !== null && $oldToCustodianIdCreate !== '') {
                    $defaultToCustodianNameCreate = optional(collect($custodians)->first(fn ($c) => (string) $c->id === (string) $oldToCustodianIdCreate))->employee_name;
                }
            @endphp
            <div class="col-md-3">
                <label class="form-label">Asset Custodian</label>
                <div class="input-group">
                    <input type="text"
                           id="to_custodian_display"
                           class="form-control @error('to_custodian_id') is-invalid @enderror"
                           value="{{ $defaultToCustodianNameCreate ?? '' }}"
                           list="to-custodian-options"
                           autocomplete="off"
                           placeholder="Type or pick asset custodian">
                    <button type="button" class="btn btn-outline-primary" id="quickCreateToCustodianBtn" title="Add asset custodian">+</button>
                </div>
                <input type="hidden" name="to_custodian_id" id="to_custodian_id" value="{{ $oldToCustodianIdCreate }}">
                <datalist id="to-custodian-options">
                    @foreach ($custodians as $custodian)
                        <option value="{{ $custodian->employee_name }}"></option>
                    @endforeach
                </datalist>
                @error('to_custodian_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            </div>
        </div>
    </div>

    </div>

    {{-- PURCHASE & WARRANTY --}}
    <div class="form-section-title mt-4">Purchase</div>
    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
            <input type="date" name="purchase_date" required
                   class="form-control @error('purchase_date') is-invalid @enderror"
                   value="{{ old('purchase_date') }}">
            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Invoice Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.001" name="purchase_cost" required
                   class="form-control @error('purchase_cost') is-invalid @enderror"
                   value="{{ old('purchase_cost') }}">
            @error('purchase_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @php
            $oldSupIdCreate = old('supplier');
            $defaultSupNameCreate = old('supplier_display');
            if ($defaultSupNameCreate === null && $oldSupIdCreate !== null && $oldSupIdCreate !== '') {
                $defaultSupNameCreate = optional(collect($vendors)->first(fn ($v) => (string)$v->id === (string)$oldSupIdCreate))->name;
            }
        @endphp
        <div class="col-md-4">
            <label class="form-label">Supplier <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text"
                       id="supplier_display"
                       class="form-control @error('supplier') is-invalid @enderror"
                       value="{{ $defaultSupNameCreate ?? '' }}"
                       list="supplier-options"
                       autocomplete="off"
                       placeholder="Type or pick supplier">
                <button type="button" class="btn btn-outline-primary" id="quickCreateSupplierBtn" title="Add supplier">+</button>
            </div>
            <input type="hidden" name="supplier" id="supplier" value="{{ old('supplier') }}">
            <datalist id="supplier-options">
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->name }}"></option>
                @endforeach                
            </datalist>
            @error('supplier')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>  
        
         <div class="col-md-4">
            <label class="form-label">Invoice No</label>
            <input type="text"  name="invoice_details"
                   class="form-control @error('invoice_details') is-invalid @enderror"
                   value="{{ old('invoice_details') }}">
            @error('invoice_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Invoice Date</label>
            <input type="date" name="invoice_date"
                   class="form-control @error('invoice_date') is-invalid @enderror"
                   value="{{ old('invoice_date') }}">
            @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">PO Number</label>
            <input type="text" name="po_number"
                   class="form-control @error('po_number') is-invalid @enderror"
                   value="{{ old('po_number') }}">
            @error('po_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">PO Date</label>
            <input type="date" name="po_date"
                   class="form-control @error('po_date') is-invalid @enderror"
                   value="{{ old('po_date') }}">
            @error('po_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Cost Centre</label>
            <div class="input-group">
                <input type="text"
                       name="cost_centre"
                       id="cost_centre"
                       class="form-control @error('cost_centre') is-invalid @enderror"
                       value="{{ old('cost_centre') }}"
                       list="cost-centre-options"
                       autocomplete="off"
                       placeholder="Type or pick cost centre">
                <button type="button" class="btn btn-outline-primary" id="quickCreateCostCentreBtn" title="Add cost centre">+</button>
            </div>
            <datalist id="cost-centre-options">
                @foreach ($costCentres as $costCentre)
                    <option value="{{ $costCentre->cost_centre_name }}"></option>
                @endforeach
            </datalist>
            @error('cost_centre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select select2 @error('department_id') is-invalid @enderror">
                <option value="">Select Department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                @endforeach
            </select>
            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

    </div>

    {{-- DEPRECIATION --}}
    <div class="form-section-title mt-4">Depreciation</div>
    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Depreciation Method <span class="text-danger">*</span></label>
            <select name="depreciation_method" class="form-select" id="depreciation_method">
                @foreach(['no_depreciation','reducing_balance'] as $method)
                    <option value="{{ $method }}"
                        {{ old('depreciation_method') == $method ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ',$method)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 d-none" id="usefulLifeWrapper">
            <label class="form-label">Useful Life (Years) <span class="text-danger">*</span></label>
            <input type="text" name="userful_life"
                   class="form-control"
                   value="{{ old('userful_life') }}">
        </div>

        <div class="col-md-4 d-none" id="salvageWrapper">
            <label class="form-label" id="salvageLabel">Salvage Value <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" max="100"
                   name="salvage_value"
                   id="salvage_value"
                   class="form-control @error('salvage_value') is-invalid @enderror"
                   value="{{ old('salvage_value') }}">
            <div class="text-danger small mt-1 @error('salvage_value') @else d-none @enderror" id="salvage_value_error">@error('salvage_value'){{ $message }}@enderror</div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Asset Image </label>
            <input class="form-control @error('filename') is-invalid @enderror" type="file" id="filename" name="filename" accept="image/*">              
            @error('filename')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

    </div>

    {{-- ACTIONS --}}
    <div class="mt-4 d-flex flex-wrap gap-2 justify-content-end">
        <button type="submit" name="save_and_duplicate" value="1" class="btn btn-success">
            Save &amp; Duplicate
        </button>
        <button type="submit" class="btn btn-primary">Save Asset</button>
        <a href="{{ route('asset.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>


        </div>
    </div>
</div>

<div class="modal fade" id="quickCustodianModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="quickCustodianForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Asset Custodian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Custodian name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="quick_custodian_name" required>
          <div class="invalid-feedback d-block" id="quick_custodian_name_error"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Department <span class="text-muted">(optional)</span></label>
          <select class="form-select" id="quick_custodian_department">
            <option value="">Select Department</option>
            @foreach ($departments as $department)
              <option value="{{ $department->id }}">{{ $department->department_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Mobile <span class="text-muted">(optional)</span></label>
          <input type="text" class="form-control" id="quick_custodian_mobile" maxlength="20">
        </div>
        <div class="mb-0">
          <label class="form-label">Email <span class="text-muted">(optional)</span></label>
          <input type="email" class="form-control" id="quick_custodian_email">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="quickSupplierModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="quickSupplierForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Supplier name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="quick_supplier_name" required>
          <div class="invalid-feedback d-block" id="quick_supplier_name_error"></div>
        </div>
        <div class="mb-0">
          <label class="form-label">Phone <span class="text-muted">(optional)</span></label>
          <input type="text" class="form-control" id="quick_supplier_phone" maxlength="20">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="quickLocationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="quickLocationForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Physical Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Physical Location name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="quick_location_name" required>
        <div class="invalid-feedback d-block" id="quick_location_name_error"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="quickCostCentreModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="quickCostCentreForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Cost Centre</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Cost Centre name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="quick_cost_centre_name" required>
          <div class="invalid-feedback d-block" id="quick_cost_centre_name_error"></div>
        </div>
        <div class="mb-0">
          <label class="form-label">Description <span class="text-muted">(optional)</span></label>
          <textarea class="form-control" id="quick_cost_centre_description" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('page-script')

<script>

function refreshSearchable($el) {
    if (!$el || !$el.length) {
        return;
    }
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.trigger('change.select2');
    }
}

const $assetNameInput = $('#asset_name');
const $assetNameOptions = $('#asset-name-options');
let currentAssetNameItems = [];

function renderAssetNameOptions(items) {
    currentAssetNameItems = items || [];
    $assetNameOptions.empty();
    currentAssetNameItems.forEach(function(item) {
        const value = typeof item === 'string' ? item : item.name;
        $assetNameOptions.append(`<option value="${$('<div>').text(value).html()}"></option>`);
    });
}

function checkAutoPopulateFromItemMaster() {
    return;
}

function fetchAssetNameSuggestions(term) {
    $.ajax({
        url: "{{ route('asset-names.suggest') }}",
        type: "GET",
        dataType: "json",
        data: { q: term, limit: 50 },
        success: function(res) {
            renderAssetNameOptions((res && res.data) ? res.data : []);
            checkAutoPopulateFromItemMaster();
        }
    });
}

$assetNameInput.on('change select input', function() {
    checkAutoPopulateFromItemMaster();
});


const IS_DUPLICATE_RETURN = @json(!empty($isDuplicateReturn));
const DUPLICATE_SAVE_MESSAGE = @json($duplicateSavedMessage ?? null);
const ASSET_CREATE_DRAFT_KEY = 'empire_asset_create_form_v3';
let saveAssetCreateDraftTimer = null;
let suppressLocationCascade = false;

function saveAssetCreateDraft() {
    try {
        const fields = {};
        $('#assetMainForm').find('input[name], select[name], textarea[name]').each(function () {
            const $el = $(this);
            const name = $el.attr('name');
            if (!name || $el.attr('type') === 'file') return;
            const type = ($el.attr('type') || '').toLowerCase();
            if (type === 'checkbox' || type === 'radio') return;
            fields[name] = $el.val();
        });
        sessionStorage.setItem(ASSET_CREATE_DRAFT_KEY, JSON.stringify({
            v: 3,
            fields: fields,
            supplier_display: ($('#supplier_display').val() || ''),
            custodian_display: ($('#custodian_display').val() || ''),
            to_custodian_display: ($('#to_custodian_display').val() || ''),
        }));
    } catch (e) {}
}

function scheduleSaveAssetCreateDraft() {
    clearTimeout(saveAssetCreateDraftTimer);
    saveAssetCreateDraftTimer = setTimeout(saveAssetCreateDraft, 250);
}

function loadAssetCreateDraft() {
    try {
        const raw = sessionStorage.getItem(ASSET_CREATE_DRAFT_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

function clearAssetCreateDraft() {
    try {
        sessionStorage.removeItem(ASSET_CREATE_DRAFT_KEY);
    } catch (e) {}
}

function isCreatePageReload() {
    try {
        const nav = performance.getEntriesByType('navigation')[0];
        return nav && nav.type === 'reload';
    } catch (e) {
        return false;
    }
}

function restoreCreateFormFromDraft(payload) {
    if (!payload || !payload.fields) {
        return;
    }

    function setNamed(name, value) {
        if (value === undefined || value === null) {
            return;
        }
        const escaped = String(name).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
        $('#assetMainForm').find('[name="' + escaped + '"]').val(value);
    }

    const f = payload.fields;
    const skip = { branch_id: 1, location_id: 1, product_sub_category_id: 1, to_branch: 1, to_location: 1 };
    Object.keys(f).forEach(function (name) {
        if (!skip[name]) {
            setNamed(name, f[name]);
        }
    });
    $('#supplier_display').val(payload.supplier_display || '');
    $('#custodian_display').val(payload.custodian_display || '');
    $('#to_custodian_display').val(payload.to_custodian_display || '');

    const companyId = f.company_id || '';
    const branchId = f.branch_id || '';
    const locationId = f.location_id || '';
    const toCompanyId = f.to_company_id || '';
    const toBranchId = f.to_branch || '';
    const toLocationId = f.to_location || '';
    const categoryId = f.product_category_id || '';
    const subCatId = f.product_sub_category_id || '';

    let pending = 0;
    if (categoryId) pending++;
    if (companyId) pending++;
    if (toCompanyId && isAssignedStatus()) pending++;

    function finishDraftRestore() {
        syncLocationLabels();
        rebuildSupplierDatalist();
        syncSupplierHidden();
        rebuildCustodianDatalist();
        syncCustodianHidden('from');
        syncCustodianHidden('to');
        toggleAssetStructure();
        toggleDepreciationFields();
        fetchAssetNameSuggestions($('#asset_name').val() || '');
    }

    if (pending === 0) {
        finishDraftRestore();
        return;
    }

    if (categoryId) {
        loadProductSubCategories(categoryId, subCatId || null, function () {
            pending--;
            if (pending === 0) finishDraftRestore();
        });
    }

    if (companyId) {
        loadBranches(companyId, branchId || null, locationId || null, function () {
            pending--;
            if (pending === 0) finishDraftRestore();
        });
    }

    if (toCompanyId && isAssignedStatus()) {
        loadToBranches(toCompanyId, toBranchId || null, toLocationId || null, function () {
            pending--;
            if (pending === 0) finishDraftRestore();
        });
    }
}

let SUPPLIERS_DATA = @json($vendors->map(fn ($v) => ['id' => (int)$v->id, 'name' => (string)$v->name])->values());

function toastSuccessAssetForm(text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1800, showConfirmButton: false });
    } else {
        alert(text);
    }
}

function rebuildSupplierDatalist() {
    const seen = {};
    const $opts = $('#supplier-options');
    $opts.empty();
    SUPPLIERS_DATA.forEach(function (v) {
        const kk = v.name.trim().toLowerCase();
        if (seen[kk]) {
            return;
        }
        seen[kk] = true;
        $opts.append($('<option></option>').attr('value', v.name));
    });
}

function registerSupplier(row) {
    if (!row || !row.id) {
        return false;
    }
    const id = parseInt(row.id, 10);
    const name = (row.name || '').trim();
    if (!name) {
        return false;
    }
    const ix = SUPPLIERS_DATA.findIndex(function (v) {
        return String(v.id) === String(id);
    });
    if (ix >= 0) {
        SUPPLIERS_DATA[ix].name = name;
    } else {
        SUPPLIERS_DATA.push({ id: id, name: name });
    }
    rebuildSupplierDatalist();
    $('#supplier').val(id);
    $('#supplier_display').val(name);
    saveAssetCreateDraft();
    return true;
}

function syncSupplierHidden() {
    const raw = ($('#supplier_display').val() || '').trim();
    $('#supplier').val('');
    if (!raw) {
        return false;
    }
    const k = raw.toLowerCase();
    const hits = SUPPLIERS_DATA.filter(function (v) {
        return v.name.trim().toLowerCase() === k;
    });
    if (hits.length !== 1) {
        return false;
    }
    $('#supplier').val(hits[0].id);
    return true;
}

function postVendorQuick(name, phone, done) {
    $.ajax({
        url: "{{ route('vendor.quick-store') }}",
        type: 'POST',
        dataType: 'json',
        data: {
            _token: "{{ csrf_token() }}",
            name: name,
            phone_number: phone || ''
        },
        success: function (res) {
            if (res && res.data) {
                registerSupplier(res.data);
            }
            toastSuccessAssetForm((res && res.message) ? res.message : 'Supplier saved.');
            if (quickSupplierModal) {
                quickSupplierModal.hide();
            }
            done && done(null, res);
        },
        error: function (xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                || (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name && xhr.responseJSON.errors.name[0])
                || 'Unable to save supplier.';
            done && done(msg, xhr);
        }
    });
}

$assetNameInput.on('focus input', function() {
    fetchAssetNameSuggestions($(this).val() || '');
});

var quickSupplierModal = null;
if (typeof bootstrap !== 'undefined' && document.getElementById('quickSupplierModal')) {
    quickSupplierModal = new bootstrap.Modal(document.getElementById('quickSupplierModal'));
}

function showQuickSupplierModalFn() {
    if (quickSupplierModal) {
        quickSupplierModal.show();
    } else {
        $('#quickSupplierModal').show();
    }
}

$('#supplier_display').on('input', function () {
    $('#supplier').val('');
});

$('#supplier_display').on('change blur', function () {
    syncSupplierHidden();
});

$('#quickCreateSupplierBtn').on('click', function() {
    $('#quick_supplier_name_error').text('');
    const typed = ($('#supplier_display').val() || '').trim();

    if (typed !== '') {
        postVendorQuick(typed, '', function (err) {
            if (err) {
                $('#quick_supplier_name').val(typed);
                $('#quick_supplier_phone').val('');
                $('#quick_supplier_name_error').text(typeof err === 'string' ? err : 'Unable to save supplier.');
                showQuickSupplierModalFn();
            }
        });
        return;
    }

    $('#quick_supplier_name').val('');
    $('#quick_supplier_phone').val('');
    showQuickSupplierModalFn();
});

$('#quickSupplierForm').on('submit', function(e) {
    e.preventDefault();
    const name = ($('#quick_supplier_name').val() || '').trim();
    $('#quick_supplier_name_error').text('');
    if (!name) {
        $('#quick_supplier_name_error').text('Supplier name is required.');
        return;
    }

    const phone = ($('#quick_supplier_phone').val() || '').trim();
    postVendorQuick(name, phone, function (err) {
        if (err) {
            $('#quick_supplier_name_error').text(typeof err === 'string' ? err : 'Unable to save supplier.');
        }
    });
});

let CUSTODIANS_DATA = @json($custodians->map(fn ($c) => ['id' => (int) $c->id, 'name' => (string) $c->employee_name])->values());
let quickCustodianTarget = 'from';

function custodianUi(kind) {
    if (kind === 'to') {
        return {
            display: '#to_custodian_display',
            hidden: '#to_custodian_id',
            list: '#to-custodian-options'
        };
    }
    return {
        display: '#custodian_display',
        hidden: '#custodian_id',
        list: '#custodian-options'
    };
}

function rebuildCustodianDatalist() {
    ['from', 'to'].forEach(function (kind) {
        const ui = custodianUi(kind);
        const $opts = $(ui.list);
        if (!$opts.length) {
            return;
        }
        const seen = {};
        $opts.empty();
        CUSTODIANS_DATA.forEach(function (v) {
            const kk = (v.name || '').trim().toLowerCase();
            if (!kk || seen[kk]) {
                return;
            }
            seen[kk] = true;
            $opts.append($('<option></option>').attr('value', v.name));
        });
    });
}

function registerCustodian(row, kind) {
    if (!row || !row.id) {
        return false;
    }
    const id = parseInt(row.id, 10);
    const name = (row.name || '').trim();
    if (!name) {
        return false;
    }
    const ix = CUSTODIANS_DATA.findIndex(function (v) {
        return String(v.id) === String(id);
    });
    if (ix >= 0) {
        CUSTODIANS_DATA[ix].name = name;
    } else {
        CUSTODIANS_DATA.push({ id: id, name: name });
    }
    rebuildCustodianDatalist();
    const ui = custodianUi(kind || quickCustodianTarget);
    $(ui.hidden).val(id);
    $(ui.display).val(name);
    saveAssetCreateDraft();
    return true;
}

function syncCustodianHidden(kind) {
    const ui = custodianUi(kind);
    if (!$(ui.display).length) {
        return false;
    }
    const raw = ($(ui.display).val() || '').trim();
    $(ui.hidden).val('');
    if (!raw) {
        return false;
    }
    const k = raw.toLowerCase();
    const hits = CUSTODIANS_DATA.filter(function (v) {
        return (v.name || '').trim().toLowerCase() === k;
    });
    if (hits.length !== 1) {
        return false;
    }
    $(ui.hidden).val(hits[0].id);
    return true;
}

function custodianAjaxError(xhr) {
    const errors = xhr.responseJSON && xhr.responseJSON.errors;
    if (errors) {
        const first = errors.employee_name || errors.mobile_number || errors.email || errors.department_id;
        if (first && first[0]) {
            return first[0];
        }
    }
    return (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to save asset custodian.';
}

function postCustodianQuick(payload, kind, done) {
    $.ajax({
        url: "{{ route('asset-custodians.quick-store') }}",
        type: 'POST',
        dataType: 'json',
        data: {
            _token: "{{ csrf_token() }}",
            employee_name: payload.employee_name,
            department_id: payload.department_id || '',
            mobile_number: payload.mobile_number || '',
            email: payload.email || ''
        },
        success: function (res) {
            if (res && res.data) {
                registerCustodian(res.data, kind);
            }
            toastSuccessAssetForm((res && res.message) ? res.message : 'Asset custodian saved.');
            if (quickCustodianModal) {
                quickCustodianModal.hide();
            }
            done && done(null, res);
        },
        error: function (xhr) {
            done && done(custodianAjaxError(xhr), xhr);
        }
    });
}

var quickCustodianModal = null;
if (typeof bootstrap !== 'undefined' && document.getElementById('quickCustodianModal')) {
    quickCustodianModal = new bootstrap.Modal(document.getElementById('quickCustodianModal'));
}

function showQuickCustodianModalFn() {
    if (quickCustodianModal) {
        quickCustodianModal.show();
    } else {
        $('#quickCustodianModal').show();
    }
}

function resetQuickCustodianForm(name) {
    $('#quick_custodian_name_error').text('');
    $('#quick_custodian_name').val(name || '');
    $('#quick_custodian_department').val('');
    $('#quick_custodian_mobile').val('');
    $('#quick_custodian_email').val('');
}

function openOrCreateCustodian(kind) {
    quickCustodianTarget = kind;
    $('#quick_custodian_name_error').text('');
    const typed = ($(custodianUi(kind).display).val() || '').trim();

    if (typed !== '') {
        postCustodianQuick({ employee_name: typed }, kind, function (err) {
            if (err) {
                resetQuickCustodianForm(typed);
                $('#quick_custodian_name_error').text(typeof err === 'string' ? err : 'Unable to save asset custodian.');
                showQuickCustodianModalFn();
            }
        });
        return;
    }

    resetQuickCustodianForm('');
    showQuickCustodianModalFn();
}

$('#custodian_display').on('input', function () {
    $('#custodian_id').val('');
});
$('#custodian_display').on('change blur', function () {
    syncCustodianHidden('from');
});
$('#to_custodian_display').on('input', function () {
    $('#to_custodian_id').val('');
});
$('#to_custodian_display').on('change blur', function () {
    syncCustodianHidden('to');
});

$('#quickCreateCustodianBtn').on('click', function () {
    openOrCreateCustodian('from');
});
$('#quickCreateToCustodianBtn').on('click', function () {
    openOrCreateCustodian('to');
});

$('#quickCustodianForm').on('submit', function (e) {
    e.preventDefault();
    const name = ($('#quick_custodian_name').val() || '').trim();
    $('#quick_custodian_name_error').text('');
    if (!name) {
        $('#quick_custodian_name_error').text('Custodian name is required.');
        return;
    }

    postCustodianQuick({
        employee_name: name,
        department_id: $('#quick_custodian_department').val() || '',
        mobile_number: ($('#quick_custodian_mobile').val() || '').trim(),
        email: ($('#quick_custodian_email').val() || '').trim()
    }, quickCustodianTarget, function (err) {
        if (err) {
            $('#quick_custodian_name_error').text(typeof err === 'string' ? err : 'Unable to save asset custodian.');
        }
    });
});

function ajaxErrorMessage(xhr, fallback) {
    return (xhr.responseJSON && xhr.responseJSON.message)
        || (xhr.responseJSON && xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0] && Object.values(xhr.responseJSON.errors)[0][0])
        || fallback;
}

let FROM_LOCATIONS = [];
let TO_LOCATIONS = [];
let quickLocationKind = 'from';

function locationUi(kind) {
    if (kind === 'to') {
        return {
            branch: '#to_branch',
            display: '#to_location_display',
            hidden: '#to_location',
            list: '#to-location-options',
            branchLabel: 'to branch'
        };
    }
    return {
        branch: '#branch_id',
        display: '#location_display',
        hidden: '#location_id',
        list: '#location-options',
        branchLabel: 'branch'
    };
}

function locationItems(kind) {
    return kind === 'to' ? TO_LOCATIONS : FROM_LOCATIONS;
}

function setLocationItems(kind, rows) {
    if (kind === 'to') {
        TO_LOCATIONS = rows;
    } else {
        FROM_LOCATIONS = rows;
    }
}

function rebuildLocationDatalist(kind) {
    const ui = locationUi(kind);
    const seen = {};
    const $opts = $(ui.list);
    $opts.empty();
    locationItems(kind).forEach(function (row) {
        const key = (row.name || '').trim().toLowerCase();
        if (!key || seen[key]) {
            return;
        }
        seen[key] = true;
        $opts.append($('<option></option>').attr('value', row.name));
    });
}

function registerLocation(kind, row) {
    if (!row || !row.id) {
        return false;
    }
    const id = parseInt(row.id, 10);
    const name = (row.name || row.location_name || '').trim();
    if (!name) {
        return false;
    }
    const items = locationItems(kind).slice();
    const ix = items.findIndex(function (item) {
        return String(item.id) === String(id);
    });
    if (ix >= 0) {
        items[ix].name = name;
    } else {
        items.push({ id: id, name: name });
    }
    setLocationItems(kind, items);
    rebuildLocationDatalist(kind);
    const ui = locationUi(kind);
    $(ui.hidden).val(id);
    $(ui.display).val(name);
    return true;
}

function syncLocationHidden(kind) {
    const ui = locationUi(kind);
    const raw = ($(ui.display).val() || '').trim();
    $(ui.hidden).val('');
    if (!raw) {
        return false;
    }
    const key = raw.toLowerCase();
    const hits = locationItems(kind).filter(function (item) {
        return (item.name || '').trim().toLowerCase() === key;
    });
    if (hits.length !== 1) {
        return false;
    }
    $(ui.hidden).val(hits[0].id);
    return true;
}

function clearLocationPicker(kind) {
    setLocationItems(kind, []);
    rebuildLocationDatalist(kind);
    const ui = locationUi(kind);
    $(ui.display).val('');
    $(ui.hidden).val('');
}

function applyLocationRows(kind, rows, selectedId) {
    const mapped = (rows || []).map(function (item) {
        return { id: item.id, name: item.location_name || item.name || '' };
    }).filter(function (item) {
        return item.id && item.name;
    });
    setLocationItems(kind, mapped);
    rebuildLocationDatalist(kind);
    const ui = locationUi(kind);
    if (selectedId) {
        const hit = mapped.find(function (item) {
            return String(item.id) === String(selectedId);
        });
        if (hit) {
            $(ui.hidden).val(hit.id);
            $(ui.display).val(hit.name);
            return;
        }
    }
    syncLocationHidden(kind);
}

function postLocationQuick(name, branchId, kind, done) {
    $.ajax({
        url: "{{ route('location.quick-store') }}",
        type: 'POST',
        dataType: 'json',
        data: {
            _token: "{{ csrf_token() }}",
            location_name: name,
            branch_id: branchId
        },
        success: function (res) {
            const row = res && res.data ? res.data : null;
            if (row && row.id) {
                registerLocation(kind, row);
            }
            toastSuccessAssetForm((res && res.message) ? res.message : 'Physical Location saved.');
            if (quickLocationModal) {
                quickLocationModal.hide();
            }
            if (typeof saveAssetCreateDraft === 'function') {
                saveAssetCreateDraft();
            }
            done && done(null, res);
        },
        error: function (xhr) {
            done && done(ajaxErrorMessage(xhr, 'Unable to save physical location.'), xhr);
        }
    });
}

function beginQuickLocation(kind) {
    const ui = locationUi(kind);
    const branchId = $(ui.branch).val();
    if (!branchId) {
        alert('Select a ' + ui.branchLabel + ' before adding a physical location.');
        $(ui.branch).trigger('focus');
        return;
    }
    quickLocationKind = kind;
    $('#quick_location_name_error').text('');
    const typed = ($(ui.display).val() || '').trim();
    if (typed !== '') {
        postLocationQuick(typed, branchId, kind, function (err) {
            if (err) {
                $('#quick_location_name').val(typed);
                $('#quick_location_name_error').text(typeof err === 'string' ? err : 'Unable to save physical location.');
                showBootstrapModal(quickLocationModal);
            }
        });
        return;
    }
    $('#quick_location_name').val('');
    showBootstrapModal(quickLocationModal);
}

function showBootstrapModal(modal) {
    if (modal) {
        modal.show();
    }
}

var quickLocationModal = null;
var quickCostCentreModal = null;

if (typeof bootstrap !== 'undefined') {
    if (document.getElementById('quickLocationModal')) {
        quickLocationModal = new bootstrap.Modal(document.getElementById('quickLocationModal'));
    }
    if (document.getElementById('quickCostCentreModal')) {
        quickCostCentreModal = new bootstrap.Modal(document.getElementById('quickCostCentreModal'));
    }
}

$('#location_display').on('input', function () {
    $('#location_id').val('');
});
$('#location_display').on('change blur', function () {
    syncLocationHidden('from');
});
$('#to_location_display').on('input', function () {
    $('#to_location').val('');
});
$('#to_location_display').on('change blur', function () {
    syncLocationHidden('to');
});

$('#quickCreateLocationBtn').on('click', function () {
    beginQuickLocation('from');
});
$('#quickCreateToLocationBtn').on('click', function () {
    beginQuickLocation('to');
});

$('#quickLocationForm').on('submit', function (e) {
    e.preventDefault();
    const name = ($('#quick_location_name').val() || '').trim();
    $('#quick_location_name_error').text('');
    if (!name) {
        $('#quick_location_name_error').text('Physical Location name is required.');
        return;
    }
    const ui = locationUi(quickLocationKind);
    const branchId = $(ui.branch).val();
    if (!branchId) {
        $('#quick_location_name_error').text('Select a ' + ui.branchLabel + ' first.');
        return;
    }
    postLocationQuick(name, branchId, quickLocationKind, function (err) {
        if (err) {
            $('#quick_location_name_error').text(typeof err === 'string' ? err : 'Unable to save physical location.');
        }
    });
});

let COST_CENTRES = @json($costCentres->pluck('cost_centre_name')->filter()->values());

function rebuildCostCentreDatalist() {
    const seen = {};
    const $opts = $('#cost-centre-options');
    $opts.empty();
    COST_CENTRES.forEach(function (name) {
        const label = (name || '').trim();
        const key = label.toLowerCase();
        if (!key || seen[key]) {
            return;
        }
        seen[key] = true;
        $opts.append($('<option></option>').attr('value', label));
    });
}

function matchingCostCentre(raw) {
    const key = (raw || '').trim().toLowerCase();
    if (!key) {
        return '';
    }
    const hit = COST_CENTRES.find(function (name) {
        return (name || '').trim().toLowerCase() === key;
    });
    return hit ? hit.trim() : '';
}

function registerCostCentre(name) {
    const label = (name || '').trim();
    if (!label) {
        return false;
    }
    if (!matchingCostCentre(label)) {
        COST_CENTRES.push(label);
    }
    rebuildCostCentreDatalist();
    $('#cost_centre').val(matchingCostCentre(label) || label);
    if (typeof saveAssetCreateDraft === 'function') {
        saveAssetCreateDraft();
    }
    return true;
}

function postCostCentreQuick(name, description, done) {
    $.ajax({
        url: "{{ route('cost-centres.quick-store') }}",
        type: 'POST',
        dataType: 'json',
        data: {
            _token: "{{ csrf_token() }}",
            cost_centre_name: name,
            cost_centre_description: description || ''
        },
        success: function (res) {
            const row = res && res.data ? res.data : null;
            const label = (row && row.cost_centre_name) ? row.cost_centre_name : name;
            registerCostCentre(label);
            toastSuccessAssetForm((res && res.message) ? res.message : 'Cost Centre saved.');
            if (quickCostCentreModal) {
                quickCostCentreModal.hide();
            }
            done && done(null, res);
        },
        error: function (xhr) {
            done && done(ajaxErrorMessage(xhr, 'Unable to save cost centre.'), xhr);
        }
    });
}

$('#cost_centre').on('change blur', function () {
    const match = matchingCostCentre($(this).val());
    if (match) {
        $(this).val(match);
    }
});

$('#quickCreateCostCentreBtn').on('click', function () {
    $('#quick_cost_centre_name_error').text('');
    const typed = ($('#cost_centre').val() || '').trim();
    if (typed !== '') {
        postCostCentreQuick(typed, '', function (err) {
            if (err) {
                $('#quick_cost_centre_name').val(typed);
                $('#quick_cost_centre_description').val('');
                $('#quick_cost_centre_name_error').text(typeof err === 'string' ? err : 'Unable to save cost centre.');
                showBootstrapModal(quickCostCentreModal);
            }
        });
        return;
    }
    $('#quick_cost_centre_name').val('');
    $('#quick_cost_centre_description').val('');
    showBootstrapModal(quickCostCentreModal);
});

$('#quickCostCentreForm').on('submit', function (e) {
    e.preventDefault();
    const name = ($('#quick_cost_centre_name').val() || '').trim();
    $('#quick_cost_centre_name_error').text('');
    if (!name) {
        $('#quick_cost_centre_name_error').text('Cost Centre name is required.');
        return;
    }
    const description = ($('#quick_cost_centre_description').val() || '').trim();
    postCostCentreQuick(name, description, function (err) {
        if (err) {
            $('#quick_cost_centre_name_error').text(typeof err === 'string' ? err : 'Unable to save cost centre.');
        }
    });
});

    $('.select2').each(function () {
        const $select = $(this);
        if ($select.hasClass('select2-hidden-accessible')) {
            return;
        }
        $select.select2({
            placeholder: $select.find('option[value=""]').first().text() || 'Select',
            allowClear: $select.find('option[value=""]').length > 0,
            closeOnSelect: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $(document.body)
        });
    });


    const REPOPULATE_FORM = @json($repopulateForm ?? false);
    const OLD_CATEGORY = @json(($repopulateForm ?? false) ? old('product_category_id') : '');
    const OLD_SUBCATEGORY = @json(($repopulateForm ?? false) ? old('product_sub_category_id') : '');
    const OLD_BRANCH = @json(($repopulateForm ?? false) ? old('branch_id') : '');
    const OLD_LOCATION = @json(($repopulateForm ?? false) ? old('location_id') : '');
    const OLD_TO_BRANCH = @json(($repopulateForm ?? false) ? old('to_branch') : '');
    const OLD_TO_LOCATION = @json(($repopulateForm ?? false) ? old('to_location') : '');
    const OLD_COMPANY = @json(($repopulateForm ?? false) ? old('company_id') : '');
    const OLD_TO_COMPANY = @json(($repopulateForm ?? false) ? old('to_company_id') : '');
    const OLD_STATUS = @json(($repopulateForm ?? false) ? old('status') : '');
    const WAREHOUSE_COMPANY_ID = {{ (int) ($warehouseCompanyId ?? 0) }};
    const WAREHOUSE_BRANCH_ID = {{ (int) ($warehouseBranchId ?? 0) }};
    const WAREHOUSE_LOCATION_ID = "{{ $warehouseLocationId ?? '' }}";

    function isAssignedStatus() {
        return ($('select[name="status"]').val() || '') === 'assigned';
    }

    function syncLocationLabels() {
        const assigned = isAssignedStatus();
        $('#company_label').html(assigned ? 'From Company <span class="text-danger">*</span>' : 'Company <span class="text-danger">*</span>');
        $('#branch_label').html(assigned ? 'From Branch <span class="text-danger">*</span>' : 'Branch <span class="text-danger">*</span>');
        $('#location_label').text(assigned ? 'From Physical Location' : 'Physical Location');
        $('#assigned_to_location_row').toggleClass('d-none', !assigned);
        $('#to_company_id').prop('required', assigned);
        $('#to_branch').prop('required', assigned);
    }

    function loadToBranches(companyId, selectedBranch = null, selectedLocation = null, onComplete = null) {
        const $branch = $('#to_branch');
        $branch.html('<option value="">Loading...</option>');

        if (!companyId) {
            $branch.html('<option value="">Select Branch</option>');
            refreshSearchable($branch);
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        $.get(
            "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
            function (res) {
                $branch.empty().append('<option value="">Select Branch</option>');
                if (res.data && res.data.length > 0) {
                    $.each(res.data, function (_, value) {
                        const sel = (selectedBranch != null && String(selectedBranch) === String(value.id)) ? 'selected' : '';
                        $branch.append(`<option value="${value.id}" ${sel}>${value.name}</option>`);
                    });
                }
                refreshSearchable($branch);
                if (selectedBranch) {
                    loadToLocations(String(selectedBranch), selectedLocation, onComplete);
                } else if (typeof onComplete === 'function') {
                    onComplete();
                }
            }
        ).fail(function () {
            $branch.html('<option value="">Failed to load branches</option>');
            refreshSearchable($branch);
            if (typeof onComplete === 'function') {
                onComplete();
            }
        });
    }

    function loadToLocations(branchId, selectedLocation = null, onComplete = null) {
        if (!branchId) {
            clearLocationPicker('to');
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        $.get(
            "{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId),
            function (res) {
                applyLocationRows('to', (res && res.data) ? res.data : [], selectedLocation);
                if (typeof onComplete === 'function') {
                    onComplete();
                }
            }
        ).fail(function () {
            clearLocationPicker('to');
            if (typeof onComplete === 'function') {
                onComplete();
            }
        });
    }

    function clearFromLocationFields() {
        $('#company_id').val('');
        $('#branch_id').html('<option value="">Select Branch</option>');
        clearLocationPicker('from');
        refreshSearchable($('#company_id'));
        refreshSearchable($('#branch_id'));
    }

    function clearToLocationFields() {
        $('#to_company_id').val('');
        $('#to_branch').html('<option value="">Select Branch</option>');
        clearLocationPicker('to');
        refreshSearchable($('#to_company_id'));
        refreshSearchable($('#to_branch'));
    }

    function setCompanyIdSilently(companyId) {
        suppressLocationCascade = true;
        $('#company_id').val(companyId ? String(companyId) : '');
        refreshSearchable($('#company_id'));
        suppressLocationCascade = false;
    }

    function prefillWarehouseFromLocation(onComplete) {
        const defaultCompanyId = 1;
        const defaultBranchId = 1;
        setCompanyIdSilently(String(defaultCompanyId));
        loadBranches(defaultCompanyId, defaultBranchId, null, onComplete);
    }

            function loadBranches(companyId, selectedId = null, selectedLocationId = null, onComplete = null) {
            const branchDropdown = $('#branch_id');
            branchDropdown.html('<option value="">Loading...</option>');

            if (!companyId) {
                branchDropdown.html('<option value="">Select Branch</option>');
                refreshSearchable(branchDropdown);
                if (typeof onComplete === 'function') {
                    onComplete();
                }
                return;
            }

            $.get(
                "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
                function (res) {
                    branchDropdown.empty().append('<option value="">Select Branch</option>');
                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            const sel = (selectedId != null && String(selectedId) === String(value.id)) ? 'selected' : '';
                            branchDropdown.append(
                                `<option value="${value.id}" ${sel}>${value.name}</option>`
                            );
                        });
                    } else {
                        branchDropdown.append('<option value="">No branches found</option>');
                    }
                    refreshSearchable(branchDropdown);
                    if (selectedId) {
                        loadLocations(String(selectedId), selectedLocationId, onComplete);
                        refreshAssetCode();
                    } else if (typeof onComplete === 'function') {
                        onComplete();
                    }
                }
            ).fail(function () {
                branchDropdown.html('<option value="">Failed to load branches</option>');
                refreshSearchable(branchDropdown);
                if (typeof onComplete === 'function') {
                    onComplete();
                }
            });
        }

        $('#company_id').on('change', function () {
            if (suppressLocationCascade) {
                return;
            }
            $('#asset_code').val('');
            $('#asset_code_hint').text('Filled when company and branch are selected.');
            clearLocationPicker('from');
            loadBranches(this.value, null, null);
        });

        $('#to_company_id').on('change', function () {
            if (suppressLocationCascade) {
                return;
            }
            clearLocationPicker('to');
            loadToBranches(this.value, null, null);
        });

        $('#to_branch').on('change', function () {
            clearLocationPicker('to');
            loadToLocations(this.value, null);
        });

        $('select[name="status"]').on('change', function () {
            const wasAssigned = $('#assigned_to_location_row').hasClass('d-none') === false;
            syncLocationLabels();
            if (isAssignedStatus()) {
                if (!wasAssigned) {
                    if (REPOPULATE_FORM && OLD_COMPANY) {
                        setCompanyIdSilently(OLD_COMPANY);
                        loadBranches(OLD_COMPANY, OLD_BRANCH || null, OLD_LOCATION || null);
                    } else if (!REPOPULATE_FORM) {
                        prefillWarehouseFromLocation();
                    }
                }
                const toCompanyId = $('#to_company_id').val() || (REPOPULATE_FORM ? OLD_TO_COMPANY : '');
                if (toCompanyId) {
                    if (!$('#to_company_id').val()) {
                        suppressLocationCascade = true;
                        $('#to_company_id').val(String(toCompanyId));
                        refreshSearchable($('#to_company_id'));
                        suppressLocationCascade = false;
                    }
                    const toBranch = REPOPULATE_FORM ? (OLD_TO_BRANCH || null) : null;
                    const toLocation = REPOPULATE_FORM ? (OLD_TO_LOCATION || null) : null;
                    loadToBranches(toCompanyId, toBranch, toLocation);
                }
            } else {
                if (wasAssigned && !REPOPULATE_FORM) {
                    clearFromLocationFields();
                }
                clearToLocationFields();
            }
        });



    function loadProductSubCategories(categoryId, selectedId = null, onComplete = null) {
        const subCategoryDropDown = $('#product_sub_category_id');
        subCategoryDropDown.html('<option>Loading...</option>');

        if (!categoryId) {
            subCategoryDropDown.html('<option value="">Select Product Sub Category</option>');
            refreshSearchable(subCategoryDropDown);
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        $.get("{{ route('ajax.getproductsubcategory.bycategory', ':id') }}".replace(':id', categoryId), function (res) {
            subCategoryDropDown.empty().append('<option value="">Select Product Sub Category</option>');

            $.each(res.data || [], function (_, value) {
                let selected = String(selectedId) === String(value.id) ? 'selected' : '';
                subCategoryDropDown.append(
                    `<option value="${value.id}" ${selected}>${value.sub_category_name}</option>`
                );
            });

            refreshSearchable(subCategoryDropDown);
            if (typeof onComplete === 'function') {
                onComplete();
            }
        }).fail(function () {
            subCategoryDropDown.html('<option value="">Select Product Sub Category</option>');
            refreshSearchable(subCategoryDropDown);
            if (typeof onComplete === 'function') {
                onComplete();
            }
        });
    }

    $('#product_category_id').on('change', function () {
        loadProductSubCategories(this.value);
    });


        function loadLocations(branchId, selectedId = null, onComplete = null) {
        if (!branchId) {
            clearLocationPicker('from');
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        $.get("{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId), function (res) {
            applyLocationRows('from', (res && res.data) ? res.data : [], selectedId);
            if (typeof onComplete === 'function') {
                onComplete();
            }
        }).fail(function () {
            clearLocationPicker('from');
            if (typeof onComplete === 'function') {
                onComplete();
            }
        });
        }

        function refreshAssetCode() {
            const companyId = $('#company_id').val();
            const branchId = $('#branch_id').val();
            const $code = $('#asset_code');
            const $hint = $('#asset_code_hint');

            if (!companyId || !branchId) {
                $code.val('');
                $hint.text('Filled when company and branch are selected.');
                return;
            }

            $hint.text('Generating asset code...');
            $.get("{{ route('asset.preview-code') }}", {
                company_id: companyId,
                branch_id: branchId
            }).done(function (res) {
                if (res && res.asset_code) {
                    $code.val(res.asset_code);
                    $hint.text('Format: Company code / Branch code / 0001');
                }
            }).fail(function (xhr) {
                $code.val('');
                const message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.errors?.company_id?.[0] || xhr.responseJSON.errors?.branch_id?.[0]))
                    || 'Company and branch need a code before an asset code can be generated.';
                $hint.text(message);
            });
        }

        $('#branch_id').on('change', function () {
            clearLocationPicker('from');
            loadLocations($(this).val(), null);
            refreshAssetCode();
        });

    function toggleAssetStructure() {
        const $qty = $('#items_count');
        const isGroup = $('#asset_structure').val() === 'group';
        $qty.prop('required', true);
        if (isGroup) {
            $qty.prop('readonly', false).removeClass('bg-light').attr('min', 2).removeAttr('max');
            const current = parseInt($qty.val(), 10);
            if (!current || current < 2) {
                $qty.val(2);
            }
        } else {
            $qty.val(1).prop('readonly', true).addClass('bg-light').attr('min', 1).attr('max', 1);
        }
    }

    $('#asset_structure').on('change', toggleAssetStructure);
    $('#items_count').on('input', function () {
        if ($('#asset_structure').val() !== 'group') {
            $(this).val(1);
        }
    });

    function clearDepreciationRateError() {
        $('#salvage_value').removeClass('is-invalid');
        $('#salvage_value_error').addClass('d-none').text('');
    }

    function depreciationRateError() {
        if ($('#depreciation_method').val() !== 'reducing_balance') {
            clearDepreciationRateError();
            return '';
        }

        const raw = ($('#salvage_value').val() ?? '').toString().trim();
        if (raw === '') {
            return 'Rate of Depreciation (%) is required.';
        }

        const rate = Number(raw);
        if (!Number.isFinite(rate) || rate < 0 || rate > 100) {
            return 'Rate of Depreciation (%) must be between 0 and 100.';
        }

        clearDepreciationRateError();
        return '';
    }

    function showDepreciationRateError(message) {
        $('#salvage_value').addClass('is-invalid');
        $('#salvage_value_error').removeClass('d-none').text(message);
        const field = document.getElementById('salvage_value');
        if (field) {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            field.focus();
        }
    }

    function toggleDepreciationFields(){

        let method = $('#depreciation_method').val();

        // Reset everything
        $('#usefulLifeWrapper, #salvageWrapper').addClass('d-none');
        $('#useful_life').prop('required', false);

        if(method === 'straight_line'){
            $('#usefulLifeWrapper').removeClass('d-none');
            $('#salvageWrapper').removeClass('d-none');

            $('#salvageLabel').html('Salvage Value <span class="text-danger">*</span>');

            $('#useful_life').prop('required', true);
        }
        else if(method === 'reducing_balance'){
            $('#usefulLifeWrapper').addClass('d-none');
            $('#salvageWrapper').removeClass('d-none');

            $('#salvageLabel').html('Rate of Depreciation (%) <span class="text-danger">*</span>');
        } else {
            clearDepreciationRateError();
        }

    }

    $('#depreciation_method').on('change', toggleDepreciationFields);
    $('#salvage_value').on('input', function () {
        const message = depreciationRateError();
        if (message && !$('#salvage_value_error').hasClass('d-none')) {
            $('#salvage_value').addClass('is-invalid');
            $('#salvage_value_error').text(message);
        }
    });

    

$(document).ready(function () {   

    rebuildSupplierDatalist();

    function initCreateFormWithoutDraft(options) {
        const opts = options || {};
        const onReady = typeof opts.onReady === 'function' ? opts.onReady : null;
        let pending = 0;

        function trackComplete(callback) {
            pending++;
            return function () {
                if (typeof callback === 'function') {
                    callback.apply(this, arguments);
                }
                pending--;
                if (pending <= 0 && onReady) {
                    onReady();
                }
            };
        }

        if (REPOPULATE_FORM && OLD_STATUS) {
            $('select[name="status"]').val(OLD_STATUS);
        }

        syncLocationLabels();
        toggleAssetStructure();
        toggleDepreciationFields();
        if (opts.clearEmptyLocations && !isAssignedStatus()) {
            clearFromLocationFields();
            clearToLocationFields();
        }

        const companyForLoad = REPOPULATE_FORM ? (OLD_COMPANY || $('#company_id').val()) : '';
        if (companyForLoad) {
            if (REPOPULATE_FORM && OLD_COMPANY && String($('#company_id').val()) !== String(OLD_COMPANY)) {
                setCompanyIdSilently(OLD_COMPANY);
            }
            loadBranches(companyForLoad, OLD_BRANCH || null, OLD_LOCATION || null, trackComplete(null));
        }

        const toCompanyForLoad = REPOPULATE_FORM ? (OLD_TO_COMPANY || $('#to_company_id').val()) : '';
        if (isAssignedStatus() && toCompanyForLoad) {
            if (REPOPULATE_FORM && OLD_TO_COMPANY && String($('#to_company_id').val()) !== String(OLD_TO_COMPANY)) {
                suppressLocationCascade = true;
                $('#to_company_id').val(String(OLD_TO_COMPANY));
                suppressLocationCascade = false;
            }
            loadToBranches(toCompanyForLoad, OLD_TO_BRANCH || null, OLD_TO_LOCATION || null, trackComplete(null));
        }

        syncSupplierHidden();
        rebuildCustodianDatalist();
        syncCustodianHidden('from');
        syncCustodianHidden('to');
        if (OLD_CATEGORY) {
            pending++;
            loadProductSubCategories(OLD_CATEGORY, OLD_SUBCATEGORY, trackComplete(null));
        }
        fetchAssetNameSuggestions($('#asset_name').val() || '');

        if (pending === 0 && onReady) {
            onReady();
        }
    }

    const pageReload = isCreatePageReload();
    const draftSnap = loadAssetCreateDraft();
    const hasDraft = draftSnap && draftSnap.fields && Object.keys(draftSnap.fields).length > 0;

    if (REPOPULATE_FORM) {
        clearAssetCreateDraft();
        initCreateFormWithoutDraft({
            clearEmptyLocations: false,
            onReady: function () {
                saveAssetCreateDraft();
            },
        });
        if (DUPLICATE_SAVE_MESSAGE) {
            toastSuccessAssetForm(DUPLICATE_SAVE_MESSAGE);
            $('input[name="asset_code"]').trigger('focus');
        }
    } else if (pageReload && hasDraft) {
        restoreCreateFormFromDraft(draftSnap);
    } else {
        clearAssetCreateDraft();
        initCreateFormWithoutDraft({ clearEmptyLocations: true });
    }

    $('#assetMainForm').on('input change', 'input, select, textarea', function () {
        scheduleSaveAssetCreateDraft();
    });
    $('#supplier_display').on('input change blur', scheduleSaveAssetCreateDraft);
    $assetNameInput.on('input change blur', scheduleSaveAssetCreateDraft);
    $(window).on('beforeunload', saveAssetCreateDraft);

    $('#assetMainForm').on('submit', function (e) {
        syncSupplierHidden();
        syncCustodianHidden('from');
        syncCustodianHidden('to');
        syncLocationHidden('from');
        syncLocationHidden('to');
        toggleAssetStructure();
        const rateError = depreciationRateError();
        if (rateError) {
            e.preventDefault();
            showDepreciationRateError(rateError);
            return false;
        }
        if (!$('#supplier').val()) {
            e.preventDefault();
            alert('Choose a supplier from the suggestions (matching name exactly), or type a new name and click +.');
            $('#supplier_display').trigger('focus');
            return false;
        }
        if (!$('#branch_id').val()) {
            e.preventDefault();
            alert(isAssignedStatus() ? 'Select a from branch.' : 'Select a branch for the selected company.');
            $('#branch_id').trigger('focus');
            return false;
        }
        if (isAssignedStatus() && !$('#to_branch').val()) {
            e.preventDefault();
            alert('Select a to branch for assignment.');
            $('#to_branch').trigger('focus');
            return false;
        }
        const typedCustodian = ($('#custodian_display').val() || '').trim();
        if (typedCustodian && !$('#custodian_id').val()) {
            e.preventDefault();
            alert('Choose an asset custodian from the suggestions (matching name exactly), or type a new name and click +.');
            $('#custodian_display').trigger('focus');
            return false;
        }
        const typedToCustodian = ($('#to_custodian_display').val() || '').trim();
        if (isAssignedStatus() && typedToCustodian && !$('#to_custodian_id').val()) {
            e.preventDefault();
            alert('Choose an asset custodian from the suggestions (matching name exactly), or type a new name and click +.');
            $('#to_custodian_display').trigger('focus');
            return false;
        }
        const typedFromLocation = ($('#location_display').val() || '').trim();
        if (typedFromLocation && !$('#location_id').val()) {
            e.preventDefault();
            alert('Choose a physical location from the suggestions (matching name exactly), or type a new name and click +.');
            $('#location_display').trigger('focus');
            return false;
        }
        const typedToLocation = ($('#to_location_display').val() || '').trim();
        if (isAssignedStatus() && typedToLocation && !$('#to_location').val()) {
            e.preventDefault();
            alert('Choose a to physical location from the suggestions (matching name exactly), or type a new name and click +.');
            $('#to_location_display').trigger('focus');
            return false;
        }
        const typedCostCentre = ($('#cost_centre').val() || '').trim();
        if (typedCostCentre && !matchingCostCentre(typedCostCentre)) {
            e.preventDefault();
            alert('Choose a cost centre from the suggestions (matching name exactly), or type a new name and click +.');
            $('#cost_centre').trigger('focus');
            return false;
        }
        if (typedCostCentre) {
            $('#cost_centre').val(matchingCostCentre(typedCostCentre));
        }
        const submitter = e.originalEvent && e.originalEvent.submitter;
        const isSaveAndDuplicate = submitter && submitter.name === 'save_and_duplicate';
        if (!isSaveAndDuplicate) {
            clearAssetCreateDraft();
        }
        return true;
    });

});

</script>

@endsection
