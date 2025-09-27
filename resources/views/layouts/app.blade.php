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
    <header class="px-4 py-3 border-b bg-white" x-data="{ mobileMenuOpen: false }">
      <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between">
          <a href="/" class="font-semibold">{{ config('app.name','Memorial') }}</a>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex items-center gap-4 text-sm">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <a href="{{ route('gallery.index') }}" class="hover:underline">Gallery</a>
            <a href="{{ route('wishes.index') }}" class="hover:underline">Wishes</a>
            <a href="{{ route('updates.index') }}" class="hover:underline">Updates</a>
            <a href="{{ route('upload.show') }}" class="hover:underline flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Photo
            </a>
            @auth
              <a href="{{ route('admin.dashboard') }}" class="hover:underline">Admin</a>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="hover:underline">Logout</button>
              </form>
            @else
              <a href="{{ route('login') }}" class="hover:underline">Login</a>
            @endauth
          </nav>

          <!-- Mobile Menu Button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
            aria-label="Toggle mobile menu"
          >
            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <nav
          x-show="mobileMenuOpen"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 transform scale-95"
          x-transition:enter-end="opacity-100 transform scale-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 transform scale-100"
          x-transition:leave-end="opacity-0 transform scale-95"
          class="md:hidden mt-4 pb-4 border-t"
          @click.away="mobileMenuOpen = false"
        >
          <div class="flex flex-col space-y-3 pt-4">
            <a href="{{ route('home') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Home</a>
            <a href="{{ route('gallery.index') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Gallery</a>
            <a href="{{ route('wishes.index') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Wishes</a>
            <a href="{{ route('updates.index') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Updates</a>
            <a href="{{ route('upload.show') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2 flex items-center" @click="mobileMenuOpen = false">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Photo
            </a>
            @auth
              <a href="{{ route('admin.dashboard') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Admin</a>
              <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button class="w-full text-left py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Logout</button>
              </form>
            @else
              <a href="{{ route('login') }}" class="block py-2 text-sm hover:bg-gray-50 rounded px-2" @click="mobileMenuOpen = false">Login</a>
            @endauth
          </div>
        </nav>
      </div>
    </header>
    <main class="max-w-5xl mx-auto p-4">
      {{ $slot ?? '' }}
      @yield('content')
    </main>
    <footer class="max-w-5xl mx-auto p-4 text-sm text-gray-500">&copy; {{ date('Y') }}</footer>

    <!-- HTMX, Alpine, GLightbox (CDN) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Preline is bundled via Vite in resources/js/app.js -->
  </body>
</html>
