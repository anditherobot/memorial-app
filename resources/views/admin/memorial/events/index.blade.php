@extends('layouts.admin')

@section('breadcrumbs')
  <li class="inline-flex items-center">
    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-gray-500">Memorial Events</span>
  </li>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Memorial Events</h1>
      <a href="{{ route('memorial.events.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
        <span class="mr-2">➕</span>
        Add Event
      </a>
    </div>

    <!-- Event Type Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($eventTypes as $typeKey => $typeName)
        @php
          $typeEvents = $events->get($typeKey, collect());
          $activeEvents = $typeEvents->where('is_active', true);
          $upcomingEvents = $activeEvents->where('date', '>=', now()->toDateString());
        @endphp

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
          <!-- Card Header -->
          <div class="p-4 border-b bg-gray-50">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-gray-900">{{ $typeName }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                  {{ $activeEvents->count() }} event{{ $activeEvents->count() !== 1 ? 's' : '' }}
                </p>
              </div>
              <div class="text-2xl">
                @switch($typeKey)
                  @case('funeral') ⚱️ @break
                  @case('viewing') 👁️ @break
                  @case('burial') 🪦 @break
                  @case('repass') 🍽️ @break
                @endswitch
              </div>
            </div>
          </div>

          <!-- Card Content -->
          <div class="p-4">
            @if($typeEvents->isEmpty())
              <div class="text-center py-8 text-gray-500">
                <div class="text-3xl mb-2">📅</div>
                <p class="text-sm">No events yet</p>
                <a href="{{ route('memorial.events.create', ['type' => $typeKey]) }}"
                   class="inline-flex items-center mt-3 px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors">
                  Add {{ $typeName }}
                </a>
              </div>
            @else
              <div class="space-y-3">
                @foreach($typeEvents->take(3) as $event)
                  <div class="p-3 border rounded-lg {{ $event->is_active ? 'bg-white' : 'bg-gray-50' }}">
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-sm {{ $event->is_active ? 'text-gray-900' : 'text-gray-500' }} truncate">
                          {{ $event->title ?: 'Untitled Event' }}
                        </h4>
                        @if($event->date || $event->time)
                          <p class="text-xs text-gray-500 mt-1">
                            @if($event->date)
                              {{ $event->date->format('M j, Y') }}
                            @endif
                            @if($event->time)
                              {{ $event->time->format('g:i A') }}
                            @endif
                          </p>
                        @endif
                        @if($event->venue_name)
                          <p class="text-xs text-gray-500 truncate">{{ $event->venue_name }}</p>
                        @endif
                      </div>

                      <div class="flex items-center space-x-1 ml-2">
                        @if(!$event->is_active)
                          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                            Inactive
                          </span>
                        @endif
                        <a href="{{ route('memorial.events.edit', $event) }}"
                           class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                           title="Edit">
                          ✏️
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach

                @if($typeEvents->count() > 3)
                  <div class="text-center pt-2">
                    <p class="text-sm text-gray-500">
                      +{{ $typeEvents->count() - 3 }} more event{{ $typeEvents->count() - 3 !== 1 ? 's' : '' }}
                    </p>
                  </div>
                @endif

                <div class="pt-3 border-t">
                  <a href="{{ route('memorial.events.create', ['type' => $typeKey]) }}"
                     class="w-full inline-flex items-center justify-center px-3 py-2 text-sm bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                    <span class="mr-1">➕</span>
                    Add {{ $typeName }}
                  </a>
                </div>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <!-- Recent Events List -->
    @if($events->flatten()->isNotEmpty())
      <div class="bg-white rounded-lg border shadow-sm">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-medium text-gray-900">All Events</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @foreach($events->flatten()->sortBy(['date', 'time']) as $event)
                <tr class="{{ $event->is_active ? '' : 'bg-gray-50' }}">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      @if($event->posterMedia)
                        <img class="h-10 w-10 rounded-lg object-cover mr-3" src="{{ Storage::url($event->posterMedia->path) }}" alt="Event poster">
                      @endif
                      <div>
                        <div class="text-sm font-medium {{ $event->is_active ? 'text-gray-900' : 'text-gray-500' }}">
                          {{ $event->title ?: 'Untitled Event' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->event_type_display }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($event->date || $event->time)
                      <div>
                        @if($event->date)
                          {{ $event->date->format('M j, Y') }}
                        @endif
                      </div>
                      @if($event->time)
                        <div class="text-xs text-gray-400">
                          {{ $event->time->format('g:i A') }}
                        </div>
                      @endif
                    @else
                      <span class="text-gray-400">Not scheduled</span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->venue_name ?: 'Not specified' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    @if($event->is_active)
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                      </span>
                    @else
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Inactive
                      </span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <a href="{{ route('memorial.events.show', $event) }}" class="text-blue-600 hover:text-blue-900">View</a>
                    <a href="{{ route('memorial.events.edit', $event) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('memorial.events.destroy', $event) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
@endsection