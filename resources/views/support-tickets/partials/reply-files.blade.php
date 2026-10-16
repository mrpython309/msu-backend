@php
    $attachmentFilenames = $stCommand->attachment_filenames;
    $attachmentUrls = $stCommand->attachment_urls;
@endphp

@foreach($attachmentFilenames as $index => $filename)
    @php
        $fileUrl = $attachmentUrls[$index] ?? '';
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $imageExtensions = ['png', 'jpe', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tif', 'svg', 'svgz', 'webp'];
    @endphp

    <div class="mb-2">
        @if(in_array($fileExt, $imageExtensions, true))
            <a href="{{ $fileUrl }}" data-lightbox="reply-gallery-{{ $stCommand->id }}">
                <img src="{{ $fileUrl }}" class="d-block w-px-100 h-px-100 rounded mb-2" alt="Reply attachment">
            </a>
        @endif
        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-primary">
            Download Attachment{{ count($attachmentFilenames) > 1 ? ' '.$loop->iteration : '' }}
        </a>
    </div>
@endforeach
