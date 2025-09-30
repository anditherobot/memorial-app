@props([
    'media',                    // Media model instance
    'showMetadata' => true,     // Show file metadata
    'showActions' => false,     // Show action buttons (edit, delete)
    'deleteUrl' => null,        // URL for delete action
    'editUrl' => null,          // URL for edit action
])

@php
    // Get image URL with fallback
    $imageUrl = Storage::disk('public')->url($media->storage_path);
    $placeholderUrl = asset('images/placeholder.svg');

    // Format file size
    $fileSize = $media->size_bytes ? number_format($media->size_bytes / 1024, 2) . ' KB' : 'Unknown';

    // Format uploaded date
    $uploadedAt = $media->created_at?->format('M d, Y g:i A');
@endphp

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <!-- Image -->
    <div class="relative bg-gray-100">
        <img src="{{ $imageUrl }}"
             alt="{{ $media->original_filename }}"
             onerror="this.src='{{ $placeholderUrl }}'; this.onerror=null;"
             class="w-full h-auto max-h-[600px] object-contain mx-auto" />
    </div>

    <!-- Metadata -->
    @if($showMetadata)
        <div class="p-4 space-y-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 truncate" title="{{ $media->original_filename }}">
                    {{ $media->original_filename }}
                </h3>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                @if($media->width && $media->height)
                    <div>
                        <span class="text-gray-500">Dimensions:</span>
                        <span class="text-gray-900 font-medium">{{ $media->width }}×{{ $media->height }}px</span>
                    </div>
                @endif

                @if($media->size_bytes)
                    <div>
                        <span class="text-gray-500">File Size:</span>
                        <span class="text-gray-900 font-medium">{{ $fileSize }}</span>
                    </div>
                @endif

                @if($media->mime_type)
                    <div>
                        <span class="text-gray-500">Type:</span>
                        <span class="text-gray-900 font-medium">{{ strtoupper(pathinfo($media->original_filename, PATHINFO_EXTENSION)) }}</span>
                    </div>
                @endif

                @if($uploadedAt)
                    <div>
                        <span class="text-gray-500">Uploaded:</span>
                        <span class="text-gray-900 font-medium">{{ $uploadedAt }}</span>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            @if($showActions && ($deleteUrl || $editUrl))
                <div class="flex gap-2 pt-3 border-t">
                    @if($editUrl)
                        <a href="{{ $editUrl }}"
                           class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition-colors text-center">
                            Edit
                        </a>
                    @endif

                    @if($deleteUrl)
                        <form method="POST" action="{{ $deleteUrl }}" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this image?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition-colors">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    @endif
</div>