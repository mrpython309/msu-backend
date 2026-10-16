@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Item')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Master / <a href="{{ route('item-masters.index') }}">Item Master</a> /</span> Edit Item
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Edit Item Master ({{ $msuItemMaster->item_code }})</h5>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('item-masters.update', $msuItemMaster) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Item Code</label>
                            <input type="text" class="form-control" id="item_code_preview" value="{{ $msuItemMaster->item_code }}" disabled>
                            <small class="text-muted">Updates when product category changes.</small>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('item_name') is-invalid @enderror" type="text" id="item_name" name="item_name" value="{{ old('item_name', $msuItemMaster->item_name) }}" maxlength="1500" required>
                            <small class="text-muted">Maximum 1500 characters.</small>
                            @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="product_category_id" class="form-label">Product Category <span class="text-danger">*</span></label>
                            <select id="product_category_id" name="product_category_id" class="form-select select2 @error('product_category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($productCategories as $category)
                                    <option value="{{ $category->id }}" data-code="{{ $category->category_code }}" {{ old('product_category_id', $msuItemMaster->product_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }} ({{ $category->category_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="product_sub_category_id" class="form-label">Product Sub Category <span class="text-danger">*</span></label>
                            <select id="product_sub_category_id" name="product_sub_category_id" class="form-select select2 @error('product_sub_category_id') is-invalid @enderror" required>
                                <option value="">Select Sub Category</option>
                                @foreach($productSubCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}" {{ old('product_sub_category_id', $msuItemMaster->product_sub_category_id) == $subCategory->id ? 'selected' : '' }}>
                                        {{ $subCategory->sub_category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_sub_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="unit_id" class="form-label">Unit of Measurement <span class="text-danger">*</span></label>
                            <select id="unit_id" name="unit_id" class="form-select select2 @error('unit_id') is-invalid @enderror" required>
                                <option value="">Select UOM</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $msuItemMaster->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="hsn_id" class="form-label">HSN <span class="text-danger">*</span></label>
                            <select id="hsn_id" name="hsn_id" class="form-select select2 @error('hsn_id') is-invalid @enderror" required>
                                <option value="">Select HSN</option>
                                @foreach(($hsns ?? collect()) as $hsn)
                                    <option value="{{ $hsn->id }}" data-tax="{{ $hsn->tax_rate }}" {{ old('hsn_id', $msuItemMaster->hsn_id) == $hsn->id ? 'selected' : '' }}>
                                        {{ $hsn->hsn_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hsn_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="image" class="form-label">Browse Image</label>
                            <input type="file" name="image" id="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                            @if($msuItemMaster->image_url)
                                <div class="mt-2">
                                    <img src="{{ $msuItemMaster->image_url }}" alt="Item image" style="max-height:80px;border-radius:4px;">
                                </div>
                            @endif
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="threshold_quantity" class="form-label">Threshold Quantity</label>
                            <input class="form-control @error('threshold_quantity') is-invalid @enderror" type="number" step="0.001" min="0" id="threshold_quantity" name="threshold_quantity" value="{{ old('threshold_quantity', $msuItemMaster->threshold_quantity) }}" placeholder="e.g. 10">
                            <small class="text-muted">Stock above zero and at or below this quantity is marked Low Stock. Zero quantity is Out Stock.</small>
                            @error('threshold_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $msuItemMaster->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $msuItemMaster->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @include('item-masters._rate-details')

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Update Item</button>
                        <a href="{{ route('item-masters.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
@include('item-masters._form-scripts')
@endsection
