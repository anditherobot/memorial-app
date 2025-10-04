<nav class="bg-gray-100 border-b" aria-label="Breadcrumb">
  <div class="max-w-5xl mx-auto px-4 py-3">
    <ol class="flex items-center space-x-2 text-sm">
      <li>
        <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
          </svg>
        </a>
      </li>
      {{ $slot }}
    </ol>
  </div>
</nav>

