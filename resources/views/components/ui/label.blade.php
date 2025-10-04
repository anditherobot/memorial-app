@props(['for' => null])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'block text-sm mb-1 text-gray-800']) }}>
  {{ $slot }}
</label>

