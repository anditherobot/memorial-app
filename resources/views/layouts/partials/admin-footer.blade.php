<footer class="bg-gray-100 text-gray-600 py-4 px-6 mt-auto">
    <div class="max-w-7xl mx-auto flex justify-between items-center text-sm">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Memorial') }}. All rights reserved.</p>
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" class="hover:underline">Back to Site</a>
            <span>Version 0.5.0</span>
        </div>
    </div>
</footer>
