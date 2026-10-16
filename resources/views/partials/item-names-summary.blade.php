@php
    $names = collect($names ?? [])->filter(fn ($name) => filled($name))->values();
@endphp
@if ($names->isEmpty())
    --
@elseif ($names->count() === 1)
    <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $names->first() }}">{{ $names->first() }}</span>
@else
    <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $names->implode(', ') }}">{{ $names->first() }},...</span>
@endif
