@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Purchase Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit {{ $purchaseOrder->po_no }}</h4>
    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control"
                           value="{{ old('purchase_date', optional($purchaseOrder->purchase_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control"
                           value="{{ old('delivery_date', optional($purchaseOrder->delivery_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control"
                           value="{{ old('payment_terms', $purchaseOrder->payment_terms) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $purchaseOrder->remarks) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
