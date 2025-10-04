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
    <x-ui.site-header />

    @hasSection('breadcrumbs')
      <x-ui.breadcrumb>
        @yield('breadcrumbs')
      </x-ui.breadcrumb>
    @endif

    <main class="max-w-5xl mx-auto p-4">
      {{ $slot ?? '' }}
      @yield('content')
    </main>
    <x-ui.site-footer />

    <!-- HTMX, Alpine, GLightbox (CDN) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Preline is bundled via Vite in resources/js/app.js -->
  </body>
</html>
