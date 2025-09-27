@extends('layouts.app')

@section('content')
  <div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Manage Updates</h1>
      <a href="{{ route('admin.updates.create') }}" class="px-4 py-2 bg-gray-900 text-white rounded">New Update</a>
    </div>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <div class="bg-white border rounded divide-y">
      @forelse($posts as $post)
        <div class="p-4 flex items-start gap-4">
          @php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); @endphp
          @if($cover)
            <img src="{{ Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path) }}" class="w-24 h-24 object-cover rounded" alt="cover" />
          @else
            <div class="w-24 h-24 bg-gray-100 rounded grid place-items-center text-gray-400 text-xs">No image</div>
          @endif
          <div class="flex-1">
            <div class="font-semibold">{{ $post->title }}</div>
            <div class="text-xs text-gray-500">Published: {{ $post->is_published ? 'Yes' : 'No' }} {{ $post->published_at ? '('.$post->published_at->toDayDateTimeString().')' : '' }}</div>
          </div>
          <div class="flex items-center gap-2">
            <a href="{{ route('admin.updates.edit', $post) }}" class="px-3 py-1.5 border rounded">Edit</a>
            <form method="POST" action="{{ route('admin.updates.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
              @csrf
              @method('DELETE')
              <button class="px-3 py-1.5 border border-red-300 text-red-700 rounded">Delete</button>
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

