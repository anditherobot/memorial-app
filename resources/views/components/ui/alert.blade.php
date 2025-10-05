@props([
  'variant' => 'neutral', // neutral | info | success | warning | danger
  'style' => 'soft', // soft | solid
])

@php
  $base = 'rounded-md px-4 py-3 border text-sm flex items-start gap-2';
  $soft = [
    'neutral' => 'bg-gray-50 text-gray-800 border-gray-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'danger' => 'bg-red-50 text-red-800 border-red-200',
  ];
  $solid = [
    'neutral' => 'bg-gray-800 text-white border-gray-900',
    'info' => 'bg-blue-600 text-white border-blue-700',
    'success' => 'bg-green-600 text-white border-green-700',
    'warning' => 'bg-yellow-500 text-white border-yellow-600',
    'danger' => 'bg-red-600 text-white border-red-700',
  ];
  $palette = $style === 'solid' ? $solid : $soft;
  $variantClass = $palette[$variant] ?? $palette['neutral'];
  $classes = trim("$base $variantClass");
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
  @if($attributes->has('action'))
    <div class="ml-auto">{{ $action }}</div>
  @endif
  @if($attributes->has('icon'))
    <div class="shrink-0">{{ $icon }}</div>
  @endif
  @if($attributes->has('message'))
    <div>{{ $message }}</div>
  @endif
</div>

