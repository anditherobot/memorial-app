@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('memorial.content.index') }}" class="text-gray-500 hover:text-gray-700">Memorial Content</a>
  </li>
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">{{ $content->display_name }}</span>
  </li>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ $content->display_name }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Content Type: {{ ucfirst(str_replace('_', ' ', $content->content_type)) }}
        </p>
      </div>
      <div class="flex items-center space-x-3">
        <a href="{{ route('memorial.content.edit', $content) }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          Edit Content
        </a>
        <a href="{{ route('memorial.content.index') }}" class="text-gray-600 hover:text-gray-900">
          ← Back to Content
        </a>
      </div>
    </div>

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-medium text-gray-900 flex items-center">
          <span class="mr-2">
            @switch($content->content_type)
              @case('bio') 📖 @break
              @case('memorial_name') 👤 @break
              @case('memorial_dates') 📅 @break
              @case('contact_info') 📞 @break
            @endswitch
          </span>
          {{ $content->title ?: $content->display_name }}
        </h2>
      </div>

      <div class="p-6">
        @if($content->title)
          <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-700 mb-1">Title</h3>
            <p class="text-gray-900">{{ $content->title }}</p>
          </div>
        @endif

        @if($content->content)
          <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Content</h3>
            <div class="prose max-w-none">
              @if($content->content_type === 'contact_info')
                <pre class="whitespace-pre-wrap font-sans text-gray-900">{{ $content->content }}</pre>
              @else
                <div class="text-gray-900">{{ $content->content }}</div>
              @endif
            </div>
          </div>
        @endif

        @if($content->updated_at)
          <div class="text-sm text-gray-500 pt-4 border-t">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Last updated {{ $content->updated_at->diffForHumans() }}
            </span>
          </div>
        @endif
      </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <a href="{{ route('memorial.content.edit', $content) }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          Edit Content
        </a>

        <form method="POST" action="{{ route('memorial.content.destroy', $content) }}" class="inline"
              onsubmit="return confirm('Are you sure you want to delete this content?')">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
            Delete
          </button>
        </form>
      </div>

      <a href="{{ route('home') }}" target="_blank"
         class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
        <span class="mr-2">🌐</span>
        Preview Site
        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
        </svg>
      </a>
    </div>
  </div>
@endsection