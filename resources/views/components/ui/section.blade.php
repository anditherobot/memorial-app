@props([
  'title' => null,
  'description' => null,
])

<section {{ $attributes->merge(['class' => 'py-6']) }}>
  @if($title)
    <div class="mb-4">
      <h2 class="text-2xl font-serif elegant-title">{{ $title }}</h2>
      @if($description)
        <p class="text-gray-600 mt-1">{{ $description }}</p>
      @endif
    </div>
  @endif
  {{ $slot }}
</section>

