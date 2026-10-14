@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Work Order')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ route('msu-work-orders.index') }}">New Work Orders</a> /</span> Edit {{ $msuWorkOrder->wo_number }}
</h4>
@include('msu-work-orders._form')
@endsection

@section('page-script')
@include('msu-work-orders._form-scripts')
@endsection


