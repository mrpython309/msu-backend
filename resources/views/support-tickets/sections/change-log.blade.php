 <!-- Status Change Logs -->

<div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title m-0">Status Change Logs</h5>
      </div>
      <div class="card-body pt-1">
        <ul class="timeline pb-0 mb-0">

          @forelse($supportTicket->activities as $activity)
<li class="timeline-item timeline-item-transparent border-primary mb-2">
    <span class="timeline-point timeline-point-primary"></span>

    <div class="timeline-event">
        <div class="timeline-header">
            <h6 class="mb-0">
                {{ ucfirst(str_replace('_',' ', $activity->event)) }}
            </h6>
            <small class="text-muted">
                {{ $activity->created_at->diffForHumans() }}
            </small>
        </div>

        <p class="mt-2">
            {{ $activity->user->name ?? 'System' }}
        </p>

        @if($activity->event === 'status_changed')

        @php
        $statusColors = [
            'open' => 'bg-label-primary',
            'accepted' => 'bg-label-info',
            'work_in_progress' => 'bg-label-warning',
            'hold' => 'bg-label-secondary',
            'in_purchase' => 'bg-label-dark',
            'completed' => 'bg-label-success',
            'closed' => 'bg-label-danger'
        ];

        $oldStatus = strtolower($activity->meta['old'] ?? '');
        $newStatus = strtolower($activity->meta['new'] ?? '');

        $oldClass = $statusColors[$oldStatus] ?? 'bg-label-secondary';
        $newClass = $statusColors[$newStatus] ?? 'bg-label-secondary';
        @endphp

        <span class="badge {{ $oldClass }}">
            {{ ucfirst(str_replace('_',' ', $oldStatus)) }}
        </span>

        <i class="bx bx-right-arrow-alt mx-1"></i>

        <span class="badge {{ $newClass }}">
            {{ ucfirst(str_replace('_',' ', $newStatus)) }}
        </span>

        @endif

        @if($activity->event === 'updated')
    <span class="badge bg-label-warning">Ticket Updated</span>

    <ul class="mt-2 mb-0">
        @foreach($activity->meta['changes'] as $field => $change)
            <li>
                <strong>{{ ucfirst(str_replace('_',' ', $field)) }}</strong> :
                {{ $change['old'] ?? '-' }}
                <i class="bx bx-right-arrow-alt mx-1"></i>
                {{ $change['new'] ?? '-' }}
            </li>
        @endforeach
    </ul>
@endif

        @if($activity->event === 'assigned')
            <span class="badge bg-label-primary text-wrap lh-base">
                Collaborate to {{ $activity->meta['assigned_to'] }}
            </span>
        @endif

        @if($activity->event === 'exited' && !empty($activity->meta['reason']))
            <div class="mt-2 text-muted text-wrap lh-base">
                <strong>Reason:</strong> {{ $activity->meta['reason'] }}
            </div>
        @endif
    </div>
</li>

@empty

  <li class="timeline-item timeline-item-transparent border-primary">
               No Logs Found
            </li>
@endforelse

         
          

          
        </ul>
      </div>
    </div>


      <!-- /Status Change Logs -->