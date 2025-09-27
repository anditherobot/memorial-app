@extends('layouts.app')

@section('content')
  <div class="max-w-md mx-auto">
    <h1 class="text-xl font-semibold mb-4">Login</h1>
    @if($errors->any())
      <div class="p-3 rounded bg-red-50 text-red-700 mb-3">
        <ul class="space-y-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
      @csrf
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" />
        @error('email')
          <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div>
        <label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" required
               class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" />
        @error('password')
          <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
        @enderror
      </div>
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" value="1" /> Remember me
      </label>
      <div>
        <button class="px-4 py-2 bg-gray-900 text-white rounded">Sign in</button>
      </div>
    </form>
    <div class="mt-3 text-sm text-gray-600">
      Tip: default admin user is admin@example.com / secret (dev only)
    </div>
  </div>
@endsection

