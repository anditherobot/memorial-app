# Image Components - Single Point of Truth

## Problem Solved

Previously, image display logic was duplicated across multiple views:
- `/gallery` - Inline thumbnail code with fallbacks
- `/admin/gallery` - Mix of `image-card` component and inline code
- Different URL generation logic
- Different error handling
- **Single point of failure** when updating image display

## Solution: Reusable Components

Created two standardized components for all image display needs:

1. **`x-image-thumbnail`** - For grid/list displays
2. **`x-image-detail`** - For single image detail views

---

## Component 1: Image Thumbnail

**File:** `resources/views/components/image-thumbnail.blade.php`

### Purpose
Display images in grid/list layouts with consistent:
- Thumbnail generation with fallback to full image
- Lazy loading
- Error handling with placeholder
- Lightbox integration
- Optional metadata display

### Props

```php
@props([
    'media',           // Media model instance (required)
    'linkUrl' => null, // Optional: custom link URL (defaults to full image)
    'gallery' => 'gallery', // Gallery group for lightbox
    'showInfo' => true, // Show filename and dimensions
])
```

### Usage Examples

#### Basic Usage
```blade
<x-image-thumbnail :media="$image" />
```

#### Custom Gallery Group
```blade
<x-image-thumbnail :media="$image" gallery="memorial" />
```

#### Hide Metadata
```blade
<x-image-thumbnail :media="$image" :show-info="false" />
```

#### With Custom Actions (Using Slot)
```blade
<x-image-thumbnail :media="$image" gallery="admin-media">
    <div class="p-2 border-t">
        <form method="POST" action="{{ route('media.destroy', $image) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600">Delete</button>
        </form>
    </div>
</x-image-thumbnail>
```

### Features

- ✅ **Automatic Thumbnail**: Uses `media_derivatives` thumbnail if available
- ✅ **Lazy Loading**: `loading="lazy"` for performance
- ✅ **Error Handling**: Falls back to placeholder.svg on error
- ✅ **Lightbox Ready**: Includes `glightbox` class and data attributes
- ✅ **Responsive**: Works in any grid layout
- ✅ **Extensible**: Slot for custom content below image

### HTML Output

```html
<div class="task-card-ui border rounded-lg overflow-hidden bg-white">
    <a href="/storage/media/originals/image.jpg" class="glightbox" data-gallery="gallery">
        <img src="/storage/media/derivatives/1/thumb.jpg"
             alt="image.jpg"
             loading="lazy"
             onerror="this.src='/images/placeholder.svg'; this.onerror=null;"
             class="w-full h-44 object-cover" />
    </a>
    <div class="p-2 text-xs flex items-center justify-between">
        <span class="truncate text-gray-700">image.jpg</span>
        <span class="chip bg-gray-100">1920×1080</span>
    </div>
</div>
```

---

## Component 2: Image Detail

**File:** `resources/views/components/image-detail.blade.php`

### Purpose
Display single image with full metadata and optional actions.

### Props

```php
@props([
    'media',                    // Media model instance (required)
    'showMetadata' => true,     // Show file metadata
    'showActions' => false,     // Show action buttons (edit, delete)
    'deleteUrl' => null,        // URL for delete action
    'editUrl' => null,          // URL for edit action
])
```

### Usage Examples

#### Basic Usage
```blade
<x-image-detail :media="$image" />
```

#### With Actions
```blade
<x-image-detail
    :media="$image"
    :show-actions="true"
    :delete-url="route('media.destroy', $image)"
    :edit-url="route('media.edit', $image)" />
```

#### Metadata Only
```blade
<x-image-detail :media="$image" :show-metadata="true" />
```

### Features

- ✅ **Full Image Display**: Shows original image, not thumbnail
- ✅ **Rich Metadata**: Filename, dimensions, file size, MIME type, upload date
- ✅ **Action Buttons**: Optional edit/delete with confirmation
- ✅ **Error Handling**: Placeholder fallback
- ✅ **Responsive**: Max height to prevent overflow
- ✅ **Extensible**: Slot for custom content

### Metadata Displayed

- Original filename
- Dimensions (width × height)
- File size (formatted in KB/MB)
- File type (extension)
- Upload timestamp

---

## Migration Guide

### Before (Duplicated Code)

**Public Gallery** (`resources/views/gallery/index.blade.php`):
```blade
@foreach($images as $img)
  @php
    $thumb = optional($img->derivatives()->where('type','thumbnail')->first());
    $thumbUrl = $thumb ? Storage::disk('public')->url($thumb->storage_path) : Storage::disk('public')->url($img->storage_path);
    $fullUrl = Storage::disk('public')->url($img->storage_path);
  @endphp
  <div class="task-card-ui border rounded-lg overflow-hidden bg-white">
    <a href="{{ $fullUrl }}" class="glightbox" data-gallery="memorial">
      <img src="{{ $thumbUrl }}"
           alt="{{ $img->original_filename }}"
           loading="lazy"
           onerror="this.src='{{ asset('images/placeholder.svg') }}'; this.onerror=null;"
           class="w-full h-44 object-cover" />
    </a>
    <div class="p-2 text-xs flex items-center justify-between">
      <span class="truncate text-gray-700">{{ $img->original_filename }}</span>
      @if($img->width && $img->height)
        <span class="chip bg-gray-100">{{ $img->width }}×{{ $img->height }}</span>
      @endif
    </div>
  </div>
@endforeach
```

### After (Using Component)

**Public Gallery**:
```blade
@foreach($images as $img)
  <x-image-thumbnail :media="$img" gallery="memorial" />
@endforeach
```

**Reduction:**
- From **20+ lines** to **1 line** per image
- No inline PHP logic
- No duplicate URL generation
- Consistent across all galleries

---

## Where Components Are Used

### Public Gallery (`/gallery`)
```blade
<x-image-thumbnail :media="$img" gallery="memorial" />
```

### Admin Gallery (`/admin/gallery`)
```blade
<x-image-thumbnail :media="$img" gallery="admin-media">
    <div class="p-2 border-t">
        <form method="POST" action="{{ route('admin.media.destroy', $img) }}">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600">Delete</button>
        </form>
    </div>
</x-image-thumbnail>
```

### Future Use Cases
- User photo uploads
- Event poster images
- Profile pictures
- Any image display throughout the app

---

## Benefits

### 1. Single Point of Truth
- **One component** handles all thumbnail logic
- Change once, updates everywhere
- No more hunting through views for duplicate code

### 2. Consistent Behavior
- Same lazy loading everywhere
- Same error handling everywhere
- Same URL generation logic

### 3. Easy Maintenance
- Bug fixes in one place
- Feature additions in one place
- Testing in one place

### 4. Better Developer Experience
- Clear, declarative syntax
- Self-documenting code
- Less cognitive load

### 5. Performance
- Built-in lazy loading
- Optimized thumbnail usage
- Error handling prevents broken images

---

## Technical Details

### Thumbnail Logic

1. **Check for thumbnail derivative**:
   ```php
   $thumb = optional($media->derivatives()->where('type','thumbnail')->first());
   ```

2. **Use thumbnail if exists, otherwise use original**:
   ```php
   $thumbnailUrl = $thumb
       ? Storage::disk('public')->url($thumb->storage_path)
       : Storage::disk('public')->url($media->storage_path);
   ```

3. **Fallback to placeholder on error**:
   ```html
   onerror="this.src='/images/placeholder.svg'; this.onerror=null;"
   ```

### Storage Integration

Uses Laravel Storage facade consistently:
```php
Storage::disk('public')->url($path)
```

Benefits:
- Works with different storage drivers
- Handles symlinks automatically
- Consistent URL generation

### Lightbox Integration

Built-in GLightbox support:
```html
<a href="..." class="glightbox" data-gallery="gallery-name">
```

Groups images by `gallery` prop for navigation.

---

## Testing

### Manual Testing Checklist

- [ ] Public gallery displays images correctly
- [ ] Admin gallery displays images correctly
- [ ] Thumbnails load properly
- [ ] Full images open in lightbox
- [ ] Lazy loading works (check network tab)
- [ ] Missing images show placeholder
- [ ] Delete buttons work in admin gallery
- [ ] Metadata displays correctly
- [ ] Responsive on mobile

### Automated Testing

Add component tests:
```php
public function test_image_thumbnail_component_renders()
{
    $media = Media::factory()->create([
        'mime_type' => 'image/jpeg',
        'original_filename' => 'test.jpg',
        'width' => 800,
        'height' => 600,
    ]);

    $view = $this->blade('<x-image-thumbnail :media="$media" />', ['media' => $media]);

    $view->assertSee('test.jpg');
    $view->assertSee('800×600');
}
```

---

## Future Enhancements

### Possible Additions

1. **Hover Actions**
   - Quick view button
   - Share button
   - Favorite button

2. **Loading States**
   - Skeleton loader while image loads
   - Progress indicator

3. **Advanced Metadata**
   - EXIF data display
   - GPS coordinates (for photos)
   - Camera info

4. **Accessibility**
   - Better ARIA labels
   - Keyboard navigation
   - Screen reader support

5. **Performance**
   - Srcset for responsive images
   - WebP format support
   - Progressive loading

---

## Troubleshooting

### Images Not Showing

1. **Check storage symlink**:
   ```bash
   ls -la public/storage
   php artisan storage:link
   ```

2. **Check file exists**:
   ```bash
   ls storage/app/public/media/originals/
   ```

3. **Check permissions**:
   ```bash
   chmod -R 755 storage/app/public
   ```

### Placeholder Always Shows

1. **Check image path in database**
2. **Verify Storage::disk('public')->url() returns correct URL**
3. **Check browser console for 404 errors**

### Lightbox Not Working

1. **Ensure GLightbox is loaded**:
   ```javascript
   if (typeof GLightbox !== 'undefined') {
       GLightbox({ selector: '.glightbox' });
   }
   ```

2. **Check gallery attribute matches**
3. **Verify href points to full image**

---

## Conclusion

These components provide a **single, reliable, maintainable** way to display images throughout the memorial application.

**Key Takeaway:** When you need to display an image, use one of these components. Don't recreate the logic inline.

**Next Steps:**
- Use these components in all new views
- Gradually migrate old inline image code
- Extend with new features as needed