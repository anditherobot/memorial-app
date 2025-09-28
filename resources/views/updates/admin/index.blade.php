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
      <a href="{{ route('admin.updates.create') }}" class="px-4 py-2 bg-gray-900 text-white rounded hover:bg-gray-800 transition-colors">New Update</a>
    </div>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white border rounded divide-y">
      @forelse($posts as $post)
        <div class="p-4 flex items-start gap-4 hover:bg-gray-50 transition-colors">
          @php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); @endphp
          @if($cover)
            <img src="{{ Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path) }}" class="w-24 h-24 object-cover rounded" alt="cover" />
          @else
            <div class="w-24 h-24 bg-gray-100 rounded grid place-items-center text-gray-400 text-xs">No image</div>
          @endif

          <div class="flex-1 min-w-0 cursor-pointer" onclick="window.location='{{ route('admin.updates.edit', $post) }}'">
            <div class="font-semibold">{{ $post->title }}</div>
            <div class="text-xs text-gray-500">Published: {{ $post->is_published ? 'Yes' : 'No' }} {{ $post->published_at ? '('.$post->published_at->toDayDateTimeString().')' : '' }}</div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.updates.edit', $post) }}" class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-sm hover:bg-gray-50 transition-colors">Edit</a>
            <form method="POST" action="{{ route('admin.updates.destroy', $post) }}" onsubmit="return confirm('Delete this post?')" class="inline">
              @csrf
              @method('DELETE')
              <button class="px-3 py-1.5 border border-red-300 text-red-700 rounded text-sm hover:bg-red-50 transition-colors">Delete</button>
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

