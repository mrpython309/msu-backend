@extends('layouts/contentNavbarLayout')

@section('title', 'Assign Bulk Assets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
    <style>
        .avatar-initial {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .fs-12px { font-size: 0.75rem !important; }
        .form-container {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Assign Bulk Assets</h4>
      </div>
    </div>

  

    {{-- DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Un Assigned Assets List</h5>
                
            </div>
             
        </div>

        <div class="card-body">

            <form action="{{ route('asset.assign.bulk.process') }}" method="POST">

        <div class="table-responsive mb-4">
            <table class="table card-table " id="dealerLocationsTable">
                <thead>
                    <tr>
                        <th class="text-center">
                         Select All <br>
                            <input type="checkbox" id="select_all">
                        </th>
                        <th>Name</th>
                        <th>Asset Code</th> 
                        <th>Purchase Date</th>
                        <th>Invoice Amount</th>
                        <th>Company</th>
                        <th>Branch</th>
                        <th>Physical Location</th> 
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="assets[]" value="{{$asset->id}}" id="asset-{{ $asset->id }}" value="{{ $asset->id }}">
                        </td>
                        <td>

                <div class="d-flex justify-content-start align-items-center customer-name">
                 @if($asset->icon == 'images')   
                <div class="avatar-wrapper">
                  <div class="avatar me-4 rounded-2 bg-label-secondary">
                    <a href="{{ $asset->doc_url }}" data-lightbox="gallery">
                <img src="{{ $asset->doc_url }}" alt="{{ $asset->asset_name }}" class="rounded">
                    </a>   
                 </div>
                </div>
                 @endif
                <div class="d-flex flex-column">
                  <span class="fw-medium text-nowrap text-heading">{{ $asset->asset_name }}</span>
                  <small>{{ $asset->assetType?->asset_type_name }}</small>
                </div>
              </div>


                            
                        </td>

                        <td>{{ $asset->asset_code }}</td>

                        

                        

                        <td>
                            {{ optional($asset->purchase_date)->format('d M Y') }}
                        </td>

                        <td>
                            ₹ {{ number_format($asset->purchase_cost, 2) }}
                        </td>

                        <td>
                            {{ $asset->company?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $asset->branch?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $asset->location?->location_name ?? '-' }}
                        </td>

                         
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No assets found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


       <div class="row g-3">

           <div class="col-4">
            <label class="form-label form-label-required" for="to_company_id">Company <span class="text-danger">*</span></label>
            <select id="to_company_id" name="to_company_id" class="form-select @error('to_company_id') is-invalid @enderror" required>
              <option value="">Select</option>
              @foreach($companies as $company)  
              <option value="{{$company->id}}" {{ old('to_company_id') == $company->id ? 'selected' : '' }}>{{$company->name}}</option>
              @endforeach
            </select>
            @error('to_company_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>


         <div class="col-4">
            <label class="form-label form-label-required" for="to_branch">Branch <span class="text-danger">*</span></label>
            <select id="to_branch" name="to_branch" class="form-select @error('to_branch') is-invalid @enderror" required>
              <option value="">Select</option>              
            </select>
            @error('to_branch')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-4">
            <label class="form-label form-label-required" for="to_location">Physical Location</label>
            <select id="to_location" name="to_location" class="form-select @error('to_location') is-invalid @enderror">
                <option value="">Select</option>
            </select>
            @error('to_location')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            </div> 
        </div>


         <div class="pt-5 float-end">
            @csrf
                 <button type="submit" class="btn btn-primary">
                            Assign
                        </button>
                        
                    </div>


            </form>
        </div>


         
    </div>

   
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>

    /* ===============================
 * Select All Logic
 * =============================== */

const $selectAll = $('#select_all');
const $checkboxes = $('input[name="assets[]"]');

// Select / Unselect All
$selectAll.on('change', function () {
    $checkboxes.prop('checked', $(this).is(':checked'));
});

// If any checkbox unchecked → uncheck select all
$(document).on('change', 'input[name="assets[]"]', function () {
    if (!$(this).is(':checked')) {
        $selectAll.prop('checked', false);
    } else if ($('input[name="assets[]"]:checked').length === $checkboxes.length) {
        $selectAll.prop('checked', true);
    }
});

$(function () {

     
    const oldCompany    = "{{ old('to_company_id') }}";
    const oldBranch     = "{{ old('to_branch') }}";
    const oldLocation   = "{{ old('to_location') }}"; 

    /* ===============================
     * Cache DOM
     * =============================== */
  
    const $company     = $('#to_company_id');
    const $branch      = $('#to_branch');
    const $location    = $('#to_location');
  
    /* ===============================
     * Initial Setup
     * =============================== */

    

    // If old company exists, reload chain
    if (oldCompany) {
        loadBranches(oldCompany, oldBranch);
    }

    if (oldBranch) {
        loadLocations(oldBranch, oldLocation);

        
    }

    $company.on('change', function () {
        const companyId = $(this).val();

        resetBranch();
        resetLocation(); 

        if (companyId) {
            loadBranches(companyId);
        }
    });

    $branch.on('change', function () {
        const branchId = $(this).val();

        resetLocation(); 

        if (!branchId) return;

        loadLocations(branchId);

         
    });

     

    function resetBranch() {
        $branch.html('<option value="">Select Branch</option>');
    }

    function resetLocation() {
        $location.html('<option value="">Select Physical Location</option>');
    }

    

    /* ===============================
     * Load Branches
     * =============================== */

    function loadBranches(companyId, selectedId = null) {
        $branch.html('<option value="">Loading...</option>');

        $.getJSON(
            "{{ route('ajax.getbranch.withoutscope', ':id') }}".replace(':id', companyId)
        )
        .done(res => {
            resetBranch();

            if (res.data?.length) {
                res.data.forEach(branch => {
                    $branch.append(
                        `<option value="${branch.id}" ${selectedId == branch.id ? 'selected' : ''}>
                            ${branch.name}
                        </option>`
                    );
                });

                if (selectedId) {
                    $branch.val(selectedId).trigger('change');
                }
            }
        })
        .fail(() => {
            resetBranch();
        });
    }

    /* ===============================
     * Load Locations
     * =============================== */

    function loadLocations(branchId, selectedId = null) {
        $location.html('<option value="">Loading...</option>');

        $.getJSON(
            "{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId)
        )
        .done(res => {
            resetLocation();

            if (res.data?.length) {
                res.data.forEach(loc => {
                    $location.append(
                        `<option value="${loc.id}" ${selectedId == loc.id ? 'selected' : ''}>
                            ${loc.location_name}
                        </option>`
                    );
                });
            }
        })
        .fail(() => {
            resetLocation();
        });
    }

    

});
</script>
@endsection
