@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-xl font-semibold">Updates</h1>
    <div id="updates-list" class="space-y-4">
      @if($posts->count())
        @include('updates._items', ['posts' => $posts])
      @else
        <div class="text-gray-500">No updates yet.</div>
      @endif
    </div>
    @if($posts->hasMorePages())
      <button
        id="load-more"
        class="px-4 py-2 bg-gray-900 text-white rounded"
        hx-get="{{ $posts->nextPageUrl() }}"
        hx-target="#updates-list"
        hx-swap="beforeend"
        hx-trigger="click"
      >Load more</button>
    @endif
  </div>
@endsection
