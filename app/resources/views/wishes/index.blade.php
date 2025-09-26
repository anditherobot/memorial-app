@extends('layouts.app')

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
