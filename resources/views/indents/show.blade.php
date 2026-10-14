@extends('layouts/contentNavbarLayout')

@section('title', 'Indent Details')

@section('content')
@php
    $statusValue = $indent->status?->value ?? (string) $indent->status;
    $displayStatus = $indent->displayStatusLabel();
    $latestDecision = $indent->latestDecisionHistory();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Indent {{ $indent->indent_no }}</h4>
    <div class="d-flex gap-2">
        @can('indents:edit')
            @if ($indent->canBeEdited())
                <a href="{{ route('indents.edit', $indent) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
            @endif
        @endcan
        <a href="{{ route('indents.index') }}" class="btn btn-secondary btn-sm">Back to list</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Indent Details</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Request No.</small>
                        <strong>{{ $indent->indent_no }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Request Date &amp; Time</small>
                        <strong>{{ ($indent->requested_at ?? $indent->submitted_at ?? $indent->created_at)?->format('d M Y h:i A') ?? ($indent->request_date?->format('d M Y') ?? '--') }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Required Date</small>
                        <strong>{{ $indent->required_date?->format('d M Y') ?? '--' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">From Store</small>
                        <strong>{{ $indent->fromStore->department_name ?? $indent->department->department_name ?? '--' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">To Store</small>
                        <strong>{{ $indent->toStore->department_name ?? '--' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Requested By</small>
                        <strong>{{ $indent->requestedBy->name ?? '--' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Priority</small>
                        <span class="badge bg-label-{{ $indent->priority === 'Urgent' || $indent->is_urgent ? 'danger' : ($indent->priority === 'High' ? 'warning' : 'info') }}">
                            {{ $indent->priority }}
                        </span>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Is Urgent</small>
                        <strong>{{ $indent->is_urgent ? 'Yes' : 'No' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Tag</small>
                        <strong>{{ $indent->tag ?: '--' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-label-{{ $displayStatus === 'Approved' ? 'success' : ($displayStatus === 'Rejected' ? 'danger' : 'warning') }}">
                            {{ $displayStatus }}
                        </span>
                    </div>
                    @if ($latestDecision && in_array($displayStatus, ['Approved', 'Rejected'], true))
                        <div class="col-12">
                            <small class="text-muted d-block">{{ $displayStatus }} Remarks</small>
                            <strong>{{ $latestDecision->remarks ?: '--' }}</strong>
                            @if ($latestDecision->approver?->name)
                                <div class="small text-muted mt-1">
                                    By {{ $latestDecision->approver->name }}
                                    @if ($latestDecision->action_date)
                                        · {{ $latestDecision->action_date->format('d M Y h:i A') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                    @if ($indent->statusEnum()?->isPendingApproval() && ($awaitingAssigneeLabel ?? $indent->assigned_approver_id))
                        <div class="col-md-4">
                            <small class="text-muted d-block">Assigned Approver</small>
                            <strong>{{ $awaitingAssigneeLabel ?? ($indent->assignedApprover->name ?? '--') }}</strong>
                        </div>
                    @endif
                    @if ($indent->approvalTemplate)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Approval Template</small>
                            <strong>{{ $indent->approvalTemplate->name }}</strong>
                        </div>
                    @endif
                    @if ($indent->current_sequence)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Current Approval Level</small>
                            <strong>Level {{ $indent->current_sequence }}</strong>
                        </div>
                    @endif
                    <div class="col-12">
                        <small class="text-muted d-block">Purpose</small>
                        <strong>{{ $indent->purpose ?: '--' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Items</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                            <th>Asset Name</th>
                            <th>Asset Type</th>
                            <th>Asset Category</th>
                            <th>Qty</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($indent->items as $item)
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->composition ?: ($item->resolvedItemType()->value === 'consumable' ? 'Consumable' : 'Asset') }}</td>
                                <td>{{ $item->category->category_name ?? '--' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No items</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Approval History</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Approval Level</th>
                            <th>Action</th>
                            <th>Approver Name</th>
                            <th>Remarks</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $latestSubmit = $indent->approvalHistories->where('action', 'Submitted')->last();
                            $filteredHistories = $latestSubmit 
                                ? $indent->approvalHistories->where('id', '>=', $latestSubmit->id)
                                : $indent->approvalHistories;
                        @endphp
                        @forelse ($filteredHistories as $history)
                            <tr>
                                <td>{{ $history->sequence ? 'Level '.$history->sequence : 'Submit' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $history->action === 'Approved' || $history->action === 'Submitted' ? 'success' : ($history->action === 'Sent Back' ? 'warning' : 'danger') }}">
                                        {{ $history->action }}
                                    </span>
                                </td>
                                <td>{{ $history->approver->name ?? '--' }}</td>
                                <td>{{ $history->remarks ?: '--' }}</td>
                                <td>{{ $history->action_date?->format('d M Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No approval history yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Approval Progress</h5></div>
            <div class="card-body">
                <ul class="timeline mb-0 list-unstyled">
                    @foreach ($timeline as $row)
                        @php
                            $icon = match ($row['state']) {
                                'done' => 'bx-check-circle text-success',
                                'current' => 'bx-radio-circle-marked text-primary',
                                'rejected' => 'bx-x-circle text-danger',
                                'sent_back' => 'bx-undo text-warning',
                                default => 'bx-circle text-muted',
                            };
                        @endphp
                        <li class="d-flex align-items-start mb-3">
                            <i class="bx {{ $icon }} me-2 mt-1" style="font-size:1.25rem;"></i>
                            <div>
                                <div class="fw-semibold">
                                    @if ($row['sequence'])
                                        Step {{ $row['sequence'] }} —
                                    @endif
                                    {{ $row['label'] }}
                                </div>
                                @if ($row['permission'])
                                    <div class="small text-muted"><code>{{ $row['permission'] }}</code></div>
                                @endif
                                @if ($row['actor'])
                                    <div class="small">{{ $row['actor'] }}</div>
                                @endif
                                @if ($row['acted_at'])
                                    <div class="small text-muted">{{ $row['acted_at']->format('d M Y h:i A') }}</div>
                                @endif
                                @if ($row['remarks'])
                                    <div class="small text-muted">{{ $row['remarks'] }}</div>
                                @endif
                                @if ($row['state'] === 'current')
                                    <span class="badge bg-label-primary mt-1">Pending</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if (! empty($canActOnCurrentStep) && $indent->statusEnum()?->isPendingApproval())
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Approval Action — Step {{ $indent->current_sequence }}</h5>
                </div>
                <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3">
                            You can act on step {{ $indent->current_sequence }}{{ ! empty($awaitingAssigneeLabel) ? ' ('.$awaitingAssigneeLabel.')' : '' }}.
                        </div>
                    <div class="d-flex flex-column gap-2">
                        <form method="POST" action="{{ route('indents.approve', $indent) }}" id="approve-form">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">Approve</button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('indents.reject', $indent) }}" id="reject-form">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Indent</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" for="reject-comments">Remarks <span class="text-danger">*</span></label>
                                    <textarea id="reject-comments" name="comments" class="form-control" rows="3" placeholder="Please enter a reason for rejection" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Confirm Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif ($indent->statusEnum()?->isPendingApproval())
            <div class="card">
                <div class="card-body">
                    @if (! empty($isRequester) && empty($canActOnCurrentStep))
                        <div class="alert alert-warning mb-0">
                            You created this indent. You cannot approve it at the current step.
                            It is waiting for
                            <strong>{{ $awaitingAssigneeLabel ?? ($indent->assignedApprover->name ?? 'the assigned approver') }}</strong>
                            (step {{ $indent->current_sequence }}).
                        </div>
                    @else
                        <div class="text-muted small">
                            Waiting for
                            <strong>{{ $awaitingAssigneeLabel ?? ($indent->assignedApprover->name ?? 'assigned approver') }}</strong>
                            to act on sequence {{ $indent->current_sequence }}.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const approveForm = document.getElementById('approve-form');
    if (approveForm) {
        approveForm.addEventListener('submit', function (e) {
            const submitBtn = approveForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Approving...';
            }
        });
    }

    const rejectForm = document.getElementById('reject-form');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function (e) {
            const commentsInput = document.getElementById('reject-comments');
            const value = commentsInput ? commentsInput.value.trim() : '';
            if (!value) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Remarks required', 'Please enter a reason.', 'warning');
                } else {
                    alert('Please enter a reason.');
                }
                return;
            }
            const submitBtn = rejectForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Rejecting...';
            }
        });
    }
});
</script>
@endsection
