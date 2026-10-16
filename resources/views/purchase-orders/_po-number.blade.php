@php
    $linkedRfq = $po->linkedRfq();
@endphp

<span class="d-inline-flex align-items-center flex-nowrap gap-1">
    @if ($link ?? false)
        <a href="{{ route('purchase-orders.show', $po) }}" class="text-nowrap">{{ $po->po_no }}</a>
    @else
        <strong class="text-nowrap">{{ $po->po_no }}</strong>
    @endif
    @if ($linkedRfq)
        @if (Route::has('rfqs.show'))
            <a href="{{ route('rfqs.show', $linkedRfq) }}"
               class="badge bg-label-primary text-decoration-none flex-shrink-0"
               title="Created from RFQ {{ $linkedRfq->rfq_no }}"
               style="font-size: 0.65rem; padding: 0.2em 0.45em; font-weight: 600; line-height: 1;">
                <i class="bx bx-file" style="font-size: 0.75rem; vertical-align: -1px;"></i> RFQ
            </a>
        @else
            <span class="badge bg-label-primary flex-shrink-0"
                  title="Created from RFQ {{ $linkedRfq->rfq_no }}"
                  style="font-size: 0.65rem; padding: 0.2em 0.45em; font-weight: 600; line-height: 1;">
                <i class="bx bx-file" style="font-size: 0.75rem; vertical-align: -1px;"></i> RFQ
            </span>
        @endif
    @endif
</span>
