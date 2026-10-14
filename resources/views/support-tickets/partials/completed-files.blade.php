@php
    $completionFilenames = $supportTicket->completed_filenames;
    $completionUrls = $supportTicket->completed_file_urls;
@endphp

@foreach($completionFilenames as $index => $filename)
    @php
        $fileUrl = $completionUrls[$index] ?? '';
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $imageExtensions = ['png', 'jpe', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tif', 'svg', 'svgz', 'webp'];
    @endphp

    <div class="mb-2">
        @if(in_array($fileExt, $imageExtensions, true))
            <a href="{{ $fileUrl }}" data-lightbox="completion-gallery-{{ $supportTicket->id }}">
                <img src="{{ $fileUrl }}" class="d-block w-px-100 h-px-100 rounded mb-2" alt="Completion attachment">
            </a>
        @else
            <a href="{{ $fileUrl }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">View File {{ $loop->iteration }}</a>
        @endif
    </div>
@endforeach
