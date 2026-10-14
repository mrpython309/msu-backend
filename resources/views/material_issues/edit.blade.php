@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Material Issue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Material Issue: {{ $materialIssue->mi_number }}</h4>
</div>

<div class="card mb-4">
    <form action="{{ route('material-issues.update', $materialIssue->id) }}" method="POST" id="issue-form">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control" value="{{ $materialIssue->issue_date }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material Request</label>
                    <input type="text" class="form-control" value="{{ $materialIssue->materialRequest->mr_number }}" readonly disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-select select2">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $materialIssue->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_master_id" id="employee_master_id" class="form-select select2">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $materialIssue->employee_master_id == $emp->id ? 'selected' : '' }}>{{ $emp->employee_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ $materialIssue->remarks }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Request Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Unit</th>
                            <th>Requested Qty</th>
                            <th>Available Stock</th>
                            <th>HSN</th>
                            <th>Issue Qty <span class="text-danger">*</span></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        @foreach($materialIssue->items as $index => $item)
                        @php
                            $mrItem = $item->materialRequestItem;
                            // The maximum they can issue now is the current issue_qty + what's remaining on the MR item
                            $maxIssue = $item->issue_qty + $mrItem->remaining_qty;
                        @endphp
                        <tr>
                            <td>{{ $mrItem->itemMaster->item_name }}</td>
                            <td>{{ $mrItem->unit->unit_name }}</td>
                            <td>{{ $mrItem->requested_qty }}</td>
                            <td class="fw-bold">{{ number_format((float)($mrItem->itemMaster->current_stock ?? 0), 3) }}</td>
                            <td>{{ $mrItem->itemMaster->hsn->hsn_code ?? $mrItem->itemMaster->hsn->code ?? '' }}</td>
                            <td>
                                <input type="number" name="items[{{ $index }}][issue_qty]" class="form-control issue-qty-input" step="0.001" min="0" max="{{ $maxIssue }}" value="{{ $item->issue_qty }}">
                                <div class="text-danger small mt-1 d-none qty-error"></div>
                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Issue</button>
            <a href="{{ route('material-issues.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('.select2').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                placeholder: $select.find('option[value=""]').first().text() || 'Select',
                allowClear: $select.find('option[value=""]').length > 0
            });
        });

        // Validation for issue quantity
        $(document).on('change keyup', '.issue-qty-input', function() {
            let val = parseFloat($(this).val());
            if (isNaN(val)) val = 0;
            let max = parseFloat($(this).attr('max')) || 0;
            let $errorMsg = $(this).siblings('.qty-error');

            if (val <= 0) {
                $(this).addClass('is-invalid');
                $errorMsg.removeClass('d-none').text('Issue quantity must be greater than 0');
            } else if (val > max) {
                $(this).addClass('is-invalid');
                $errorMsg.removeClass('d-none').text('Max ' + max + ' allowed');
                $(this).val(max > 0 ? max : '');
            } else {
                $(this).removeClass('is-invalid');
                $errorMsg.addClass('d-none').text('');
            }
        });

        $('#issue-form').on('submit', function(e) {
            let hasError = false;
            let hasQty = false;

            $('.issue-qty-input').each(function() {
                let val = parseFloat($(this).val());
                if (isNaN(val)) val = 0;
                let max = parseFloat($(this).attr('max')) || 0;
                let $errorMsg = $(this).siblings('.qty-error');

                if (val <= 0) {
                    hasError = true;
                    $(this).addClass('is-invalid');
                    $errorMsg.removeClass('d-none').text('Issue quantity must be greater than 0');
                } else if (val > max) {
                    hasError = true;
                    $(this).addClass('is-invalid');
                    $errorMsg.removeClass('d-none').text('Max ' + max + ' allowed');
                } else {
                    hasQty = true;
                    $(this).removeClass('is-invalid');
                    $errorMsg.addClass('d-none').text('');
                }
            });

            if (hasError || !hasQty) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection
