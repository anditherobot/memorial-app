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
    <span class="text-gray-500">Create Memorial Content</span>
  </li>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Create Memorial Content</h1>
        <p class="text-sm text-gray-500 mt-1">Add new memorial content to your site</p>
      </div>
      <x-ui.button-link href="{{ route('memorial.content.index') }}" variant="ghost">← Back to Content</x-ui.button-link>
    </div>

    <form method="POST" action="{{ route('memorial.content.store') }}" class="space-y-6">
      @csrf

      <x-ui.card class="shadow-sm" padding="p-0">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-medium text-gray-900">Content Details</h2>
        </div>

        <div class="p-6 space-y-6">
          <!-- Content Type -->
          <div>
            <label for="content_type" class="block text-sm font-medium text-gray-700 mb-2">
              Content Type *
            </label>
            <select name="content_type" id="content_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select a content type...</option>
              @foreach($contentTypes as $key => $name)
                <option value="{{ $key }}" {{ old('content_type') === $key ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
            @error('content_type')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">
              Choose the type of memorial content you want to create.
            </p>
          </div>

          <!-- Title -->
          <div>
            <x-ui.label for="title">Title <span class="text-gray-500">(optional)</span></x-ui.label>
            <x-ui.input name="title" id="title" :value="old('title')" placeholder="Enter a title for this content..." error="title" />
          </div>

          <!-- Content -->
          <div>
            <x-ui.label for="content">Content <span class="text-gray-500">(optional)</span></x-ui.label>
            <x-ui.textarea name="content" id="content" rows="8" placeholder="Enter the content..." error="content">{{ old('content') }}</x-ui.textarea>
          </div>
        </div>
      </x-ui.card>

      <!-- Submit Buttons -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <x-ui.button-link href="{{ route('memorial.content.index') }}" variant="outline">Cancel</x-ui.button-link>
          <x-ui.button type="submit" variant="primary">Create Content</x-ui.button>
        </div>
      </div>
    </form>

    <!-- Content Guidelines -->
    <div class="mt-8 bg-blue-50 rounded-lg border border-blue-200 p-6">
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
  </div>
@endsection
