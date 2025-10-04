@extends('layouts.admin')



@section('content')
  <div class="max-w-7xl mx-auto space-y-6">
    <x-admin-page-header
        title="Dashboard"
        :breadcrumbs="[
            ['title' => 'Dashboard']
        ]"
    />

    <!-- Top Row: Stats & Account Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Stats Cards -->
      <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-ui.card padding="p-4">
          <div class="text-sm text-gray-600 font-medium">Pending Wishes</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['wishes_pending'] }}</div>
        </x-ui.card>
        <x-ui.card padding="p-4">
          <div class="text-sm text-gray-600 font-medium">Total Wishes</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['wishes_total'] }}</div>
        </x-ui.card>
        <x-ui.card padding="p-4">
          <div class="text-sm text-gray-600 font-medium">Info Posts</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['posts_total'] }}</div>
        </x-ui.card>
                <x-ui.card padding="p-4">
                   <div class="text-sm text-gray-600 font-medium">Media Files</div>
                   <div class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['media_total'] }}</div>
                </x-ui.card>
                <x-ui.card padding="p-4">
                    <a href="{{ route('admin.docs.components') }}" class="text-sm text-gray-600 font-medium">Component Docs</a>
                </x-ui.card>
               </div>
      <!-- Account Info Card -->
      <x-ui.card padding="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account</h3>
        <div class="space-y-3">
          <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Username</div>
            <div class="text-sm font-mono bg-gray-50 px-3 py-2 rounded border border-gray-200">{{ $user->email }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Password</div>
            <div class="text-sm font-mono bg-gray-50 px-3 py-2 rounded border border-gray-200">••••••••</div>
          </div>
          <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <x-ui.button type="submit" class="w-full" variant="primary">Logout</x-ui.button>
          </form>
        </div>
      </x-ui.card>
    </div>

    <!-- Recent Pictures Gallery -->
    <x-ui.card padding="p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Recent Pictures</h2>
        <a href="{{ route('admin.gallery') }}" class="text-sm text-gray-600 hover:text-black transition-colors">View all →</a>
      </div>
      @if($recentPictures->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          @foreach($recentPictures as $picture)
            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 border border-gray-200 hover:border-gray-400 transition-colors">
              <img src="{{ asset('storage/' . $picture->storage_path) }}" alt="{{ $picture->original_filename }}" class="w-full h-full object-cover">
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <p>No pictures uploaded yet</p>
        </div>
      @endif
    </x-ui.card>

    <!-- Two Column Layout: Recent Uploads & Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Uploads -->
      <x-ui.card padding="p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Recent Uploads</h2>
          <a href="{{ route('admin.gallery') }}" class="text-sm text-gray-600 hover:text-black transition-colors">View all →</a>
        </div>
        @if($recentUploads->count() > 0)
          <div class="space-y-3">
            @foreach($recentUploads as $upload)
              <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                  @if(str_starts_with($upload->mime_type, 'image/'))
                    <div class="w-12 h-12 rounded overflow-hidden bg-gray-100">
                      <img src="{{ asset('storage/' . $upload->storage_path) }}" alt="" class="w-full h-full object-cover">
                    </div>
                  @else
                    <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center">
                      <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                  @endif
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ $upload->original_filename }}</p>
                  <p class="text-xs text-gray-500">{{ $upload->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-xs text-gray-500">
                  {{ number_format($upload->size_bytes / 1024, 1) }} KB
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-8 text-gray-500">No uploads yet</div>
        @endif
      </x-ui.card>

      <!-- Recent Info Posts -->
      <x-ui.card padding="p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Recent Info Posts</h2>
          <a href="{{ route('admin.updates.index') }}" class="text-sm text-gray-600 hover:text-black transition-colors">View all →</a>
        </div>
        @if($recentPosts->count() > 0)
          <div class="space-y-3">
            @foreach($recentPosts as $post)
              <a href="{{ route('admin.updates.edit', $post) }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-2">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $post->title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $post->created_at->diffForHumans() }}</p>
                  </div>
                  @if($post->is_published)
                    <x-ui.badge variant="dark">Published</x-ui.badge>
                  @else
                    <x-ui.badge variant="neutral">Draft</x-ui.badge>
                  @endif
                </div>
              </a>
            @endforeach
          </div>
        @else
          <div class="text-center py-8 text-gray-500">No info posts yet</div>
        @endif
      </x-ui.card>
    </div>
  </div>
@endsection
