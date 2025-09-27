<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Admin - {{ config('app.name', 'Memorial') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <!-- Fixed Header -->
    <header class="fixed inset-x-0 top-0 z-50 bg-white border-b shadow-sm h-14">
      <div class="h-14 flex items-center justify-between px-4">
        <!-- Mobile menu button -->
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 lg:hidden"
          aria-label="Mobile menu"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>

        <!-- Logo/Title -->
        <div class="flex items-center space-x-3">
          <div class="lg:hidden">
            <span class="font-semibold text-gray-900">{{ config('app.name', 'Memorial') }}</span>
            <span class="text-sm text-gray-500 ml-2">Admin</span>
          </div>
        </div>

        <!-- User Info & Logout -->
        <div class="flex items-center space-x-4">
          <div class="hidden sm:block text-sm text-gray-600">
            {{ auth()->user()->email }}
          </div>
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
              Logout
            </button>
          </form>
        </div>
      </div>
    </header>

    <!-- Sidebar Overlay (Mobile, below header) -->
    <div
      x-show="sidebarOpen"
      x-transition:enter="transition-opacity ease-linear duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition-opacity ease-linear duration-300"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-x-0 top-14 bottom-0 z-40 lg:hidden"
      @click="sidebarOpen = false"
    >
      <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
    </div>

    <!-- Sidebar -->
    <aside
      class="fixed top-14 left-0 z-40 h-[calc(100vh-3.5rem)] w-64 bg-white border-r transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between px-4 py-4 border-b">
        <div>
          <h1 class="font-semibold text-gray-900">{{ config('app.name', 'Memorial') }}</h1>
          <p class="text-sm text-gray-500">Administration</p>
        </div>
        <button
          @click="sidebarOpen = false"
          class="p-1 rounded-md text-gray-400 hover:text-gray-600 lg:hidden"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      @include('admin._navigation')
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-14">
      <!-- Breadcrumbs -->
      <div class="bg-white border-b px-4 py-3">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
              <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">
                Admin
              </a>
            </li>
            @yield('breadcrumbs')
          </ol>
        </nav>
      </div>

      <!-- Page Content -->
      <div class="p-4">
        @if(session('success'))
          <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-md">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
            {{ session('error') }}
          </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
      </div>
    </main>

    <!-- HTMX, Alpine, GLightbox (CDN) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Preline is bundled via Vite in resources/js/app.js -->
  </body>
</html>
