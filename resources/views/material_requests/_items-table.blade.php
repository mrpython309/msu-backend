@php
    $mrItemOptions = $items->map(function ($item) {
        $name = (string) $item->item_name;
        $shortName = mb_strlen($name) > 120 ? (mb_substr($name, 0, 120).'…') : $name;
        $code = (string) ($item->item_code ?? '');

        return [
            'id' => $item->id,
            'item_code' => $code,
            'label' => $code.' - '.$name,
            'short_label' => $code.' - '.$shortName,
            'uom' => $item->unit?->unit_name ?? '',
            'unit_id' => $item->unit_id,
            'stock' => $item->current_stock,
        ];
    })->values();
@endphp

<style>
    #items-table {
        table-layout: fixed;
        width: 100%;
        min-width: 760px;
    }
    #items-table th:first-child,
    #items-table td:first-child {
        width: 46%;
    }
    #items-table .item-autocomplete {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<h5 class="mb-3">Request Items</h5>
<datalist id="mr-item-options">
    @foreach($mrItemOptions as $item)
        <option value="{{ $item['short_label'] }}"
            data-id="{{ $item['id'] }}"
            data-code="{{ $item['item_code'] }}"
            data-label="{{ $item['label'] }}"
            data-uom="{{ $item['uom'] }}"
            data-unit-id="{{ $item['unit_id'] }}"
            data-stock="{{ $item['stock'] }}"></option>
    @endforeach
</datalist>
<div class="table-responsive">
    <table class="table table-bordered" id="items-table">
        <thead class="bg-light">
            <tr>
                <th>Item <span class="text-danger">*</span></th>
                <th>Unit</th>
                <th>Available Stock</th>
                <th>Requested Qty <span class="text-danger">*</span></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="items-body">
            @foreach($oldItems as $idx => $row)
                @php
                    $selectedId = $row['msu_item_master_id'] ?? '';
                    $found = $selectedId
                        ? $mrItemOptions->first(fn ($opt) => (string) $opt['id'] === (string) $selectedId)
                        : null;
                    $selectedLabel = $found['short_label'] ?? '';
                    $unitName = $row['unit_name'] ?? ($found['uom'] ?? '');
                    $unitId = $row['unit_id'] ?? ($found['unit_id'] ?? '');
                    $stock = $row['stock'] ?? ($found['stock'] ?? '');
                    $qty = $row['requested_qty'] ?? '';
                @endphp
                <tr class="mr-item-row">
                    <td>
                        @if(!empty($row['id']))
                            <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $row['id'] }}">
                        @endif
                        <div class="position-relative">
                            <input type="text" class="form-control item-autocomplete" list="mr-item-options"
                                   placeholder="Type to search item..." value="{{ $selectedLabel }}"
                                   title="{{ $found['label'] ?? $selectedLabel }}" autocomplete="off" required>
                            <input type="hidden" name="items[{{ $idx }}][msu_item_master_id]" class="item-select-hidden"
                                   value="{{ $selectedId }}">
                            <div class="text-danger small mt-1 d-none item-error"></div>
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="items[{{ $idx }}][unit_id]" class="unit-id-input" value="{{ $unitId }}">
                        <input type="text" class="form-control unit-display" value="{{ $unitName }}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control stock-display" value="{{ $stock }}" readonly>
                    </td>
                    <td>
                        <input type="number" name="items[{{ $idx }}][requested_qty]" class="form-control requested-qty"
                               step="0.001" min="0.001" value="{{ $qty }}" required>
                        <div class="text-danger small mt-1 d-none qty-error"></div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-row"><i class="bx bx-trash"></i></button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-primary mt-3" id="add-item-btn"><i class="bx bx-plus"></i> Add Item</button>
</div>
@include('material_requests._item-scripts')
