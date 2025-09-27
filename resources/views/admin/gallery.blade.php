@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Gallery Management</span>
  </li>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Gallery Management</h1>
      <p class="text-sm text-gray-500">Upload and manage memorial photos</p>
    </div>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.gallery.upload') }}" enctype="multipart/form-data" class="p-6 bg-white border rounded-lg shadow-sm space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Memorial Photo</label>
        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
          <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
              <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
              <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <span>Upload a file</span>
                <input id="file-upload" name="file" type="file" accept="image/*" required class="sr-only" onchange="previewImage(this)" />
              </label>
              <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
          </div>
        </div>
        @error('file')
          <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
        @enderror
      </div>

      <!-- Image Preview -->
      <div id="image-preview" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
        <div class="flex items-start space-x-4">
          <img id="preview-image" class="h-32 w-32 object-cover rounded-lg border" />
          <div class="flex-1">
            <div id="image-info" class="text-sm text-gray-600 space-y-1"></div>
            <button type="button" onclick="clearPreview()" class="mt-2 text-sm text-red-600 hover:text-red-500">Remove</button>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input id="is_public" name="is_public" type="checkbox" checked class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
          <label for="is_public" class="ml-2 block text-sm text-gray-900">Make photo public (visible in gallery)</label>
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
          Upload Photo
        </button>
      </div>
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

    function previewImage(input) {
      const file = input.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('image-preview');
          const previewImage = document.getElementById('preview-image');
          const imageInfo = document.getElementById('image-info');

          previewImage.src = e.target.result;
          preview.classList.remove('hidden');

          // Display file information
          const fileSize = (file.size / 1024 / 1024).toFixed(2);
          imageInfo.innerHTML = `
            <div><strong>File:</strong> ${file.name}</div>
            <div><strong>Size:</strong> ${fileSize} MB</div>
            <div><strong>Type:</strong> ${file.type}</div>
          `;
        };
        reader.readAsDataURL(file);
      }
    }

    function clearPreview() {
      const preview = document.getElementById('image-preview');
      const fileInput = document.getElementById('file-upload');
      const previewImage = document.getElementById('preview-image');
      const imageInfo = document.getElementById('image-info');

      preview.classList.add('hidden');
      fileInput.value = '';
      previewImage.src = '';
      imageInfo.innerHTML = '';
    }

    // Enhanced drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
      const dropZone = document.querySelector('.border-dashed');
      const fileInput = document.getElementById('file-upload');

      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
      });

      ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
      });

      dropZone.addEventListener('drop', handleDrop, false);

      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }

      function highlight(e) {
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
      }

      function unhighlight(e) {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
      }

      function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
          fileInput.files = files;
          previewImage(fileInput);
        }
      }
    });
  </script>
@endsection
