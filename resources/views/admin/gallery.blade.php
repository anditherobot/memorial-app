@extends('layouts.admin')



@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <x-admin-page-header
        title="Gallery Management"
        :breadcrumbs="[
            ['title' => 'Gallery Management']
        ]"
    />

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <x-upload-form :action="route('admin.gallery.upload')" />

    @if($photos->isNotEmpty())
      <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-3">User Photos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          @foreach($photos as $photo)
            @php
              $thumbUrl = $photo->variants['thumbnail'] ?? $photo->display_path ?? $photo->original_path;
              $displayUrl = $photo->display_path ?? $photo->original_path;
            @endphp
            <div class="bg-white border rounded-lg overflow-hidden">
              <a href="{{ Storage::disk('public')->url($displayUrl) }}" class="glightbox" data-gallery="photos">
                <img src="{{ Storage::disk('public')->url($thumbUrl) }}" alt="{{ $photo->uuid }}" class="w-full h-44 object-cover" />
              </a>
              <div class="p-2 text-xs text-gray-700 flex items-center justify-between">
                <span class="truncate">{{ $photo->uuid }}</span>
                <span class="chip bg-gray-100">{{ $photo->width }}×{{ $photo->height }}</span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Media Gallery</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @forelse($images as $img)
          @php $thumb = optional($img->derivatives()->where('type','thumbnail')->first()); @endphp
          <x-image-card
              :image-url="Storage::disk('public')->url($img->storage_path)"
              :thumbnail-url="$thumb ? Storage::disk('public')->url($thumb->storage_path) : null"
              :alt-text="$img->original_filename"
              :filename="$img->original_filename"
              :dimensions="$img->width . '×' . $img->height"
              :delete-url="route('admin.media.destroy', $img)"
          />
        @empty
          <div class="text-gray-500">No images yet.</div>
        @endforelse
      </div>
    </div>

    <div>
      {{ $images->links() }}
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
      }
    });
  </script>
@endsection
