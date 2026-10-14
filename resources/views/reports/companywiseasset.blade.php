@extends('layouts/contentNavbarLayout')

@section('title', 'Companywise Assets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Companywise Assets</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.companywiseassets') }}" class="row g-3">
                  @csrf
                 <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select class="form-select" name="company_id">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
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


                
               
                
                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.companywiseassets')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Companywise Assets List</h5>
            </div>
             
        </div>


     @include('reports.partials.assets',['assets'=>$assets,'tableColumns'=>$tableColumns])

    </div>

    
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection
 
