@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Purchase Order')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="{{ route('msu-purchase-orders.index') }}">New Purchase Orders</a> /</span> Edit {{ $msuPurchaseOrder->po_number }}
</h4>
@include('msu-purchase-orders._form')
@endsection

@section('page-script')
@include('msu-purchase-orders._form-scripts')
@endsection
