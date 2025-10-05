@props([
  'variant' => 'neutral', // neutral | info | success | warning | danger | dark
])

@php
  $base = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium';
  $variants = [
    'neutral' => 'bg-gray-200 text-gray-700',
    'info' => 'bg-blue-100 text-blue-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'dark' => 'bg-black text-white',
  ];
  $variantClass = $variants[$variant] ?? $variants['neutral'];
  $classes = trim("$base $variantClass");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</span>

