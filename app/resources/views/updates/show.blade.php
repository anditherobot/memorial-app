@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto space-y-6">
    <article class="prose max-w-none p-4 bg-white border rounded">
      <h1>{{ $post->title }}</h1>
      <div class="text-sm text-gray-500">{{ optional($post->published_at)->toDayDateTimeString() }}</div>
      <div class="mt-4">{!! $post->body !!}</div>
    </article>
    <a href="{{ route('updates.index') }}" class="inline-block text-blue-600">← Back to updates</a>
  </div>
@endsection

