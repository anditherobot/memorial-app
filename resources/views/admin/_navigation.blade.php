@php
$currentRoute = request()->route()->getName();
$navigationItems = [
    [
        'name' => 'Dashboard',
        'icon' => '📊',
        'route' => 'admin.dashboard',
        'active' => $currentRoute === 'admin.dashboard'
    ],
    [
        'name' => 'Memorial Events',
        'icon' => '📅',
        'route' => 'memorial.events.index',
        'active' => str_contains($currentRoute, 'memorial.events')
    ],
    [
        'name' => 'Memorial Content',
        'icon' => '📝',
        'route' => 'memorial.content.index',
        'active' => str_contains($currentRoute, 'memorial.content')
    ],
    [
        'name' => 'Wishes & Messages',
        'icon' => '💌',
        'route' => 'admin.wishes',
        'active' => str_contains($currentRoute, 'wishes')
    ],
    [
        'name' => 'Gallery',
        'icon' => '🖼️',
        'route' => 'admin.gallery',
        'active' => str_contains($currentRoute, 'gallery')
    ],
    [
        'name' => 'Updates',
        'icon' => '📰',
        'route' => 'admin.updates.index',
        'active' => str_contains($currentRoute, 'updates')
    ],
    [
        'name' => 'Tasks',
        'icon' => '📋',
        'route' => 'admin.tasks.index',
        'active' => str_contains($currentRoute, 'tasks')
    ],
    [
        'name' => 'Documentation',
        'icon' => '📚',
        'route' => 'admin.docs',
        'active' => str_contains($currentRoute, 'docs')
    ]
];
@endphp

<nav class="flex-1 px-4 py-4 space-y-1">
  @foreach($navigationItems as $item)
    @if($item['disabled'] ?? false)
      <!-- Disabled/Coming Soon Items -->
      <div class="flex items-center px-3 py-2 text-sm text-gray-400 rounded-md cursor-not-allowed">
        <span class="mr-3">{{ $item['icon'] }}</span>
        {{ $item['name'] }}
        <span class="ml-auto text-xs bg-blue-100 px-2 py-1 rounded text-blue-600">Beta</span>
      </div>
    @else
      <!-- Active Navigation Items -->
      <a
        href="{{ route($item['route']) }}"
        class="flex items-center px-3 py-2 text-sm rounded-md transition-colors
          {{ $item['active']
            ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
            : 'text-gray-700 hover:bg-gray-100'
          }}"
        @if(isset($item['active']) && $item['active']) aria-current="page" @endif
      >
        <span class="mr-3">{{ $item['icon'] }}</span>
        {{ $item['name'] }}
      </a>
    @endif
  @endforeach
</nav>

<!-- Quick Actions Section -->
<div class="px-4 py-4 border-t">
  <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Quick Actions</h3>
  <div class="space-y-1">
    <a
      href="{{ route('upload.show') }}"
      class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 transition-colors"
    >
      <span class="mr-3">📸</span>
      Add Photos
    </a>
    <a
      href="{{ route('admin.updates.create') }}"
      class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 transition-colors"
    >
      <span class="mr-3">✍️</span>
      New Update
    </a>
    <a
      href="{{ route('home') }}"
      target="_blank"
      class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 transition-colors"
    >
      <span class="mr-3">🌐</span>
      View Site
      <svg class="w-3 h-3 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
      </svg>
    </a>
  </div>
</div>