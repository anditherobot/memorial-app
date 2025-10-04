@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Updates</span>
  </li>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Manage Updates</h1>
        <p class="text-sm text-gray-500">Memorial announcements and news</p>
      </div>
      <x-ui.button-link href="{{ route('admin.updates.create') }}" variant="primary">New Update</x-ui.button-link>
    </div>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white border rounded divide-y">
      @forelse($posts as $post)
        <div class="relative">
          <a href="{{ route('admin.updates.edit', $post) }}" class="block p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-4">
              @php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); @endphp
              @if($cover)
                <img src="{{ Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path) }}" class="w-24 h-24 object-cover rounded" alt="cover" />
              @else
                <div class="w-24 h-24 bg-gray-100 rounded grid place-items-center text-gray-400 text-xs">No image</div>
              @endif

              <div class="flex-1 min-w-0">
                <div class="font-semibold">{{ $post->title }}</div>
                <div class="text-xs text-gray-500">Published: {{ $post->is_published ? 'Yes' : 'No' }} {{ $post->published_at ? '('.$post->published_at->toDayDateTimeString().')' : '' }}</div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-sm">Edit</span>
              </div>
            </div>
          </a>

          <div class="absolute top-4 right-4 z-10">
            <form method="POST" action="{{ route('admin.updates.destroy', $post) }}" onsubmit="return confirm('Delete this post?')" class="inline">
              @csrf
              @method('DELETE')
              <x-ui.button type="submit" variant="danger" size="sm" onclick="event.stopPropagation()">Delete</x-ui.button>
            </form>
          </div>
        </div>
      @empty
        <div class="p-4 text-gray-500">No posts yet.</div>
      @endforelse
    </div>

    <div>{{ $posts->links() }}</div>
  </div>
@endsection
