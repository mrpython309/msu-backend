@if($status === 'cancelled')
    <span class="badge bg-label-danger">Cancelled</span>
@elseif($status === 'partially_issued')
    <span class="badge bg-label-warning">Partially Issued</span>
@elseif($status === 'fully_issued')
    <span class="badge bg-label-success">Fully Issued</span>
@else
    <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
@endif
