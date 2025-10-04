@php use Illuminate\Support\Str; @endphp
@foreach($posts as $post)
  @php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); @endphp
  <article class="update-card border rounded-lg p-4 flex gap-4">
    @if($cover)
      <img src="{{ Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path) }}" class="w-28 h-28 object-cover rounded" alt="cover" />
    @endif
    <a href="{{ route('updates.show', $post) }}" class="block flex-1">
      <h2 class="text-lg font-semibold">{{ $post->title }}</h2>
      <div class="mt-1 space-x-2">
        @if($post->author_name)
          <x-ui.badge variant="neutral">{{ $post->author_name }}</x-ui.badge>
        @endif
        @if($post->published_at)
          <x-ui.badge variant="neutral">{{ $post->published_at->format('M j, Y') }}</x-ui.badge>
        @endif
      </div>
      <div class="mt-2 prose max-w-none">{!! Str::limit(strip_tags($post->body), 240) !!}</div>
    </a>
  </article>
@endforeach
