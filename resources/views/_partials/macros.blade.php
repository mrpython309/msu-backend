@php
  $width = $width ?? '32';
  $height = $height ?? 'auto';
  $class = $class ?? '';
@endphp

<img src="{{ asset('assets/img/icons/site_logo.png') }}" alt="{{ config('variables.templateName', 'Logo') }}"
  width="{{ $width }}" height="{{ $height }}" class="img-fluid {{ $class }}"
  style="max-height: 38px; width: auto; object-fit: contain;" />
