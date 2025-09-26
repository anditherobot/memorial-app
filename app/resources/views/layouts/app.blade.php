<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Memorial') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="px-4 py-3 border-b bg-white">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <a href="/" class="font-semibold">{{ config('app.name','Memorial') }}</a>
        <nav class="flex items-center gap-4 text-sm">
          <a href="{{ route('home') }}" class="hover:underline">Home</a>
          <a href="{{ route('gallery.index') }}" class="hover:underline">Gallery</a>
          <a href="{{ route('wishes.index') }}" class="hover:underline">Wishes</a>
          <a href="{{ route('updates.index') }}" class="hover:underline">Updates</a>
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
    <!-- Preline (uses Tailwind) -->
    <script type="module">import "preline";</script>
  </body>
</html>
