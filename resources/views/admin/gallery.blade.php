@extends('layouts.app')

@section('content')
  <div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold">Manage Gallery</h1>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.gallery.upload') }}" enctype="multipart/form-data" class="p-4 bg-white border rounded space-y-3">
      @csrf
      <div>
        <label class="block text-sm font-medium">Upload image</label>
        <input type="file" name="file" accept="image/*" required class="mt-1 block w-full" />
        @error('file')
          <div class="text-red-600 text-sm">{{ $message }}</div>
        @enderror
      </div>
      <button class="px-4 py-2 bg-gray-900 text-white rounded">Upload</button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      @forelse($images as $img)
        @php $thumb = optional($img->derivatives()->where('type','thumbnail')->first()); @endphp
        <div class="task-card-ui bg-white border rounded-lg overflow-hidden">
          <a href="{{ Storage::disk('public')->url($img->storage_path) }}" class="glightbox" data-gallery="admin">
            @if($thumb)
              <img src="{{ Storage::disk('public')->url($thumb->storage_path) }}" alt="{{ $img->original_filename }}" class="w-full h-44 object-cover" />
            @else
              <img src="{{ Storage::disk('public')->url($img->storage_path) }}" alt="{{ $img->original_filename }}" class="w-full h-44 object-cover" />
            @endif
          </a>
          <div class="p-2 text-xs text-gray-700 flex items-center justify-between">
            <span class="truncate">{{ $img->original_filename }}</span>
            <span class="chip bg-gray-100">{{ $img->width }}×{{ $img->height }}</span>
          </div>
          <form method="POST" action="{{ route('admin.media.destroy', $img) }}" onsubmit="return confirm('Delete this image?')" class="p-2">
            @csrf
            @method('DELETE')
            <button class="w-full px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 rounded">Delete</button>
          </form>
        </div>
      @empty
        <div class="text-gray-500">No images yet.</div>
      @endforelse
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
