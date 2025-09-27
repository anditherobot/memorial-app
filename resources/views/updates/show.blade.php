@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto space-y-6">
    @php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); @endphp
    @if($cover)
      <img src="{{ Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path) }}" class="w-full h-auto rounded border" alt="cover" />
    @endif
    <article class="prose max-w-none p-4 bg-white border rounded update-card">
      <h1 class="mb-2">{{ $post->title }}</h1>
      <div class="mb-3 space-x-2">
        @if($post->author_name)
          <span class="chip bg-gray-100">{{ $post->author_name }}</span>
        @endif
        @if($post->published_at)
          <span class="chip bg-gray-100">{{ $post->published_at->toDayDateTimeString() }}</span>
        @endif
      </div>
      <div class="mt-4">{!! $post->body !!}</div>
    </article>
    <a href="{{ route('updates.index') }}" class="inline-block text-blue-600">← Back to updates</a>
  </div>
@endsection
