@extends('layouts.app')

@section('title', 'In Loving Memory — Alex Morgan')

@section('content')
    <section aria-labelledby="bio-title" class="mb-12">
        <h1 id="bio-title" class="text-2xl font-semibold mb-4">About Alex</h1>
        <p class="text-gray-700 leading-relaxed">{{ $bio }}</p>
    </section>

    <section aria-labelledby="photos-title" class="mb-12">
        <h2 id="photos-title" class="text-xl font-semibold mb-4">Photos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($photos as $photo)
                <figure class="overflow-hidden rounded-lg bg-white shadow">
                    <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" class="w-full h-56 object-cover" />
                    <figcaption class="p-3 text-sm text-gray-600">{{ $photo['caption'] }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="updates-title" class="mb-6">
        <h2 id="updates-title" class="text-xl font-semibold mb-4">Updates</h2>
        <div class="space-y-4">
            @foreach ($updates as $item)
                <article class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-semibold">{{ $item['title'] }}</h3>
                        <time datetime="{{ $item['date'] }}" class="text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($item['date'])->toFormattedDateString() }}</time>
                    </div>
                    @if (!empty($item['address']))
                        <p class="mt-1 text-sm text-gray-700">{{ $item['address'] }}</p>
                    @endif
                    @if (!empty($item['notes']))
                        <p class="mt-2 text-sm text-gray-700">{{ $item['notes'] }}</p>
                    @endif
                    @if (!empty($item['links']))
                        <ul class="mt-3 flex flex-wrap gap-3 text-sm">
                            @foreach ($item['links'] as $link)
                                <li>
                                    <a href="{{ $link['url'] }}" class="text-blue-600 hover:underline" target="_blank" rel="noopener">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endsection

