@extends('layouts.admin')



@section('content')
  <div class="max-w-6xl mx-auto space-y-6">
    <x-admin-page-header
        title="Dashboard"
        :breadcrumbs="[
            ['title' => 'Dashboard']
        ]"
    />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Pending Wishes</div>
        <div class="text-2xl font-semibold">{{ $stats['wishes_pending'] }}</div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Total Wishes</div>
        <div class="text-2xl font-semibold">{{ $stats['wishes_total'] }}</div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Posts</div>
        <div class="text-2xl font-semibold">{{ $stats['posts_total'] }}</div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Media</div>
        <div class="text-2xl font-semibold">{{ $stats['media_total'] }}</div>
      </div>
    </div>

    <!-- Memorial Management Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Memorial Management</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Memorial Events (In Development) -->
        <div class="p-4 border border-dashed border-blue-200 bg-blue-50 rounded-lg text-center">
          <div class="text-2xl mb-2">📅</div>
          <div class="font-medium text-blue-900">Memorial Events</div>
          <div class="text-xs mt-1 text-blue-600">Phase 2 - In Development</div>
        </div>

        <!-- Memorial Content (In Development) -->
        <div class="p-4 border border-dashed border-blue-200 bg-blue-50 rounded-lg text-center">
          <div class="text-2xl mb-2">📝</div>
          <div class="font-medium text-blue-900">Memorial Content</div>
          <div class="text-xs mt-1 text-blue-600">Phase 3 - In Development</div>
        </div>

        <!-- Gallery Management -->
        <a href="{{ route('admin.gallery') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
          <div class="text-2xl mb-2">🖼️</div>
          <div class="font-medium text-gray-900">Gallery</div>
          <div class="text-xs text-gray-500 mt-1">Manage photos</div>
        </a>

        <!-- Wishes & Messages -->
        <a href="{{ route('admin.wishes') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
          <div class="text-2xl mb-2">💌</div>
          <div class="font-medium text-gray-900">Messages</div>
          <div class="text-xs text-gray-500 mt-1">{{ $stats['wishes_pending'] }} pending</div>
        </a>
      </div>
    </div>

    <!-- Content Management -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Content & Updates</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('admin.updates.index') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-gray-900">Updates</div>
              <div class="text-sm text-gray-500">Manage announcements</div>
            </div>
            <div class="text-2xl">📰</div>
          </div>
        </a>

        <a href="{{ route('admin.tasks.index') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-gray-900">Task Tracker</div>
              <div class="text-sm text-gray-500">Development tasks</div>
            </div>
            <div class="text-2xl">📋</div>
          </div>
        </a>

        <a href="{{ route('admin.docs') }}" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-gray-900">Documentation</div>
              <div class="text-sm text-gray-500">Help & guides</div>
            </div>
            <div class="text-2xl">📚</div>
          </div>
        </a>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
      <div class="flex flex-wrap gap-3">
        <a href="{{ route('upload.show') }}" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors">
          <span class="mr-2">📸</span>
          Add Photos
        </a>
        <a href="{{ route('admin.updates.create') }}" class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition-colors">
          <span class="mr-2">✍️</span>
          New Update
        </a>
        <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
          <span class="mr-2">🌐</span>
          View Site
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
@endsection
