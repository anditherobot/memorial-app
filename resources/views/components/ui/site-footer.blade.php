<footer class="bg-gray-800 text-white py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <h3 class="text-lg font-semibold">In Loving Memory</h3>
        <p class="text-gray-400">A tribute to a life well-lived.</p>
      </div>
      <div>
        <h3 class="text-lg font-semibold">Navigation</h3>
        <ul class="mt-4 space-y-2">
          <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white">Home</a></li>
          <li><a href="{{ route('gallery.index') }}" class="text-gray-400 hover:text-white">Gallery</a></li>
          <li><a href="{{ route('wishes.index') }}" class="text-gray-400 hover:text-white">Wishes</a></li>
          <li><a href="{{ route('updates.index') }}" class="text-gray-400 hover:text-white">Updates</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-lg font-semibold">Admin</h3>
        <ul class="mt-4 space-y-2">
          <li><a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-white">Admin Panel</a></li>
        </ul>
      </div>
    </div>
    <div class="mt-8 border-t border-gray-700 pt-8 flex flex-col md:flex-row justify-between items-center">
      <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ config('app.name','Memorial') }}. All rights reserved.</p>
      <p class="text-gray-400 text-sm mt-4 md:mt-0">In loving memory of [Name]</p>
    </div>
  </div>
</footer>

