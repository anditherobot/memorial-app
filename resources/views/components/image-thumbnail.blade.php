@props([
    'media',           // Media model instance
    'linkUrl' => null, // Optional: custom link URL (defaults to full image)
    'gallery' => 'gallery', // Gallery group for lightbox
    'showInfo' => true, // Show filename and dimensions
    'selectable' => false, // Show checkbox for selection
])

@php
    // Get derivatives (thumbnail for grid, web-optimized for full view)
    $thumb = $media->derivatives()->where('type','thumbnail')->first();
    $thumbPath = $thumb?->storage_path ?? null;

    $webOptimized = $media->derivatives()->where('type','web-optimized')->first();
    $webOptimizedPath = $webOptimized?->storage_path ?? null;

    // Use thumbnail if it exists and has a valid path, otherwise use original
    $thumbnailUrl = ($thumbPath && strlen($thumbPath) > 0)
        ? Storage::disk('public')->url($thumbPath)
        : Storage::disk('public')->url($media->storage_path);

    // Use web-optimized for full image if available, otherwise original
    $fullImageUrl = $linkUrl ?? (($webOptimizedPath && strlen($webOptimizedPath) > 0)
        ? Storage::disk('public')->url($webOptimizedPath)
        : Storage::disk('public')->url($media->storage_path));

    $placeholderUrl = asset('images/placeholder.svg');
@endphp

<div class="task-card-ui border rounded-lg overflow-hidden bg-white relative">
    @if($selectable)
        <div class="absolute top-2 left-2 z-10">
            <input type="checkbox"
                   class="media-select-checkbox w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                   data-media-id="{{ $media->id }}"
                   data-has-optimization="{{ $media->derivatives()->where('type', 'web-optimized')->exists() ? 'true' : 'false' }}" />
        </div>
    @endif
    <a href="{{ $fullImageUrl }}" class="glightbox" data-gallery="{{ $gallery }}">
        <img src="{{ $thumbnailUrl }}"
             alt="{{ $media->original_filename }}"
             loading="lazy"
             onerror="this.src='{{ $placeholderUrl }}'; this.onerror=null;"
             class="w-full h-44 object-cover" />
    </a>

    @if($showInfo)
        <div class="p-2 text-xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="truncate text-gray-700" title="{{ $media->original_filename }}">
                    {{ $media->original_filename }}
                </span>
                @if($media->width && $media->height)
                    <span class="chip bg-gray-100">{{ $media->width }}×{{ $media->height }}</span>
                @endif
            </div>
            <div class="flex items-center justify-between text-gray-500">
                <span>{{ number_format($media->size_bytes / 1024 / 1024, 2) }} MB</span>
                @php
                    $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();
                @endphp
                @if($webOptimized)
                    <span class="text-green-600 font-medium">✓ Optimized</span>
                @else
                    <span class="text-yellow-600 font-medium">✗ Not Optimized</span>
                @endif
            </div>
        </div>
    @endif

    {{ $slot }}
</div>