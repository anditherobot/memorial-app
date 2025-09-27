@extends('layouts.app')

@section('content')
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Gallery</h1>

    @if($images->count() > 0)
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        @foreach($images as $img)
          @php $thumb = optional($img->derivatives()->where('type','thumbnail')->first()); @endphp
          <div class="task-card-ui border rounded-lg overflow-hidden bg-white">
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img->storage_path) }}" class="glightbox" data-gallery="memorial">
              @if($thumb)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($thumb->storage_path) }}" alt="{{ $img->original_filename }}" class="w-full h-44 object-cover" />
              @else
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img->storage_path) }}" alt="{{ $img->original_filename }}" class="w-full h-44 object-cover" />
              @endif
            </a>
            <div class="p-2 text-xs flex items-center justify-between">
              <span class="truncate text-gray-700">{{ $img->original_filename }}</span>
              @if($img->width && $img->height)
                <span class="chip bg-gray-100">{{ $img->width }}×{{ $img->height }}</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      <div class="mt-4">{{ $images->links() }}</div>
    @else
      <p class="text-gray-600 mb-3">Sample gallery (no uploads yet).</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        @foreach(($samples ?? []) as $i => $path)
          <div class="task-card-ui border rounded-lg overflow-hidden bg-white">
            <a href="{{ asset($path) }}" class="glightbox" data-gallery="memorial" aria-label="Open sample image {{ $i+1 }}">
              <img src="{{ asset($path) }}" alt="Sample image {{ $i+1 }}" class="w-full h-44 object-cover bg-white" />
            </a>
            <div class="p-2 text-xs flex items-center justify-between">
              <span class="truncate text-gray-700">Sample {{ $i+1 }}</span>
              <span class="chip bg-gray-100">SVG</span>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
      }
    });
  </script>
@endsection
