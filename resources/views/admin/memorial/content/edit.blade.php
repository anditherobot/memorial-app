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
    <span class="text-gray-500">Edit {{ $content->display_name }}</span>
  </li>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Edit {{ $content->display_name }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Content Type: {{ ucfirst(str_replace('_', ' ', $content->content_type)) }}
        </p>
      </div>
      <a href="{{ route('memorial.content.index') }}" class="text-gray-600 hover:text-gray-900">
        ← Back to Content
      </a>
    </div>

    <form method="POST" action="{{ route('memorial.content.update', $content) }}" class="space-y-6">
      @csrf
      @method('PUT')

      <div class="bg-white shadow-sm border rounded-lg">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-medium text-gray-900 flex items-center">
            <span class="mr-2">
              @switch($content->content_type)
                @case('bio') 📖 @break
                @case('memorial_name') 👤 @break
                @case('memorial_dates') 📅 @break
                @case('contact_info') 📞 @break
              @endswitch
            </span>
            {{ $content->display_name }}
          </h2>
        </div>

        <div class="p-6 space-y-6">
          <!-- Content Type (readonly for existing content) -->
          <div>
            <label for="content_type" class="block text-sm font-medium text-gray-700 mb-2">
              Content Type
            </label>
            <select name="content_type" id="content_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              @foreach($contentTypes as $key => $name)
                <option value="{{ $key }}" {{ old('content_type', $content->content_type) === $key ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
            @error('content_type')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
              Title
              <span class="text-gray-500">(optional)</span>
            </label>
            <input type="text" name="title" id="title" value="{{ old('title', $content->title) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Enter a title for this content...">
            @error('title')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Content -->
          <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
              Content
              <span class="text-gray-500">(optional)</span>
            </label>
            <textarea name="content" id="content" rows="8"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter the content...">{{ old('content', $content->content) }}</textarea>
            @error('content')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

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

      <!-- Submit Buttons -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <a href="{{ route('memorial.content.index') }}"
             class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
            Cancel
          </a>
          <button type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            Save Changes
          </button>
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
    </form>
  </div>
@endsection