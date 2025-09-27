@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('memorial.events.index') }}" class="text-gray-500 hover:text-gray-700">Memorial Events</a>
  </li>
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Edit Event</span>
  </li>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Edit Memorial Event</h1>
      <a href="{{ route('memorial.events.index') }}" class="text-gray-600 hover:text-gray-900">
        ← Back to Events
      </a>
    </div>

    <form method="POST" action="{{ route('memorial.events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      <div class="bg-white shadow-sm border rounded-lg">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-medium text-gray-900">Event Details</h2>
        </div>

        <div class="p-6 space-y-6">
          <!-- Event Type -->
          <div>
            <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
            <select name="event_type" id="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
              @foreach($eventTypes as $key => $name)
                <option value="{{ $key }}" {{ old('event_type', $event->event_type) == $key ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
            @error('event_type')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
            <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Memorial Service for John Doe" required>
            @error('title')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Date and Time -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
              <input type="date" name="date" id="date" value="{{ old('date', $event->date?->format('Y-m-d')) }}"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              @error('date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="time" class="block text-sm font-medium text-gray-700 mb-2">Time</label>
              <input type="time" name="time" id="time" value="{{ old('time', $event->time?->format('H:i')) }}"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              @error('time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Venue Information -->
          <div>
            <label for="venue_name" class="block text-sm font-medium text-gray-700 mb-2">Venue Name</label>
            <input type="text" name="venue_name" id="venue_name" value="{{ old('venue_name', $event->venue_name) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Grace Chapel">
            @error('venue_name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
            <textarea name="address" id="address" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Full address including city, state, and zip code">{{ old('address', $event->address) }}</textarea>
            @error('address')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Contact Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
              <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $event->contact_phone) }}"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="(555) 123-4567">
              @error('contact_phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
              <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $event->contact_email) }}"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="contact@venue.com">
              @error('contact_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes & Instructions</label>
            <textarea name="notes" id="notes" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Special instructions, directions, or additional information...">{{ old('notes', $event->notes) }}</textarea>
            @error('notes')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Current Poster -->
          @if($event->posterMedia)
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Poster</label>
              <div class="flex items-center space-x-4">
                <img src="{{ Storage::url($event->posterMedia->path) }}" alt="Current poster" class="h-20 w-20 object-cover rounded-lg border">
                <div>
                  <p class="text-sm text-gray-600">{{ $event->posterMedia->filename }}</p>
                  <p class="text-xs text-gray-500">{{ number_format($event->posterMedia->size / 1024, 1) }} KB</p>
                </div>
              </div>
            </div>
          @endif

          <!-- Event Poster -->
          <div>
            <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">
              {{ $event->posterMedia ? 'Replace Poster (Optional)' : 'Event Poster (Optional)' }}
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="poster" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                    <span>{{ $event->posterMedia ? 'Upload new image' : 'Upload an image' }}</span>
                    <input id="poster" name="poster" type="file" class="sr-only" accept="image/*">
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
              </div>
            </div>
            @error('poster')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Active Status -->
          <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $event->is_active) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">
              Active (event will be visible on the website)
            </label>
          </div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('memorial.events.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
          Cancel
        </a>
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
          Update Event
        </button>
      </div>
    </form>
  </div>
@endsection