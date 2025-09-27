@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto space-y-6">
    @if(session('status'))
      <div class="p-3 rounded bg-green-50 text-green-700">{{ session('status') }}</div>
    @endif

    <h1 class="text-xl font-semibold">Pending Wishes</h1>
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

