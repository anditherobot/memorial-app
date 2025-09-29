<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Memorial') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="px-4 py-4 border-b bg-white shadow-sm" x-data="{ mobileMenuOpen: false }">
      <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between">
          <a href="/" class="text-xl font-semibold text-gray-900">{{ config('app.name','Memorial') }}</a>

          <!-- Desktop Navigation -->
          <nav class="hidden lg:flex items-center gap-6 text-base">
            <a href="{{ route('home') }}" class="text-gray-700 hover:text-black transition-colors font-medium">Home</a>
            <a href="{{ route('gallery.index') }}" class="text-gray-700 hover:text-black transition-colors font-medium">Gallery</a>
            <a href="{{ route('wishes.index') }}" class="text-gray-700 hover:text-black transition-colors font-medium">Wishes</a>
            <a href="{{ route('updates.index') }}" class="text-gray-700 hover:text-black transition-colors font-medium">Updates</a>

            <div class="h-6 w-px bg-gray-300"></div>

            @auth
              <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 text-gray-700 hover:bg-gray-100 rounded-md transition-colors font-medium">
                Admin
              </a>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="px-4 py-1.5 bg-black text-white hover:bg-gray-800 rounded-md transition-colors font-medium">
                  Logout
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" class="px-4 py-1.5 bg-black text-white hover:bg-gray-800 rounded-md transition-colors font-medium">
                Login
              </a>
            @endauth
          </nav>

          <!-- Mobile: Main Links Always Visible -->
          <div class="flex lg:hidden items-center gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-gray-700 hover:text-black transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
              <span class="text-sm font-medium">Home</span>
            </a>
            <a href="{{ route('gallery.index') }}" class="flex items-center gap-1.5 text-gray-700 hover:text-black transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <span class="text-sm font-medium">Gallery</span>
            </a>
            <a href="{{ route('wishes.index') }}" class="flex items-center gap-1.5 text-gray-700 hover:text-black transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
              <span class="text-sm font-medium">Wishes</span>
            </a>

            <!-- Mobile Menu Button (for secondary items) -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
              aria-label="Toggle menu"
            >
              <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
              </svg>
              <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile: Collapsed Secondary Menu -->
        <nav
          x-show="mobileMenuOpen"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 transform scale-95"
          x-transition:enter-end="opacity-100 transform scale-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 transform scale-100"
          x-transition:leave-end="opacity-0 transform scale-95"
          class="lg:hidden mt-4 pb-4 border-t"
          @click.away="mobileMenuOpen = false"
        >
          <div class="flex flex-col space-y-2 pt-4">
            <a href="{{ route('updates.index') }}" class="block py-2.5 px-3 text-base text-gray-700 hover:bg-gray-50 rounded-md font-medium transition-colors" @click="mobileMenuOpen = false">
              Updates
            </a>
            <a href="{{ route('upload.show') }}" class="block py-2.5 px-3 text-base text-gray-700 hover:bg-gray-50 rounded-md font-medium transition-colors flex items-center" @click="mobileMenuOpen = false">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Photo
            </a>

            <div class="h-px bg-gray-200 my-2"></div>

            @auth
              <a href="{{ route('admin.dashboard') }}" class="block py-2.5 px-3 text-base text-gray-700 hover:bg-gray-50 rounded-md font-medium transition-colors" @click="mobileMenuOpen = false">
                Admin Dashboard
              </a>
              <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button class="w-full text-left py-2.5 px-3 text-base bg-black text-white hover:bg-gray-800 rounded-md font-medium transition-colors" @click="mobileMenuOpen = false">
                  Logout
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" class="block py-2.5 px-3 text-base bg-black text-white hover:bg-gray-800 rounded-md font-medium transition-colors text-center" @click="mobileMenuOpen = false">
                Login
              </a>
            @endauth
          </div>
        </nav>
      </div>
    </header>

    @hasSection('breadcrumbs')
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
            @yield('breadcrumbs')
          </ol>
        </div>
      </nav>
    @endif

    <main class="max-w-5xl mx-auto p-4">
      {{ $slot ?? '' }}
      @yield('content')
    </main>
    @include('layouts.partials.footer')

    <!-- HTMX, Alpine, GLightbox (CDN) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Preline is bundled via Vite in resources/js/app.js -->
  </body>
</html>
