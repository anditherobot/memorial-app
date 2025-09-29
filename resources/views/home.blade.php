@extends('layouts.app')

@section('title', 'In Loving Memory — {{ $memorialName?->title ?: "Memorial" }}')

@section('content')
    <!-- Hero Bio Section -->
    <section aria-labelledby="bio-title" class="hero-section rounded-3xl p-8 md:p-12 mb-16 fade-in">
        <div class="relative z-10">
            <div class="text-center mb-8">
                <h1 id="bio-title" class="text-4xl md:text-5xl font-bold elegant-title text-shadow mb-4">
                    {{ $memorialName?->title ?: $memorialName?->content ?: "Memorial" }}
                </h1>
                <div class="w-24 h-1 bg-gradient-to-r from-gray-800 to-black mx-auto rounded-full mb-6"></div>
                @if($memorialDates?->content)
                    <div class="text-lg text-gray-600 italic whitespace-pre-line">{{ $memorialDates->content }}</div>
                @endif
            </div>
            <div class="max-w-4xl mx-auto">
                <p class="text-lg md:text-xl text-gray-700 leading-relaxed text-center font-light">{{ $bio }}</p>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Photos Section -->
    <section aria-labelledby="photos-title" class="mb-16 fade-in fade-in-delay-1">
        <div class="text-center mb-12">
            <h2 id="photos-title" class="text-3xl md:text-4xl font-bold elegant-title text-shadow mb-4">Cherished Memories</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">A collection of cherished moments and memories to celebrate a life well-lived.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($photos as $index => $photo)
                <figure class="photo-card rounded-2xl bg-white shadow-lg overflow-hidden fade-in" style="animation-delay: {{ 0.1 * ($index + 2) }}s">
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" class="w-full h-full object-cover" />
                    </div>
                    <figcaption class="p-6">
                        <p class="text-gray-700 font-medium text-center">{{ $photo['caption'] }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Updates Section -->
    <section aria-labelledby="updates-title" class="mb-12 fade-in fade-in-delay-2">
        <div class="text-center mb-12">
            <h2 id="updates-title" class="text-3xl md:text-4xl font-bold elegant-title text-shadow mb-4">Updates & Events</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Stay informed about memorial services, gatherings, and ways to honor their memory.</p>
        </div>
        <div class="space-y-6">
            @foreach ($updates as $index => $item)
                <article class="update-card rounded-2xl p-6 md:p-8 shadow-lg fade-in" style="animation-delay: {{ 0.1 * ($index + 4) }}s">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                @if(isset($item['type']) && $item['type'] === 'event')
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200">
                                        <svg class="w-4 h-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @elseif(isset($item['type']) && $item['type'] === 'update')
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200">
                                        <svg class="w-4 h-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200">
                                        <svg class="w-4 h-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <h3 class="text-xl font-semibold text-gray-900">{{ $item['title'] }}</h3>
                            </div>
                            <div class="flex items-center gap-4 flex-wrap">
                                <time datetime="{{ $item['date'] }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-black text-white">
                                    {{ \Illuminate\Support\Carbon::parse($item['date'])->toFormattedDateString() }}
                                </time>
                                @if(isset($item['time']) && $item['time'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-900">
                                        {{ $item['time'] }}
                                    </span>
                                @endif
                                @if(isset($item['event_type']) && $item['event_type'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-800 text-white">
                                        {{ ucfirst(str_replace('_', ' ', $item['event_type'])) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (!empty($item['address']) || !empty($item['venue_name']))
                        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    @if(!empty($item['venue_name']))
                                        <p class="text-gray-900 font-medium">{{ $item['venue_name'] }}</p>
                                    @endif
                                    @if(!empty($item['address']))
                                        <p class="text-gray-700">{{ $item['address'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($item['notes']))
                        <p class="text-gray-700 mb-4 leading-relaxed">{{ $item['notes'] }}</p>
                    @endif

                    @if (!empty($item['links']))
                        <div class="flex flex-wrap gap-3">
                            @foreach ($item['links'] as $link)
                                <a href="{{ $link['url'] }}"
                                   class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors duration-200 shadow-md hover:shadow-lg"
                                   target="_blank"
                                   rel="noopener">
                                    {{ $link['label'] }}
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    @if($contactInfo?->content)
        <!-- Section Divider -->
        <div class="section-divider"></div>

        <!-- Contact Section -->
        <section aria-labelledby="contact-title" class="mb-12 fade-in">
            <div class="text-center mb-8">
                <h2 id="contact-title" class="text-3xl md:text-4xl font-bold elegant-title text-shadow mb-4">Contact Information</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Reach out to family members for questions or to share condolences.</p>
            </div>
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-lg">
                    @if($contactInfo->title)
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ $contactInfo->title }}</h3>
                    @endif
                    <div class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $contactInfo->content }}</div>
                </div>
            </div>
        </section>
    @endif
@endsection
