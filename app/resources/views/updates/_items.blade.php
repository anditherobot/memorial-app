@php use Illuminate\Support\Str; @endphp
@foreach($posts as $post)
  <article class="p-4 bg-white border rounded">
    <a href="{{ route('updates.show', $post) }}" class="block">
      <h2 class="text-lg font-semibold">{{ $post->title }}</h2>
      <div class="text-sm text-gray-500">{{ optional($post->published_at)->toDayDateTimeString() }}</div>
      <div class="mt-2 prose max-w-none">{!! Str::limit(strip_tags($post->body), 240) !!}</div>
    </a>
  </article>
@endforeach

