<div class="modal fade" id="assignmentLogs{{$assignment->id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-history me-2"></i> Assignment Logs
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        @if($assignment->assigned_at && $assignment->assignedBy)
        <!-- ASSIGNED -->
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h6 class="text-primary mb-2">Assigned</h6>
            <div class="row small">
              <div class="col-md-4"><strong>By:</strong> {{ optional($assignment->assignedBy)->name ?? '-' }}</div>
              <div class="col-md-4"><strong>At:</strong> {{ $assignment->assigned_at?->format('d M Y, h:i A') ?? '-' }}</div>
              <div class="col-12 mt-2 text-muted">
                <strong>Notes:</strong> {{ $assignment->assigned_notes ?? '-' }}
              </div>
            </div>
          </div>
        </div>
        @endif

        @if($assignment->acceptedBy && $assignment->accepted_at)

        <!-- ACCEPTED -->
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h6 class="text-success mb-2">Accepted</h6>
            <div class="row small">
              <div class="col-md-4"><strong>By:</strong> {{ optional($assignment->acceptedBy)->name ?? '-' }}</div>
              <div class="col-md-4"><strong>At:</strong> {{ $assignment->accepted_at?->format('d M Y, h:i A') ?? '-' }}</div>
              <div class="col-12 mt-2 text-muted">
                <strong>Notes:</strong> {{ $assignment->accepted_notes ?? '-' }}
              </div>
            </div>
          </div>
        </div>

        @endif

        @if($assignment->rejectedBy && $assignment->rejected_at)

        <!-- REJECTED -->
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h6 class="text-danger mb-2">Rejected</h6>
            <div class="row small">
              <div class="col-md-4"><strong>By:</strong> {{ optional($assignment->rejectedBy)->name ?? '-' }}</div>
              <div class="col-md-4"><strong>At:</strong> {{ $assignment->rejected_at?->format('d M Y, h:i A') ?? '-' }}</div>
              <div class="col-12 mt-2 text-muted">
                <strong>Notes:</strong> {{ $assignment->rejected_notes ?? '-' }}
              </div>
            </div>
          </div>
        </div>

        @endif

         @if($assignment->returnedBy && $assignment->returned_at)

        <!-- RETURNED -->
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h6 class="text-warning mb-2">Returned</h6>
            <div class="row small">
              <div class="col-md-4"><strong>By:</strong> {{ optional($assignment->returnedBy)->name ?? '-' }}</div>
              <div class="col-md-4"><strong>At:</strong> {{ $assignment->returned_at?->format('d M Y, h:i A') ?? '-' }}</div>
              <div class="col-12 mt-2 text-muted">
                <strong>Notes:</strong> {{ $assignment->return_notes ?? '-' }}
              </div>
            </div>
          </div>
        </div>

        @endif

         @if($assignment->returnAcceptedBy && $assignment->return_accepted_at)

        <!-- RETURN ACCEPTED -->
        <div class="card mb-0 shadow-sm">
          <div class="card-body">
            <h6 class="text-info mb-2">Return Accepted</h6>
            <div class="row small">
              <div class="col-md-4"><strong>By:</strong> {{ optional($assignment->returnAcceptedBy)->name ?? '-' }}</div>
              <div class="col-md-4"><strong>At:</strong> {{ $assignment->return_accepted_at?->format('d M Y, h:i A') ?? '-' }}</div>
              <div class="col-12 mt-2 text-muted">
                <strong>Notes:</strong> {{ $assignment->return_accepted_notes ?? '-' }}
              </div>
            </div>
          </div>
        </div>

        @endif

      </div>

      <div class="modal-footer">
        <button class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
