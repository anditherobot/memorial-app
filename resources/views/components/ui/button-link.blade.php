@props([
  'href' => '#',
  'variant' => 'primary', // primary | secondary | ghost | danger
  'size' => 'md', // sm | md | lg
  'target' => null,
  'rel' => null,
])

@php
  $base = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1';
  $sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
  ];
  $variants = [
    'primary' => 'bg-black text-white hover:bg-gray-800 focus:ring-gray-300',
    'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-300',
    'ghost' => 'bg-transparent text-gray-800 hover:bg-gray-100 focus:ring-gray-300',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300',
    'info' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-300',
    'outline' => 'bg-transparent border border-gray-300 text-gray-800 hover:bg-gray-100 focus:ring-gray-300',
    'brand-outline' => 'bg-transparent border-2 border-purple-600 text-purple-700 hover:bg-purple-50 focus:ring-purple-500',
  ];
  $sizeClass = $sizes[$size] ?? $sizes['md'];
  $variantClass = $variants[$variant] ?? $variants['primary'];
  $classes = trim("$base $sizeClass $variantClass");
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($target) target="{{ $target }}" @endif @if($rel) rel="{{ $rel }}" @endif>
  {{ $slot }}
  @if($attributes->has('icon'))
    {{ $icon }}
  @endif
</a>
