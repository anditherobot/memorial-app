@extends('layouts.app')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Updates</span>
  </li>
@endsection

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
      <x-ui.button id="load-more" variant="primary"
        hx-get="{{ $posts->nextPageUrl() }}"
        hx-target="#updates-list"
        hx-swap="beforeend"
        hx-trigger="click"
      >Load more</x-ui.button>
    @endif
  </div>
@endsection
