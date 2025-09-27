@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Memorial Content</span>
  </li>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Memorial Content</h1>
      <p class="text-sm text-gray-500">Manage biography, details, and contact information</p>
    </div>

    <!-- Content Management Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      @foreach($contentTypes as $typeKey => $typeName)
        @php
          $content = $contents[$typeKey] ?? null;
          $hasContent = $content && ($content->title || $content->content);
          $wordCount = $content && $content->content ? str_word_count(strip_tags($content->content)) : 0;
        @endphp

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
          <!-- Card Header -->
          <div class="p-4 border-b {{ $hasContent ? 'bg-green-50' : 'bg-gray-50' }}">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-gray-900">{{ $typeName }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                  @if($hasContent)
                    {{ $wordCount }} words
                    @if($content->updated_at)
                      • Updated {{ $content->updated_at->diffForHumans() }}
                    @endif
                  @else
                    Not configured yet
                  @endif
                </p>
              </div>
              <div class="text-2xl">
                @switch($typeKey)
                  @case('bio') 📖 @break
                  @case('memorial_name') 👤 @break
                  @case('memorial_dates') 📅 @break
                  @case('contact_info') 📞 @break
                @endswitch
              </div>
            </div>
          </div>

          <!-- Card Content -->
          <div class="p-4">
            @if($hasContent)
              <div class="space-y-3">
                @if($content->title)
                  <div>
                    <h4 class="font-medium text-sm text-gray-900">{{ $content->title }}</h4>
                  </div>
                @endif

                @if($content->content)
                  <div class="text-sm text-gray-600">
                    @if($typeKey === 'bio')
                      {{ Str::limit($content->content, 150) }}
                    @elseif($typeKey === 'contact_info')
                      <pre class="whitespace-pre-wrap font-sans">{{ Str::limit($content->content, 100) }}</pre>
                    @else
                      {{ Str::limit($content->content, 100) }}
                    @endif
                  </div>
                @endif

                <div class="pt-3 border-t flex items-center space-x-2">
                  <a href="{{ route('memorial.content.edit-by-type', $typeKey) }}"
                     class="inline-flex items-center px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors">
                    ✏️ Edit
                  </a>

                  @if($content)
                    <form method="POST" action="{{ route('memorial.content.destroy', $content) }}" class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this content?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="inline-flex items-center px-3 py-1 text-sm bg-red-50 text-red-700 rounded-md hover:bg-red-100 transition-colors">
                        🗑️ Clear
                      </button>
                    </form>
                  @endif
                </div>
              </div>
            @else
              <div class="text-center py-8 text-gray-500">
                <div class="text-3xl mb-2">
                  @switch($typeKey)
                    @case('bio') 📝 @break
                    @case('memorial_name') 👤 @break
                    @case('memorial_dates') 📅 @break
                    @case('contact_info') 📞 @break
                  @endswitch
                </div>
                <p class="text-sm mb-3">No content yet</p>
                <a href="{{ route('memorial.content.edit-by-type', $typeKey) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                  Add {{ $typeName }}
                </a>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <!-- Content Guidelines -->
    <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
      <h2 class="text-lg font-medium text-blue-900 mb-3">Content Guidelines</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
        <div>
          <h3 class="font-medium mb-2">📖 Biography</h3>
          <p>Share the life story, achievements, and memories that celebrate their legacy.</p>
        </div>
        <div>
          <h3 class="font-medium mb-2">👤 Memorial Name</h3>
          <p>Full name as it should appear throughout the memorial site.</p>
        </div>
        <div>
          <h3 class="font-medium mb-2">📅 Memorial Dates</h3>
          <p>Birth and passing dates, or other significant life milestones.</p>
        </div>
        <div>
          <h3 class="font-medium mb-2">📞 Contact Information</h3>
          <p>Family contact details for visitors who wish to reach out or send condolences.</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    @if(collect($contents)->some(fn($content) => $content && ($content->title || $content->content)))
      <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
          <a href="{{ route('home') }}" target="_blank"
             class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
            <span class="mr-2">🌐</span>
            Preview Site
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
          </a>

          <a href="{{ route('memorial.events.index') }}"
             class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors">
            <span class="mr-2">📅</span>
            Manage Events
          </a>
        </div>
      </div>
    @endif
  </div>
@endsection