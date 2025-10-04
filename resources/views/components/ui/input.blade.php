@props([
  'name',
  'type' => 'text',
  'value' => null,
  'error' => null,
])

@php
  $hasError = $error ? $errors->has($error) : ($errors->has($name ?? ''));
  $classes = 'w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
  if ($hasError) {
    $classes .= ' border-red-500 focus:ring-red-500 focus:border-red-500';
  }
@endphp

<input
  type="{{ $type }}"
  name="{{ $name }}"
  @if(!is_null($value)) value="{{ $value }}" @endif
  {{ $attributes->merge(['class' => $classes]) }}
>

@if($hasError)
  <div class="text-red-600 text-sm mt-1">{{ $errors->first($error ?: $name) }}</div>
@endif

