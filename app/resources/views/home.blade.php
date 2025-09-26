@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold">In Loving Memory</h1>
    <p class="text-gray-700">This memorial website shares photos, messages, and updates celebrating a life well‑lived.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <a href="{{ route('gallery.index') }}" class="p-4 rounded border bg-white hover:bg-gray-50">Gallery</a>
      <a href="{{ route('wishes.index') }}" class="p-4 rounded border bg-white hover:bg-gray-50">Wishwall</a>
      <a href="{{ route('updates.index') }}" class="p-4 rounded border bg-white hover:bg-gray-50">Updates</a>
    </div>
  </div>
@endsection

