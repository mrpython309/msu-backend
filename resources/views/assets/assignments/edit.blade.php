@extends('assets.asset-layout')

@section('asset_section')

<div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Update Assignment</h5>
        <div class="card-action-element">
          <a class="btn btn-sm btn-primary text-white" href="{{ asset_show_section_url($asset, 'assignments') }}">Back to Assigned List</a>
        </div>
      </div>
      <div class="card-body">

        @php
            if (! isset($assignment)) {
                $assignment = $asset->latestAssignment;
            }
            $selectedCustodianId = old('assiged_emoloyee', $assignment?->assiged_emoloyee ?: $asset->custodian_id);
            $selectedDepartmentId = old('department_id', $assignment?->department_id ?: $asset->department_id);
        @endphp

        <form action="{{ route('asset.assignment.update',$assignment) }}" method="POST">

        <div class="row g-3">

           <div class="col-4">
            <label class="form-label form-label-required" for="assign_date">Date <span class="text-danger">*</span></label>
            <input type="date" name="assign_date" class="form-control @error('assign_date') is-invalid @enderror" value="{{ old('assign_date',date('Y-m-d',strtotime($assignment->assign_date))) }}">
            @error('assign_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
           </div>

          <div class="col-4">
            <label class="form-label form-label-required" for="to_company_id">Company <span class="text-danger">*</span></label>
            <select id="to_company_id" name="to_company_id" class="form-select assignment-select @error('to_company_id') is-invalid @enderror" required>
              <option value="">Select</option>
              @foreach($companies as $company)  
              <option value="{{$company->id}}" {{ old('to_company_id',$assignment->to_company_id) == $company->id ? 'selected' : '' }}>{{$company->name}}</option>
              @endforeach
            </select>
            @error('to_company_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

           <div class="col-4">
            <label class="form-label form-label-required" for="to_branch">Branch <span class="text-danger">*</span></label>
            <select id="to_branch" name="to_branch" class="form-select assignment-select @error('to_branch') is-invalid @enderror" required>
              <option value="">Select</option>
              @foreach($branches as $branch)  
              <option value="{{$branch->id}}" {{ old('to_branch',$assignment->to_branch) == $branch->id ? 'selected' : '' }}>{{$branch->name}}</option>
              @endforeach
              
            </select>
            @error('to_branch')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-4">
            <label class="form-label" for="to_location">Physical Location</label>
            <select id="to_location" name="to_location" class="form-select assignment-select @error('to_location') is-invalid @enderror">
                <option value="">Select</option>
                @foreach($locations as $location)  
                <option value="{{$location->id}}" {{ old('to_location',$assignment->to_location) == $location->id ? 'selected' : '' }}>{{$location->location_name}}</option>
                @endforeach

            </select>
            @error('to_location')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            </div>

            <div class="col-4">
            <label class="form-label" for="assiged_emoloyee">Asset Custodian</label>
            <select id="assiged_emoloyee" name="assiged_emoloyee" class="form-select assignment-select @error('assiged_emoloyee') is-invalid @enderror">
                <option value="">Select</option>
                @foreach ($custodians as $custodian)
                    <option value="{{ $custodian->id }}" data-department="{{ $custodian->department_id }}" @selected((string) $selectedCustodianId === (string) $custodian->id)>
                        {{ $custodian->employee_name }}
                    </option>
                @endforeach
            </select>
            @error('assiged_emoloyee')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            </div>

            <div class="col-4">
            <label class="form-label" for="department_id">Department</label>
            <select id="department_id" name="department_id" class="form-select assignment-select @error('department_id') is-invalid @enderror">
                <option value="">Select</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>
                        {{ $department->department_name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            </div>
       
            <div class="col-4">
            <label class="form-label" for="notes">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes',$assignment->notes) }}</textarea>
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>

         <div class="pt-5 float-end">
            @csrf
            @method('PUT')
            <input type="hidden" name="asset_id" id="asset_id" value="{{$asset->id}}">
                        <button type="submit" class="btn btn-primary">
                            Assign
                        </button>
                        <a href="{{asset_show_section_url($asset, 'assignments') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

        </form>
       
       

      </div>
    </div> 
 

 

@endsection



@section('page-script')
<script>
$(function () {

    const companyValue  = "{{ old('to_company_id', $assignment->to_company_id) }}";
    const branchValue   = "{{ old('to_branch', $assignment->to_branch) }}";
    const locationValue = "{{ old('to_location', $assignment->to_location) }}";

    const $company    = $('#to_company_id');
    const $branch     = $('#to_branch');
    const $location   = $('#to_location');
    const $employee   = $('#assiged_emoloyee');
    const $department = $('#department_id');

    let formReady = false;
    let suppressBranch = false;

    initSearch($('.assignment-select'));

    if (companyValue) {
        loadBranches(companyValue, branchValue);
    }

    $company.on('change', function () {
        resetBranch();
        resetLocation();

        if ($(this).val()) {
            loadBranches($(this).val());
        }
    });

    $branch.on('change', function () {
        if (suppressBranch) {
            return;
        }

        resetLocation();

        if ($(this).val()) {
            loadLocations($(this).val());
        }
    });

    $employee.on('change', function () {
        if (!formReady) {
            return;
        }

        const departmentId = $(this).find('option:selected').data('department');
        if (!departmentId) {
            return;
        }

        $department.val(String(departmentId)).trigger('change');
    });

    formReady = true;

    function initSearch($select) {
        if (typeof $.fn.select2 !== 'function') {
            return;
        }

        $select.each(function () {
            const $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                placeholder: $el.find('option[value=""]').first().text() || 'Select',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $(document.body)
            });
        });
    }

    function resetBranch() {
        replaceOptions($branch, 'Select', []);
    }

    function resetLocation() {
        replaceOptions($location, 'Select', []);
    }

    function replaceOptions($select, placeholder, options, selectedId) {
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.empty().append(new Option(placeholder, ''));
        options.forEach(function (item) {
            const selected = selectedId !== null && selectedId !== undefined && String(selectedId) === String(item.id);
            $select.append(new Option(item.label, item.id, selected, selected));
        });
        initSearch($select);
    }

    function loadBranches(companyId, selectedBranch = null) {
        suppressBranch = true;
        replaceOptions($branch, 'Loading...', []);

        $.getJSON(
            "{{ route('ajax.getbranch.withoutscope', ':id') }}".replace(':id', companyId)
        )
        .done(res => {
            const options = (res.data || []).map(branch => ({ id: branch.id, label: branch.name }));
            replaceOptions($branch, 'Select', options, selectedBranch);

            if (selectedBranch) {
                loadLocations(selectedBranch, locationValue);
            }

            suppressBranch = false;
        })
        .fail(() => {
            resetBranch();
            suppressBranch = false;
        });
    }

    function loadLocations(branchId, selectedLocation = null) {
        replaceOptions($location, 'Loading...', []);

        $.getJSON(
            "{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId)
        )
        .done(res => {
            const options = (res.data || []).map(loc => ({ id: loc.id, label: loc.location_name }));
            replaceOptions($location, 'Select', options, selectedLocation);
        })
        .fail(() => resetLocation());
    }

});
</script>


@endsection
