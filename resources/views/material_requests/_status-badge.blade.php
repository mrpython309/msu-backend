@if($status === 'created')
    <span class="badge bg-label-secondary">Created</span>
@elseif($status === 'approved')
    <span class="badge bg-label-primary">Approved</span>
@elseif($status === 'partially_issued')
    <span class="badge bg-label-warning">Partially Issued</span>
@elseif($status === 'fully_issued')
    <span class="badge bg-label-success">Fully Issued</span>
@elseif($status === 'cancelled')
    <span class="badge bg-label-danger">Cancelled</span>
@else
    <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
@endif
