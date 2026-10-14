@php
    $rateRows = old('rates');
    if ($rateRows === null && isset($msuItemMaster)) {
        $rateRows = $msuItemMaster->rates->sortBy('effective_from')->values()->map(fn ($r) => [
            'id' => $r->id,
            'rate' => $r->rate,
            'tax' => $r->tax,
            'effective_from' => $r->effective_from?->format('Y-m-d'),
        ])->toArray();
    }
    $rateRows = $rateRows ?: [['id' => '', 'rate' => '', 'tax' => '', 'effective_from' => now()->format('Y-m-d')]];
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Rate Details</h5>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-rate-row">
            <i class="bx bx-plus"></i> Add Rate
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle" id="item-rates-table">
                <thead>
                    <tr>
                        <th>Rate <span class="text-danger">*</span></th>
                        <th>Tax (%)</th>
                        <th>Effective From <span class="text-danger">*</span></th>
                        <th style="width:70px"></th>
                    </tr>
                </thead>
                <tbody id="item-rates-body">
                    @foreach($rateRows as $idx => $rate)
                        <tr class="rate-row">
                            <td>
                                <input type="hidden" name="rates[{{ $idx }}][id]" value="{{ $rate['id'] ?? '' }}">
                                <input type="number" step="0.01" min="0" name="rates[{{ $idx }}][rate]" class="form-control" value="{{ $rate['rate'] ?? '' }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" max="100" name="rates[{{ $idx }}][tax]" class="form-control" value="{{ $rate['tax'] ?? 0 }}">
                            </td>
                            <td>
                                <input type="date" name="rates[{{ $idx }}][effective_from]" class="form-control" value="{{ $rate['effective_from'] ?? '' }}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-rate-row"><i class="bx bx-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="deleted-rate-ids"></div>
        <small class="text-muted">Applicable rate is the latest rate whose Effective From is on or before today (or PO date).</small>
    </div>
</div>
