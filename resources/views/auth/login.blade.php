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
        <x-ui.label for="email">Email</x-ui.label>
        <x-ui.input name="email" type="email" :value="old('email')" required error="email" />
      </div>
      <div>
        <x-ui.label for="password">Password</x-ui.label>
        <x-ui.input name="password" type="password" required error="password" />
      </div>
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" value="1" /> Remember me
      </label>
      <div>
        <x-ui.button type="submit" variant="primary">Sign in</x-ui.button>
      </div>
    </form>
    <div class="mt-3 text-sm text-gray-600">
      Tip: default admin user is admin@example.com / secret (dev only)
    </div>
  </div>
@endsection
