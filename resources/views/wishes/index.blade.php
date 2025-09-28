@extends('layouts.app')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Wishes</span>
  </li>
@endsection

@section('content')
  <div class="max-w-2xl mx-auto space-y-6">
    @if(session('status'))
      <div class="p-3 rounded bg-green-50 text-green-700">{{ session('status') }}</div>
    @endif

    <h1 class="text-xl font-semibold">Share a Wish</h1>
    <form method="POST" action="{{ route('wishes.store') }}" class="space-y-3"
          hx-post="{{ route('wishes.store') }}"
          hx-target="#wish-status"
          hx-swap="innerHTML">
      @csrf
      <div class="hidden">
        <label>Website <input type="text" name="website" value=""></label>
      </div>
      <div>
        <label class="block text-sm mb-1">Your name</label>
        <input name="name" value="{{ old('name') }}" required maxlength="120" class="w-full border rounded px-3 py-2" />
        @error('name')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block text-sm mb-1">Message</label>
        <textarea name="message" rows="4" required maxlength="2000" class="w-full border rounded px-3 py-2">{{ old('message') }}</textarea>
        @error('message')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
      </div>
      <button class="px-4 py-2 bg-gray-900 text-white rounded">Submit</button>
    </form>

    <div id="wish-status" class="text-sm text-gray-700"></div>

    <h2 class="text-lg font-semibold">Recent Wishes</h2>
    <div class="space-y-4">
      @forelse($wishes as $wish)
        <article class="p-4 bg-white border rounded">
          <div class="text-sm text-gray-500">{{ $wish->created_at->diffForHumans() }}</div>
          <div class="font-medium">{{ $wish->name }}</div>
          <p class="mt-2">{{ $wish->message }}</p>
        </article>
      @empty
        <div class="text-gray-500">No wishes yet. Be the first.</div>
      @endforelse
    </div>

    <div>
      {{ $wishes->links() }}
    </div>
  </div>
@endsection
