@extends('layouts.app')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Wishes</span>
  </li>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto space-y-8">
    <!-- Header Section -->
    <div class="text-center py-6">
      <div class="flex items-center justify-center mb-3">
        <svg class="w-8 h-8 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <h1 class="text-3xl font-bold text-gray-900">Messages of Love & Remembrance</h1>
      </div>
      <p class="text-gray-600 max-w-2xl mx-auto">
        Heartfelt words from family and friends to honor their memory
      </p>
    </div>

    @if(session('status'))
      <div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-start">
        <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        {{ session('status') }}
      </div>
    @endif

    <div class="grid grid-cols-1 gap-8">
      <!-- Wishes List - Takes up 3/4 on large screens -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Stats Bar -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 border border-purple-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
              <span class="text-sm font-medium text-gray-900">{{ $wishes->total() }} {{ Str::plural('message', $wishes->total()) }} of love and remembrance</span>
            </div>
            <div class="text-xs text-gray-600">Latest: {{ $wishes->first()?->created_at?->diffForHumans() ?? 'None yet' }}</div>
          </div>
        </div>

        <!-- Top Callout + CTA -->
        <div class="mt-4 bg-white border border-purple-100 rounded-xl p-6 shadow-sm flex items-center justify-between">
          <div class="flex items-center text-sm text-gray-700">
            <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            Share a message in remembrance
          </div>
          <div class="flex items-center gap-3">
            <x-ui.button-link href="#share-form" variant="brand-outline" size="md">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Post a Wish
            </x-ui.button-link>
          </div>
        </div>

        <!-- Inline Share Form (beneath CTA) -->
        <x-ui.card class="rounded-xl shadow-sm border-gray-100 overflow-hidden mt-4" padding="p-0" id="share-form">
          <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-5 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900 text-lg flex items-center">
              <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
              </svg>
              Share Your Heart
            </h2>
            <p class="text-sm text-gray-600 mt-1">Add your message of love and remembrance</p>
          </div>

          <form id="wish-form" method="POST" action="{{ route('wishes.store') }}" class="p-6 space-y-6"
                hx-post="{{ route('wishes.store') }}"
                hx-target="#wish-status"
                hx-swap="innerHTML">
            @csrf
            <div class="hidden">
              <label>Website <input type="text" name="website" value=""></label>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Your Name</label>
              <input name="name" value="{{ old('name') }}" required maxlength="120"
                     placeholder="Enter your name..."
                     class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" />
              @error('name')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Your Message</label>
              <textarea name="message" rows="6" required maxlength="2000"
                        placeholder="Share a cherished memory, express your love, or offer words of comfort..."
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors resize-none">{{ old('message') }}</textarea>
              @error('message')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
              <div class="text-xs text-gray-500 mt-2 flex justify-between">
                <span>Your words matter</span>
                <span>Max 2000 chars</span>
              </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button type="submit" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Share Your Love
              </button>
              <button type="button" onclick="document.getElementById('wish-form').reset()" class="inline-flex items-center px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                Clear
              </button>
            </div>
          </form>
        </x-ui.card>

        <!-- Wishes Feed -->
        <!-- Top pagination for long lists -->
        <div class="mt-2 flex justify-end">
          {{ $wishes->withQueryString()->onEachSide(1)->links() }}
        </div>

        <div class="grid grid-cols-1 gap-6 mt-4">
          @php($lastDate = null)
          @forelse($wishes as $wish)
            @php($currentDate = $wish->created_at->toDateString())
            @if($lastDate !== $currentDate)
              @php($lastDate = $currentDate)
              @php($dateLabel = $wish->created_at->isToday() ? 'Today' : ($wish->created_at->isYesterday() ? 'Yesterday' : $wish->created_at->format('M j, Y')))
              <div class="xl:col-span-2 flex items-center text-xs font-semibold text-gray-600 uppercase tracking-wide mt-4">
                <span class="w-10 h-px bg-gray-200 mr-3"></span>
                <span>{{ $dateLabel }}</span>
                <span class="flex-1 h-px bg-gray-200 ml-3"></span>
              </div>
            @endif
            <x-ui.card class="rounded-xl shadow-sm border-gray-100 hover:shadow-md transition-all duration-200 hover:border-purple-200" padding="p-6">
              <div class="flex items-start gap-4">
                <div class="relative">
                  <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-lg flex-shrink-0 shadow-sm">
                    {{ strtoupper(substr($wish->name, 0, 2)) }}
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"></path>
                    </svg>
                  </div>
                </div>

                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between mb-3">
                    <div>
                      <div class="font-semibold text-gray-900 text-lg">{{ $wish->name }}</div>
                      <div class="text-[13px] text-gray-600 flex items-center mt-1">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $wish->created_at->diffForHumans() }}
                        <span class="mx-2">•</span>
                        <span class="text-purple-600 font-medium">{{ $wish->created_at->format('M j, Y') }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-purple-300">
                    <blockquote class="text-gray-800 leading-7 text-[1.05rem]">
                      "{{ $wish->message }}"
                    </blockquote>
                  </div>

                  <!-- Engagement indicators -->
                  <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                      <button class="flex items-center hover:text-purple-600 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span>{{ rand(3, 15) }}</span>
                      </button>
                      <span class="text-xs">{{ Str::limit($wish->message, 50) }}</span>
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ strlen($wish->message) }} characters
                    </div>
                  </div>
                </div>
              </div>
            </x-ui.card>
          @empty
            <div class="text-center py-16">
              <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
              <p class="text-gray-500">Be the first to share a memory or message of love.</p>
            </div>
          @endforelse
        </div>

        @if($wishes->hasPages())
          <div class="flex justify-center pt-4">
            {{ $wishes->links() }}
          </div>
        @endif
      </div>

      
    </div>

    <div id="wish-status" class="text-sm text-gray-700"></div>
  </div>
@endsection
