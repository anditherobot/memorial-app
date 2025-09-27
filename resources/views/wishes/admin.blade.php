@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Wishes & Messages</span>
  </li>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Wishes & Messages</h1>
      <p class="text-sm text-gray-500">Moderate and approve memorial messages</p>
    </div>
    <div class="space-y-4">
      @forelse($pending as $wish)
        <article class="p-4 bg-white border rounded">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium">{{ $wish->name }}</div>
              <div class="text-sm text-gray-500">{{ $wish->created_at->toDayDateTimeString() }} — IP {{ $wish->submitted_ip }}</div>
            </div>
            <div class="space-x-2">
              <form method="POST" action="{{ route('admin.wishes.approve', $wish) }}" class="inline">
                @csrf
                <button class="px-3 py-1 rounded bg-green-600 text-white">Approve</button>
              </form>
              <form method="POST" action="{{ route('admin.wishes.delete', $wish) }}" class="inline" onsubmit="return confirm('Delete this wish?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-1 rounded bg-red-600 text-white">Delete</button>
              </form>
            </div>
          </div>
          <p class="mt-2">{{ $wish->message }}</p>
        </article>
      @empty
        <div class="text-gray-500">No pending wishes.</div>
      @endforelse
    </div>

    {{ $pending->links() }}
  </div>
@endsection

