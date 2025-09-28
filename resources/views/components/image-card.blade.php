<div class="task-card-ui bg-white border rounded-lg overflow-hidden">
    <a href="{{ $imageUrl }}" class="glightbox" data-gallery="admin">
        @if($thumbnailUrl)
            <img src="{{ $thumbnailUrl }}" alt="{{ $altText }}" class="w-full h-44 object-cover" />
        @else
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-44 object-cover" />
        @endif
    </a>
    <div class="p-2 text-xs text-gray-700 flex items-center justify-between">
        <span class="truncate">{{ $filename }}</span>
        <span class="chip bg-gray-100">{{ $dimensions }}</span>
    </div>
    <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm('Delete this image?')" class="p-2">
        @csrf
        @method('DELETE')
        <button class="w-full px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 rounded">Delete</button>
    </form>
</div>
