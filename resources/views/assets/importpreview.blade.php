@extends('layouts/contentNavbarLayout')

@section('title', 'Assets')
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Imported Assets Preview</h4>
      </div>
    </div>
 
     <div class="row g-6 mb-5">

            <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-success h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-check icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{$validAsset}}</h4>
        </div>
        <p class="mb-2">Valid Assets</p>
        
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-error icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{$invalidAsset}}</h4>
        </div>
        <p class="mb-2">Invalid Assets</p>
         
      </div>
    </div>
  </div>


          </div>

    {{-- DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Assets List</h5>
            </div>             
        </div>


         
        

           @php
                // Get first row data to extract headers
                $first = $imports->first();
                $columns = $first ? array_keys(json_decode($first->data, true)) : [];
            @endphp

        <div class="table-responsive">
            <table class="table card-table " id="dealerLocationsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Errors</th>
                        <th>Asset Name</th>
                        <th>Serial Number</th>
                        <th>Asset Structure</th>
                        <th>Items Count</th>
                        <th>Parent Category</th>
                        <th>Category</th>
                        <th>Asset Type</th>
                        <th>Operational Status</th>
                        <th>Condition</th>
                        <th>Purchase Date</th>
                        <th>Purchase Cost</th>
                        <th>Supplier</th>
                        <th>Brand</th>
                        <th>Invoice</th>
                        <th>Depreciation Method</th>
                        <th>Rate of Depreciation</th>
                        <th>Description</th>                        
                    </tr>
                </thead>
                <tbody>
                     @forelse($imports as $import)

                        @php
                            $data = json_decode($import->data, true);
                            $errors = json_decode($import->errors, true);
                        @endphp

                        <tr class="{{ $import->is_valid ? '' : 'table-danger' }}">
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if($import->is_valid)
                                    <span class="badge bg-success">Valid</span>
                                @else
                                    <span class="badge bg-danger">Invalid</span>
                                @endif
                            </td>

                            <td>
                                @if(!empty($errors))
                                    <ul class="mb-0 text-danger">
                                        @foreach($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>{{ $data['asset_name'] ?? '-' }}</td>
                            <td>{{ $data['serial_no'] ?? '-' }}</td>
                            <td>{{ $data['asset_structure'] ?? '-' }}</td>
                            <td>{{ $data['items_count'] ?? '-' }}</td>
                            <td>{{ $data['category_original'] ?? '-' }}</td>
                            <td>{{ $data['sub_category_original'] ?? '-' }}</td>
                            <td>{{ $data['asset_type_original'] ?? '-' }}</td>
                            <td>{{ $data['operational_status_original'] ?? '-' }}</td>
                            <td>{{ $data['condition_original'] ?? '-' }}</td>
                            <td>{{ $data['purchase_date'] ?? '-' }}</td>
                            <td>{{ $data['purchase_cost'] ?? '-' }}</td>
                            <td>{{ $data['supplier_original'] ?? '-' }}</td>
                            <td>{{ $data['brand_original'] ?? '-' }}</td>
                            <td>{{ $data['invoice_details_original'] ?? '-' }}</td>
                            <td>{{ $data['depreciation_method_original'] ?? '-' }}</td>
                            <td>{{ $data['depreciation_rate'] ?? '-' }}</td>
                            <td>{{ $data['description_original'] ?? '-' }}</td> 

                        </tr>

                     @empty
                        <tr>
                            <td colspan="19" class="text-center">No Assets Found</td>
                        </tr>   

                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if($imports->hasPages())
                <div class="text-muted">Showing {{ $imports->firstItem() ?? 0 }} to {{ $imports->lastItem() ?? 0 }} of {{ $imports->total() }} Assets</div>
                <div>{{ $imports->links() }}</div>
            @endif
        </div>

 
        <div class="row p-4">

          <div class="col-md-12 ">

            @if($validAsset > 0)

            <a href="{{route('asset.import.movevaliddata')}}" class="btn btn-primary float-end">Move Valid Data to Asset</a>

            @endif
          </div>


        </div>



    </div>
@endsection
 
