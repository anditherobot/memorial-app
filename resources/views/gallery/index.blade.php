@extends('layouts.app')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Gallery</span>
  </li>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Gallery</h1>

    @if($images->count() > 0)
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        @foreach($images as $img)
          <x-image-thumbnail :media="$img" gallery="memorial" />
        @endforeach
      </div>
      <div class="mt-4">{{ $images->links() }}</div>
    @else
      <p class="text-gray-600 mb-3">Sample gallery (no uploads yet).</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        @foreach(($samples ?? []) as $i => $path)
          <x-ui.card padding="p-0">
            <a href="{{ asset($path) }}" class="glightbox" data-gallery="memorial" aria-label="Open sample image {{ $i+1 }}">
              <img src="{{ asset($path) }}" alt="Sample image {{ $i+1 }}" class="w-full h-44 object-cover bg-white" />
            </a>
            <div class="p-2 text-xs flex items-center justify-between">
              <span class="truncate text-gray-700">Sample {{ $i+1 }}</span>
              <x-ui.badge variant="neutral">SVG</x-ui.badge>
            </div>
          </x-ui.card>
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
