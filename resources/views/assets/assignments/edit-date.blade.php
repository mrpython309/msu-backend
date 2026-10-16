@php
    $dateValue = (string) old('assignment_id') === (string) $assignment->id
        ? old('assign_date', $assignment->assign_date?->format('Y-m-d'))
        : $assignment->assign_date?->format('Y-m-d');
    $showDateError = isset($errors) && $errors->has('assign_date') && (string) old('assignment_id') === (string) $assignment->id;
@endphp

<div class="modal fade" id="editAssignDate{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('asset.assignment.assign-date', $assignment) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

        <div class="modal-header">
          <h5 class="modal-title">Edit Assigned Date</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="text-muted mb-4">Only the assigned date can be changed. Company, branch, custodian, and status stay the same.</p>
          <label class="form-label" for="assign_date_{{ $assignment->id }}">Assigned Date <span class="text-danger">*</span></label>
          <input
            type="date"
            id="assign_date_{{ $assignment->id }}"
            name="assign_date"
            class="form-control @if($showDateError) is-invalid @endif"
            value="{{ $dateValue }}"
            required
          >
          @if($showDateError)
            <div class="invalid-feedback">{{ $errors->first('assign_date') }}</div>
          @endif
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if($showDateError)
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('editAssignDate{{ $assignment->id }}');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
});
</script>
@endif
