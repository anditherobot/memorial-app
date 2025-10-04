@props([
  'hover' => false,
  'padding' => 'p-4',
])

@php
  $base = 'bg-white border border-gray-200 rounded-lg';
  $hover = $hover ? 'transition transform hover:-translate-y-0.5 hover:shadow-md' : '';
  $classes = trim("$base $hover $padding");
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</div>

