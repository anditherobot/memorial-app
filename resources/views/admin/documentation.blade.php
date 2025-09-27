@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <h1 class="text-2xl font-semibold">📚 Documentation</h1>

  <!-- Quick Navigation -->
  <div class="p-4 bg-white border rounded">
    <h2 class="text-lg font-medium mb-3">Quick Navigation</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
      <a href="#overview" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">📋 Overview</a>
      <a href="#public-features" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">👥 Public</a>
      <a href="#admin-features" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">⚙️ Admin</a>
      <a href="#gallery-management" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">🖼️ Gallery</a>
      <a href="#content-management" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">📝 Content</a>
      <a href="#mobile-features" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">📱 Mobile</a>
      <a href="#troubleshooting" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">🔧 Help</a>
      <a href="#version-info" class="p-2 rounded border bg-gray-50 hover:bg-gray-100 text-center">ℹ️ Info</a>
    </div>
  </div>

  <!-- App Overview -->
  <section id="overview">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">📋 App Overview</h2>
      <p class="text-gray-700 mb-4">
        This memorial website allows friends and family to honor the memory of a loved one through photos, messages, and shared memories.
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="p-3 border rounded bg-green-50">
          <div class="text-sm font-medium text-green-900 mb-2">✅ Working Features</div>
          <div class="text-xs text-green-800 space-y-1">
            <div>• Mobile-responsive design</div>
            <div>• Photo/video uploads</div>
            <div>• Wish/memory system</div>
            <div>• Admin moderation</div>
          </div>
        </div>
        <div class="p-3 border rounded bg-blue-50">
          <div class="text-sm font-medium text-blue-900 mb-2">🎯 Key Features</div>
          <div class="text-xs text-blue-800 space-y-1">
            <div>• Public photo uploads</div>
            <div>• Admin/public separation</div>
            <div>• Thumbnail generation</div>
            <div>• Rate limiting protection</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Public Features -->
  <section id="public-features">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">👥 Public Features</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">🏠 Home Page</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Memorial biography display</div>
            <div>• Recent updates section</div>
            <div>• Navigation to all features</div>
          </div>
        </div>

        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">🖼️ Gallery</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Responsive grid layout</div>
            <div>• Lightbox photo viewing</div>
            <div>• Public + admin content</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="p-3 border rounded bg-blue-50">
          <div class="text-sm font-medium text-blue-900 mb-2">📸 Photo Upload</div>
          <div class="text-xs text-blue-800 space-y-1">
            <div>• Photos/Videos tabs</div>
            <div>• Drag & drop support</div>
            <div>• Auto thumbnail generation</div>
            <div>• Rate limited: 3/minute</div>
            <div>• Max 50MB, JPG/PNG/MP4/MOV</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-yellow-50">
          <div class="text-sm font-medium text-yellow-900 mb-2">💝 Wishes</div>
          <div class="text-xs text-yellow-800 space-y-1">
            <div>• Name and message form</div>
            <div>• Requires admin approval</div>
            <div>• Rate limited: 5/minute</div>
            <div>• Appears after moderation</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Admin Features -->
  <section id="admin-features">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">⚙️ Admin Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">📊 Dashboard</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Stats overview</div>
            <div>• Quick navigation</div>
            <div>• Access via /admin</div>
          </div>
        </div>

        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">✅ Task Tracker</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Create/edit tasks</div>
            <div>• Set status & priority</div>
            <div>• Track progress</div>
          </div>
        </div>

        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">🛡️ Moderation</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Approve/reject wishes</div>
            <div>• Delete inappropriate content</div>
            <div>• Manage user submissions</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Gallery Management -->
  <section id="gallery-management">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">🖼️ Gallery Management</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="p-3 border rounded bg-green-50">
          <div class="text-sm font-medium text-green-900 mb-2">📤 Admin Upload</div>
          <div class="text-xs text-green-800 space-y-1">
            <div>• Via Admin → Gallery</div>
            <div>• Marked as is_public = false</div>
            <div>• Auto thumbnails</div>
            <div>• No rate limits</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-blue-50">
          <div class="text-sm font-medium text-blue-900 mb-2">👥 Public Upload</div>
          <div class="text-xs text-blue-800 space-y-1">
            <div>• Via "Add Photo" button</div>
            <div>• Marked as is_public = true</div>
            <div>• Rate limited</div>
            <div>• Both appear in gallery</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-red-50">
          <div class="text-sm font-medium text-red-900 mb-2">🗑️ Delete</div>
          <div class="text-xs text-red-800 space-y-1">
            <div>• Click "Delete" button</div>
            <div>• Confirmation required</div>
            <div>• Removes original + thumbnails</div>
            <div>• Cannot be undone</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Content Management -->
  <section id="content-management">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">📝 Content Management</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">📰 Updates</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Create via Admin → Updates</div>
            <div>• Add title and content</div>
            <div>• Edit/delete existing</div>
            <div>• Publish immediately</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-yellow-50">
          <div class="text-sm font-medium text-yellow-900 mb-2">💌 Wish Moderation</div>
          <div class="text-xs text-yellow-800 space-y-1">
            <div>• Review in Admin → Wishes</div>
            <div>• Approve to make public</div>
            <div>• Delete inappropriate content</div>
            <div>• Regular review recommended</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Mobile Features -->
  <section id="mobile-features">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">📱 Mobile Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">📱 Navigation</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Hamburger menu</div>
            <div>• Smooth animations</div>
            <div>• Touch-friendly</div>
          </div>
        </div>

        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">🖼️ Gallery Grid</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• 2 cols mobile</div>
            <div>• 3 cols tablet</div>
            <div>• 4 cols desktop</div>
          </div>
        </div>

        <div class="p-3 border rounded">
          <div class="text-sm font-medium mb-2">📤 Upload</div>
          <div class="text-xs text-gray-600 space-y-1">
            <div>• Mobile file picker</div>
            <div>• Drag & drop</div>
            <div>• Progress feedback</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Troubleshooting -->
  <section id="troubleshooting">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">🔧 Troubleshooting</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="p-3 border rounded bg-red-50">
          <div class="text-sm font-medium text-red-900 mb-2">🚫 Images Not Showing</div>
          <div class="text-xs text-red-800 space-y-1">
            <div><strong>Fix:</strong> Run <code class="bg-red-100 px-1 rounded text-xs">php artisan storage:link</code></div>
            <div>• Check file permissions</div>
            <div>• Verify storage directory exists</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-orange-50">
          <div class="text-sm font-medium text-orange-900 mb-2">📤 Upload Problems</div>
          <div class="text-xs text-orange-800 space-y-1">
            <div>• Check file size (<50MB)</div>
            <div>• Verify file type (JPG/PNG/MP4)</div>
            <div>• Wait if rate limited</div>
          </div>
        </div>

        <div class="p-3 border rounded bg-blue-50">
          <div class="text-sm font-medium text-blue-900 mb-2">⚡ Performance</div>
          <div class="text-xs text-blue-800 space-y-1">
            <div>• Clear Laravel caches regularly</div>
            <div>• Monitor storage space</div>
            <div>• Auto thumbnails reduce load</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Version Info -->
  <section id="version-info">
    <div class="p-4 bg-white border rounded">
      <h2 class="text-lg font-medium mb-3">ℹ️ Version Information</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-3 border rounded text-center">
          <div class="text-sm text-gray-500">Version</div>
          <div class="text-lg font-semibold">v0.5.0</div>
        </div>
        <div class="p-3 border rounded text-center">
          <div class="text-sm text-gray-500">Framework</div>
          <div class="text-lg font-semibold">Laravel</div>
        </div>
        <div class="p-3 border rounded text-center">
          <div class="text-sm text-gray-500">Frontend</div>
          <div class="text-lg font-semibold">Tailwind CSS</div>
        </div>
        <div class="p-3 border rounded text-center">
          <div class="text-sm text-gray-500">JavaScript</div>
          <div class="text-lg font-semibold">Alpine.js</div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  html { scroll-behavior: smooth; }
  code { font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 0.75rem; }
</style>
@endsection