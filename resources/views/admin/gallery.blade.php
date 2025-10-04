@extends('layouts.admin')



@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <x-admin-page-header
        title="Gallery Management"
        :breadcrumbs="[
            ['title' => 'Gallery Management']
        ]"
    />

    <!-- Toast Notifications -->
    @if(session('status'))
      <x-ui.alert id="successToast" variant="success" style="solid" class="fixed top-4 right-4 z-50 animate-fade-in shadow-lg">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        {{ session('status') }}
      </x-ui.alert>
    @endif

    @if(session('warning'))
      <x-ui.alert id="warningToast" variant="warning" style="solid" class="fixed top-4 right-4 z-50 animate-fade-in shadow-lg">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        {{ session('warning') }}
      </x-ui.alert>
    @endif

    @if(session('error'))
      <x-ui.alert id="errorToast" variant="danger" style="solid" class="fixed top-4 right-4 z-50 animate-fade-in shadow-lg">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        {{ session('error') }}
      </x-ui.alert>
    @endif

    <x-upload-form :action="route('admin.gallery.upload')" />

    @if($photos->isNotEmpty())
      <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-3">User Photos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          @foreach($photos as $photo)
            {{-- Note: Photo model doesn't match Media model structure, keeping old code for now --}}
            <x-ui.card padding="p-0">
              <a href="{{ $photo->display_url }}" class="glightbox" data-gallery="photos">
                <img src="{{ $photo->thumbnail_url }}"
                     alt="{{ $photo->uuid }}"
                     loading="lazy"
                     onerror="this.src='{{ asset('images/placeholder.svg') }}'"
                     class="w-full h-44 object-cover" />
              </a>
              <div class="p-2 text-xs text-gray-700 flex items-center justify-between">
                <span class="truncate">{{ $photo->uuid }}</span>
                <x-ui.badge variant="neutral">{{ $photo->width }}×{{ $photo->height }}</x-ui.badge>
              </div>
            </x-ui.card>
          @endforeach
        </div>
      </div>
    @endif

    <div>
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-900">Media Gallery</h2>

        <!-- Bulk Actions -->
        <div class="flex items-center gap-3">
          <button id="selectAllBtn" type="button" onclick="toggleSelectAll()" class="text-sm text-gray-600 hover:text-gray-900">
            Select All
          </button>
          <x-ui.button id="optimizeBtn" type="button" onclick="optimizeSelected()" variant="info" size="sm" class="disabled:bg-gray-300 disabled:cursor-not-allowed">
            <span id="optimizeBtnText">Optimize Selected</span>
          </x-ui.button>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="mediaGrid">
        @forelse($images as $img)
          <x-image-thumbnail :media="$img" gallery="admin-media" :selectable="true">
            {{-- Add delete button in slot --}}
            <div class="p-2 border-t">
              <form method="POST" action="{{ route('admin.media.destroy', $img) }}" onsubmit="return confirm('Delete this image?')">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" size="sm" class="w-full">Delete</x-ui.button>
              </form>
            </div>
          </x-image-thumbnail>
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
      // Initialize GLightbox
      if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
      }

      // Auto-hide toast notifications after 5 seconds
      ['successToast', 'warningToast', 'errorToast'].forEach(id => {
        const toast = document.getElementById(id);
        if (toast) {
          setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
          }, 5000);
        }
      });

      // Handle checkbox changes to enable/disable optimize button
      document.addEventListener('change', (e) => {
        if (e.target.classList.contains('media-select-checkbox')) {
          updateOptimizeButton();
        }
      });
    });

    function toggleSelectAll() {
      const checkboxes = document.querySelectorAll('.media-select-checkbox');
      const allChecked = Array.from(checkboxes).every(cb => cb.checked);

      checkboxes.forEach(cb => cb.checked = !allChecked);

      document.getElementById('selectAllBtn').textContent = allChecked ? 'Select All' : 'Deselect All';
      updateOptimizeButton();
    }

    function updateOptimizeButton() {
      const selected = document.querySelectorAll('.media-select-checkbox:checked');
      const optimizeBtn = document.getElementById('optimizeBtn');
      const optimizeBtnText = document.getElementById('optimizeBtnText');

      if (selected.length === 0) {
        optimizeBtn.disabled = true;
        optimizeBtnText.textContent = 'Optimize Selected';
      } else {
        // Count how many need optimization (don't have web-optimized)
        const needsOptimization = Array.from(selected).filter(cb => cb.dataset.hasOptimization === 'false').length;

        optimizeBtn.disabled = false;
        optimizeBtnText.textContent = `Optimize ${selected.length} Selected${needsOptimization < selected.length ? ' (' + needsOptimization + ' need optimization)' : ''}`;
      }
    }

    async function optimizeSelected() {
      const selected = document.querySelectorAll('.media-select-checkbox:checked');
      if (selected.length === 0) return;

      const mediaIds = Array.from(selected).map(cb => cb.dataset.mediaId);
      const optimizeBtn = document.getElementById('optimizeBtn');
      const optimizeBtnText = document.getElementById('optimizeBtnText');

      // Disable button and show loading
      optimizeBtn.disabled = true;
      optimizeBtnText.textContent = 'Optimizing...';

      try {
        const response = await fetch('{{ route("admin.gallery.optimize") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ media_ids: mediaIds })
        });

        if (response.ok) {
          // Reload page to show updated badges and toast message
          window.location.reload();
        } else {
          const data = await response.json();
          alert('Optimization failed: ' + (data.message || 'Unknown error'));
          optimizeBtn.disabled = false;
          updateOptimizeButton();
        }
      } catch (error) {
        alert('Optimization failed: ' + error.message);
        optimizeBtn.disabled = false;
        updateOptimizeButton();
      }
    }
  </script>

  <style>
    @keyframes fade-in {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .animate-fade-in {
      animation: fade-in 0.3s ease-out;
    }
  </style>
@endsection
