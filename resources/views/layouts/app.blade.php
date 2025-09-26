<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Memorial')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <header class="border-b bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-lg font-semibold">In Loving Memory</a>
            <nav class="text-sm text-gray-600">
                <a href="/" class="hover:text-gray-900">Home</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-xs text-gray-500">
            <p>&copy; {{ date('Y') }} Memorial Site</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>

