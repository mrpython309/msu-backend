<script>
(function() {
    function boot() {
        if (typeof window.jQuery === 'undefined') {
            setTimeout(boot, 50);
            return;
        }
        initMrItems(window.jQuery);
    }

    function initMrItems($) {
        var itemIndex = $('#items-body .mr-item-row').length;
        var mrItems = @json($mrItemOptions ?? []);
        var rowTemplate = ''
            + '<tr class="mr-item-row">'
            + '<td><div class="position-relative">'
            + '<input type="text" class="form-control item-autocomplete" list="mr-item-options" placeholder="Type to search item..." autocomplete="off" required>'
            + '<input type="hidden" name="items[__INDEX__][msu_item_master_id]" class="item-select-hidden">'
            + '<div class="text-danger small mt-1 d-none item-error"></div>'
            + '</div></td>'
            + '<td><input type="hidden" name="items[__INDEX__][unit_id]" class="unit-id-input">'
            + '<input type="text" class="form-control unit-display" readonly></td>'
            + '<td><input type="text" class="form-control stock-display" readonly></td>'
            + '<td><input type="number" name="items[__INDEX__][requested_qty]" class="form-control requested-qty" step="0.001" min="0.001" required>'
            + '<div class="text-danger small mt-1 d-none qty-error"></div></td>'
            + '<td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bx bx-trash"></i></button></td>'
            + '</tr>';

        function showError(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: message });
            } else {
                alert(message);
            }
        }

        function normalizeItemText(val) {
            return String(val || '')
                .replace(/\s+/g, ' ')
                .replace(/\s*-\s*/g, '-')
                .trim()
                .toLowerCase();
        }

        function findItem(val) {
            val = String(val || '').trim();
            if (!val) {
                return null;
            }

            var i;
            var item;
            for (i = 0; i < mrItems.length; i++) {
                item = mrItems[i];
                if (item.short_label === val || item.label === val || String(item.item_code) === val) {
                    return item;
                }
            }

            var normalized = normalizeItemText(val);
            for (i = 0; i < mrItems.length; i++) {
                item = mrItems[i];
                if (
                    normalizeItemText(item.short_label) === normalized
                    || normalizeItemText(item.label) === normalized
                    || normalizeItemText(item.item_code) === normalized
                ) {
                    return item;
                }
            }

            var hits = [];
            for (i = 0; i < mrItems.length; i++) {
                item = mrItems[i];
                var fullLabel = String(item.label || '');
                var shortLabel = String(item.short_label || '');
                if (fullLabel.indexOf(val) === 0 || (shortLabel && val.indexOf(shortLabel) === 0)) {
                    hits.push(item);
                }
            }
            if (hits.length === 1 && val.length >= 8) {
                return hits[0];
            }

            return null;
        }

        function clearItemFields($row) {
            $row.find('.unit-display').val('');
            $row.find('.unit-id-input').val('');
            $row.find('.stock-display').val('');
            $row.find('.requested-qty').removeAttr('max').removeClass('is-invalid');
            $row.find('.qty-error').addClass('d-none').text('');
        }

        function validateQty($row) {
            var $qtyInput = $row.find('.requested-qty');
            var $hidden = $row.find('.item-select-hidden');
            var $errorMsg = $row.find('.qty-error');
            var val = parseFloat($qtyInput.val()) || 0;
            var stock = parseFloat($row.find('.stock-display').val());
            if (isNaN(stock)) {
                stock = 0;
            }

            if ($hidden.val() === '') {
                $qtyInput.removeClass('is-invalid');
                $errorMsg.addClass('d-none');
                return true;
            }

            if (stock <= 0) {
                $qtyInput.addClass('is-invalid');
                $errorMsg.removeClass('d-none').text('Stock is not available');
                return false;
            }

            if ($qtyInput.val() && val > stock) {
                $qtyInput.addClass('is-invalid');
                $errorMsg.removeClass('d-none').text('Max ' + stock + ' allowed');
                return false;
            }

            $qtyInput.removeClass('is-invalid');
            $errorMsg.addClass('d-none');
            return true;
        }

        function applyItem($row, item) {
            var $input = $row.find('.item-autocomplete');
            var $hidden = $row.find('.item-select-hidden');
            var $errorMsg = $row.find('.item-error');
            var id = String(item.id);
            var isDuplicate = false;

            $('.item-select-hidden').not($hidden).each(function() {
                if (String($(this).val()) === id) {
                    isDuplicate = true;
                }
            });

            if (isDuplicate) {
                $input.addClass('is-invalid');
                $errorMsg.removeClass('d-none').text('This item is already added.');
                $hidden.val('');
                clearItemFields($row);
                showError('This item is already added to the material request.');
                return false;
            }

            $input.removeClass('is-invalid');
            $errorMsg.addClass('d-none');
            $hidden.val(id);
            if ($input.val() !== item.short_label) {
                $input.val(item.short_label);
            }
            $input.attr('title', item.label || item.short_label);

            $row.find('.unit-display').val(item.uom || '');
            $row.find('.unit-id-input').val(item.unit_id || '');
            var stock = parseFloat(item.stock);
            $row.find('.stock-display').val(isNaN(stock) ? 0 : stock);
            validateQty($row);
            return true;
        }

        function onItemInput() {
            var $this = $(this);
            var val = $this.val();
            var $row = $this.closest('.mr-item-row');
            var $hidden = $row.find('.item-select-hidden');
            var $errorMsg = $row.find('.item-error');

            if (!val) {
                $hidden.val('');
                $errorMsg.addClass('d-none');
                $this.removeClass('is-invalid');
                $this.attr('title', '');
                clearItemFields($row);
                return;
            }

            var item = findItem(val);
            if (item) {
                applyItem($row, item);
            } else {
                $hidden.val('');
            }
        }

        function addItemRow() {
            var html = rowTemplate.replace(/__INDEX__/g, itemIndex);
            var tbody = document.getElementById('items-body');
            if (tbody && html) {
                tbody.insertAdjacentHTML('beforeend', html);
                itemIndex++;
            }
        }

        function validateForm() {
            var $form = $('#material-request-form');
            var errors = [];

            $('#items-body .item-autocomplete').each(function() {
                onItemInput.call(this);
            });

            var $branch = $form.find('select[name="branch_id"]');
            if ($branch.length && $branch.prop('required') && !$branch.val()) {
                errors.push('Please select a Branch.');
                if ($branch.hasClass('select2-hidden-accessible')) {
                    $branch.next('.select2-container').addClass('is-invalid');
                }
            }

            var $date = $form.find('input[name="request_date"]');
            if ($date.length && $date.prop('required') && !$date.val()) {
                errors.push('Please select a Request Date.');
                $date.addClass('is-invalid');
            }

            $('#items-body .mr-item-row').each(function() {
                var $row = $(this);
                var $input = $row.find('.item-autocomplete');
                var $hidden = $row.find('.item-select-hidden');
                var $qty = $row.find('.requested-qty');

                if (!$hidden.val()) {
                    $input.addClass('is-invalid');
                    $row.find('.item-error').removeClass('d-none').text('Please select a valid item.');
                    errors.push('Please select a valid item.');
                }

                if (!$qty.val() || parseFloat($qty.val()) <= 0) {
                    $qty.addClass('is-invalid');
                    errors.push('Please enter requested quantity.');
                } else if (!validateQty($row)) {
                    errors.push($row.find('.qty-error').text() || 'Requested quantity is invalid.');
                }
            });

            if (!errors.length) {
                return true;
            }

            var unique = [];
            errors.forEach(function(msg) {
                if (unique.indexOf(msg) === -1) {
                    unique.push(msg);
                }
            });
            showError(unique[0]);
            return false;
        }

        $(document).off('.mrItems');
        $(document).on('input.mrItems change.mrItems', '.item-autocomplete', onItemInput);
        $(document).on('change.mrItems keyup.mrItems', '.requested-qty', function() {
            validateQty($(this).closest('.mr-item-row'));
        });
        $(document).on('click.mrItems', '#add-item-btn', function(e) {
            e.preventDefault();
            addItemRow();
        });
        $(document).on('click.mrItems', '.remove-row', function() {
            if ($('#items-body .mr-item-row').length > 1) {
                $(this).closest('.mr-item-row').remove();
            } else {
                alert('At least one item is required.');
            }
        });

        $(document).on('submit.mrItems', '#material-request-form', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        $('#items-body .item-autocomplete').each(function() {
            if ($(this).val()) {
                onItemInput.call(this);
            }
        });
    }

    boot();
})();
</script>
