@extends('layouts.app')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('updates.index') }}" class="text-gray-500 hover:text-gray-700">Updates</a>
  </li>
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">{{ Str::limit($post->title, 30) }}</span>
  </li>
@endsection

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
