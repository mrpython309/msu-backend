<script>
(function() {
    var oldSubCategoryId = @json(old('product_sub_category_id', isset($msuItemMaster) ? $msuItemMaster->product_sub_category_id : null));
    var originalCategoryId = @json(isset($msuItemMaster) ? (string) old('product_category_id', $msuItemMaster->product_category_id) : null);
    var originalItemCode = @json(isset($msuItemMaster) ? $msuItemMaster->item_code : null);
    var subCategoryUrl = @json(route('item-masters.get-sub-categories'));
    var rateIndex = $('#item-rates-body .rate-row').length;

    function updateItemCodePreview() {
        var $selected = $('#product_category_id option:selected');
        var categoryId = String($selected.val() || '');
        var code = ($selected.data('code') || '').toString().toUpperCase();
        if (!categoryId) {
            $('#item_code_preview').val('Select a product category');
            return;
        }
        if (originalCategoryId && categoryId === String(originalCategoryId) && originalItemCode) {
            $('#item_code_preview').val(originalItemCode);
            return;
        }
        $('#item_code_preview').val(code ? (code + '0001 (next sequence on save)') : 'Will regenerate on save');
    }

    function loadSubCategories(categoryId, selectedId) {
        var $subCategorySelect = $('#product_sub_category_id');
        $subCategorySelect.empty().append('<option value="">Select Sub Category</option>');
        if (!categoryId) {
            $subCategorySelect.val('').trigger('change');
            return;
        }

        $.ajax({
            url: subCategoryUrl,
            type: 'GET',
            dataType: 'json',
            data: { product_category_id: categoryId },
            success: function(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    $subCategorySelect.append('<option value="" disabled>No sub categories found</option>');
                    $subCategorySelect.val('').trigger('change');
                    return;
                }
                $.each(data, function(key, value) {
                    var selected = (String(selectedId) === String(value.id)) ? 'selected' : '';
                    $subCategorySelect.append('<option value="' + value.id + '" ' + selected + '>' + value.sub_category_name + '</option>');
                });
                $subCategorySelect.val(selectedId || '').trigger('change');
            }
        });
    }

    function addRateRow() {
        var html = '<tr class="rate-row">' +
            '<td><input type="hidden" name="rates[' + rateIndex + '][id]" value="">' +
            '<input type="number" step="0.01" min="0" name="rates[' + rateIndex + '][rate]" class="form-control"></td>' +
            '<td><input type="number" step="0.01" min="0" max="100" name="rates[' + rateIndex + '][tax]" class="form-control" value="0"></td>' +
            '<td><input type="date" name="rates[' + rateIndex + '][effective_from]" class="form-control"></td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-rate-row"><i class="bx bx-trash"></i></button></td>' +
            '</tr>';
        $('#item-rates-body').append(html);
        rateIndex++;
    }

    $(document).ready(function() {
        $('#product_category_id, #product_sub_category_id, #unit_id, #hsn_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%'
        });

        $('#product_category_id').on('change', function() {
            updateItemCodePreview();
            loadSubCategories($(this).val(), null);
        });
        updateItemCodePreview();

        var currentCategoryId = $('#product_category_id').val();
        if (currentCategoryId && $('#product_sub_category_id option').length <= 1) {
            loadSubCategories(currentCategoryId, oldSubCategoryId);
        }

        $('#add-rate-row').on('click', addRateRow);
        $(document).on('click', '.remove-rate-row', function() {
            var $row = $(this).closest('tr');
            var id = $row.find('input[name*="[id]"]').val();
            if (id) {
                $('#deleted-rate-ids').append('<input type="hidden" name="deleted_rate_ids[]" value="' + id + '">');
            }
            if ($('#item-rates-body .rate-row').length > 1) {
                $row.remove();
            } else {
                $row.find('input[type="number"], input[type="date"]').val('');
                $row.find('input[name*="[id]"]').val('');
            }
        });
    });
})();
</script>
