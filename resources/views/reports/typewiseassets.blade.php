@extends('layouts/contentNavbarLayout')

@section('title', 'Typewise Assets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Typewise Assets</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.typewiseassets') }}" class="row g-3">
                @csrf
                 <div class="col-md-3">
                    <label class="form-label">Parent Category</label>
                    <select class="form-select" name="category_id" id="category_id">
                        <option value="">All Parent Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="sub_category_id" id="sub_category_id">
                        <option value="">All Categories</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ request('sub_category_id') == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Asset Type</label>
                    <select class="form-select" name="asset_type_id" id="asset_type_id">
                        <option value="">All Asset Types</option>
                        @foreach($assetTypes as $assettype)
                            <option value="{{ $assettype->id }}" {{ request('asset_type_id') == $assettype->id ? 'selected' : '' }}>
                                {{ $assettype->asset_type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                 

                 <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                         
                    </select>
                </div>

                 <div class="col-md-3">
                    <label class="form-label">Conditions</label>
                    <select class="form-select" name="condition">
                        <option value="">All Conditions</option>
                       @foreach ($conditions as $condition)
        <option value="{{ $condition->value }}" {{ request('condition') == $condition->value ? 'selected' : '' }}>
            {{ $condition->label() }}
        </option>
    @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Operational Status</label>
                    <select class="form-select" name="operational_status">
                        <option value="">All Operational Status</option>
                       @foreach ($operations_status as $operationStatus)
        <option value="{{ $operationStatus->value }}" {{ request('operational_status') == $operationStatus->value ? 'selected' : '' }}>
            {{ $operationStatus->label() }}
        </option>
    @endforeach
                    </select>
                </div>


                
               
                
                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.typewiseassets')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Categorywise Assets List</h5>
            </div>
             
        </div>


     @include('reports.partials.assets',['assets'=>$assets,'tableColumns'=>$tableColumns])

    </div>

    
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')

<script>

   function loadSubCategories(categoryId, selectedId = null) {
            const subCategoryDropDown = $('#sub_category_id');
            subCategoryDropDown.html('<option>Loading...</option>');

            if (!categoryId) {
                subCategoryDropDown.html('<option value="">Select Category</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getsubcategory.bycategory', ':id') }}".replace(':id', categoryId),
                function (res) {
                    subCategoryDropDown.empty().append('<option value="">Select Category</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            subCategoryDropDown.append(
                                `<option value="${value.id}" ${selected}>${value.category_name}</option>`
                            );
                        });
                    } else {
                        subCategoryDropDown.append('<option value="">No Category found</option>');
                    }
                }
            ) .fail(function () {
                subCategoryDropDown.html('<option value="">Failed to load Category</option>');
            });
   } 


 

        $('#category_id').on('change', function () {
            loadSubCategories(this.value);
        });


        function loadAssetTypes(subId, selectedId = null) {
            const assetTypeDropDown = $('#asset_type_id');
            assetTypeDropDown.html('<option>Loading...</option>');

            if (!subId) {
                assetTypeDropDown.html('<option value="">Select Asset Type</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getassettype.byassetcategory', ':id') }}".replace(':id', subId),
                function (res) {
                    assetTypeDropDown.empty().append('<option value="">Select Asset Type</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            assetTypeDropDown.append(
                                `<option value="${value.id}" ${selected}>${value.asset_type_name}</option>`
                            );
                        });
                    } else {
                        assetTypeDropDown.append('<option value="">No Asset Type found</option>');
                    }
                }           
            ) .fail(function () {
                assetTypeDropDown.html('<option value="">Failed to load Asset Type</option>');
            })
        }

        $('#sub_category_id').on('change', function () {
            loadAssetTypes(this.value);
        });
 



</script>

@endsection
 
