@props([
  'href',
  'active' => false,
])

@php
  $classes = 'text-gray-700 hover:text-black transition-colors font-medium';
  if ($active) {
    $classes = 'text-black font-semibold';
  }
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>

