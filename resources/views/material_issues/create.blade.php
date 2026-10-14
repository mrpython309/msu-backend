@extends('layouts/contentNavbarLayout')

@section('title', 'Issue Material')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Issue Material</h4>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card mb-4">
    <form action="{{ route('material-issues.store') }}" method="POST" id="issue-form">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    @php
                        $selectedBranchId = old('branch_id');
                        if ($selectedBranchId && ! ($branches ?? collect())->contains('id', (int) $selectedBranchId)) {
                            $selectedBranchId = '';
                        }
                    @endphp
                    <select name="branch_id" id="branch_id" class="form-select select2 @error('branch_id') is-invalid @enderror" required>
                        <option value="">Select Branch</option>
                        @foreach(($branches ?? collect()) as $branch)
                            <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->code ? $branch->code.' - ' : '' }}{{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material Request <span class="text-danger">*</span></label>
                    <select name="material_request_id" id="material_request_id" class="form-select select2 @error('material_request_id') is-invalid @enderror" required disabled>
                        <option value="">Select Branch first</option>
                        @foreach($materialRequests as $mr)
                            <option value="{{ $mr->id }}"
                                data-branch-id="{{ $mr->branch_id }}"
                                {{ (string) old('material_request_id') === (string) $mr->id ? 'selected' : '' }}>
                                {{ $mr->mr_number }} ({{ str_replace('_', ' ', $mr->status) }})
                            </option>
                        @endforeach
                    </select>
                    @error('material_request_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-select select2">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) old('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_master_id" id="employee_master_id" class="form-select select2">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) old('employee_master_id') === (string) $emp->id ? 'selected' : '' }}>{{ $emp->employee_name }}</option>
                        @endforeach
                    </select>
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
                            <th>Previously Issued</th>
                            <th>Available Stock</th>
                            <th>HSN</th>
                            <th>Issue Qty <span class="text-danger">*</span></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Select a branch and Material Request to load items.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Confirm Issue</button>
            <a href="{{ route('material-issues.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('page-script')
<script>
(function() {
    function boot() {
        if (typeof window.jQuery === 'undefined') {
            setTimeout(boot, 50);
            return;
        }
        initIssueForm(window.jQuery);
    }

    function initIssueForm($) {
        var mrItemsUrl = @json(url('material-issues/get-material-request-items'));
        var oldMrId = @json(old('material_request_id'));
        var $mrSelect = $('#material_request_id');
        var allMrOptions = $mrSelect.find('option').clone();

        function initSearchableSelect($el) {
            if (!$el || !$el.length || typeof $.fn.select2 !== 'function') return;
            $el.each(function() {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    placeholder: 'Select',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            });
        }

        function clearMrItems() {
            $('#items-body').html('<tr><td colspan="7" class="text-center text-muted">Select a branch and Material Request to load items.</td></tr>');
        }

        function filterMaterialRequests(preserveSelected) {
            var branchId = String($('#branch_id').val() || '');
            var selectedMr = preserveSelected ? String($mrSelect.val() || oldMrId || '') : '';

            $mrSelect.empty();
            if (!branchId) {
                $mrSelect.append('<option value="">Select Branch first</option>');
                $mrSelect.prop('disabled', true).val('');
                initSearchableSelect($mrSelect);
                clearMrItems();
                return;
            }

            $mrSelect.prop('disabled', false);
            $mrSelect.append('<option value="">Select Material Request</option>');
            var matched = 0;
            allMrOptions.each(function() {
                var $opt = $(this);
                var value = String($opt.attr('value') || '');
                if (!value) return;
                if (String($opt.attr('data-branch-id') || '') === branchId) {
                    var $clone = $opt.clone();
                    if (selectedMr && selectedMr === value) {
                        $clone.prop('selected', true);
                    }
                    $mrSelect.append($clone);
                    matched++;
                }
            });

            if (!matched) {
                $mrSelect.append('<option value="" disabled>No Material Requests available for this branch</option>');
            }

            if (!selectedMr || String($mrSelect.val() || '') !== selectedMr) {
                $mrSelect.val('');
                clearMrItems();
            }
            initSearchableSelect($mrSelect);
        }

        function loadMrItems() {
            var branchId = String($('#branch_id').val() || '');
            var mrId = $mrSelect.val();
            var $opt = $mrSelect.find('option:selected');
            var tbody = $('#items-body');

            if (!branchId) {
                clearMrItems();
                return;
            }

            if (mrId && String($opt.attr('data-branch-id') || '') !== branchId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Selected Material Request does not belong to the selected branch.' });
                }
                $mrSelect.val('').trigger('change.select2');
                clearMrItems();
                return;
            }

            if (!mrId) {
                clearMrItems();
                return;
            }

            tbody.html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');

            $.ajax({
                url: mrItemsUrl + '/' + mrId,
                type: 'GET',
                data: { branch_id: branchId },
                success: function(response) {
                    if (response.department_id) {
                        $('#department_id').val(response.department_id).trigger('change');
                    }
                    if (response.employee_master_id) {
                        $('#employee_master_id').val(response.employee_master_id).trigger('change');
                    }

                    tbody.empty();

                    if (!response.items || response.items.length === 0) {
                        tbody.html('<tr><td colspan="7" class="text-center text-muted">No items found for this request.</td></tr>');
                        return;
                    }

                    response.items.forEach(function(item, index) {
                        let issueInput = '';
                        let remaining = parseFloat(item.remaining_qty);

                        if (remaining > 0) {
                            let maxIssue = remaining;
                            let defaultQty = maxIssue;
                            issueInput = `<input type="number" name="items[${index}][issue_qty]" class="form-control issue-qty-input" step="0.001" min="0" max="${maxIssue}" value="${defaultQty}">
                                          <div class="text-danger small mt-1 d-none qty-error"></div>
                                          <input type="hidden" name="items[${index}][material_request_item_id]" value="${item.id}">`;
                        } else {
                            issueInput = `<span class="badge bg-success">Fully Issued</span>`;
                        }

                        let row = `
                            <tr>
                                <td>${item.item_name}</td>
                                <td>${item.unit_name}</td>
                                <td>${item.requested_qty}</td>
                                <td>${item.issued_qty ?? 0}</td>
                                <td class="fw-bold">${item.available_stock}</td>
                                <td>${item.hsn}</td>
                                <td>${issueInput}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load items. Please try again.';
                    tbody.html('<tr><td colspan="7" class="text-center text-danger">' + msg + '</td></tr>');
                }
            });
        }

        initSearchableSelect($('#branch_id, #department_id, #employee_master_id'));

        $('#branch_id').on('change', function() {
            filterMaterialRequests(false);
        });

        filterMaterialRequests(true);
        $mrSelect.on('change', loadMrItems);
        if ($mrSelect.val()) {
            loadMrItems();
        }

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
    }

    boot();
})();
</script>
@endsection
