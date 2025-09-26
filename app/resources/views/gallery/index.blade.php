@extends('layouts.app')

@section('content')
  <div class="max-w-5xl mx-auto">
    <h1 class="text-xl font-semibold mb-4">Gallery</h1>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="gallery-grid">
      @foreach($images as $img)
        @php $thumb = optional($img->derivatives()->where('type','thumbnail')->first()); @endphp
        <a href="{{ \Illuminate\Support\Facades\Storage::url($img->storage_path) }}" class="glightbox" data-gallery="memorial">
          @if($thumb)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb->storage_path) }}" alt="{{ $img->original_filename }}" class="w-full h-40 object-cover rounded" />
          @else
            <div class="w-full h-40 bg-gray-200 rounded grid place-items-center text-gray-500">Image</div>
          @endif
        </a>
      @endforeach
    </div>
    <div class="mt-4">{{ $images->links() }}</div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
      }
    });
  </script>
@endsection
