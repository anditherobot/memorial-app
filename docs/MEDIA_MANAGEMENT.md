# Media Management System

## Overview

The Media Management system handles image and video uploads, storage, optimization, and display across the Memorial application.

## Core Models

### Media
Primary model for all uploaded files (images, videos, documents).

**Table**: `media`

**Fields**:
- `original_filename` - Original file name
- `mime_type` - MIME type (e.g., `image/jpeg`)
- `size_bytes` - File size in bytes
- `width`, `height` - Image/video dimensions
- `duration_seconds` - Video duration
- `hash` - SHA-256 hash for duplicate detection
- `storage_path` - Path in storage (e.g., `media/originals/{uuid}_{filename}`)
- `is_public` - Whether publicly accessible

### MediaDerivative
Optimized versions of media (thumbnails, web-optimized).

**Table**: `media_derivatives`

**Fields**:
- `media_id` - Foreign key to media
- `type` - Derivative type (`thumbnail`, `web-optimized`)
- `storage_path` - Path to derivative file
- `width`, `height` - Derivative dimensions
- `size_bytes` - Derivative file size

**Derivative Types**:
1. **`thumbnail`** - Grid display (800px width, JPEG 80%)
2. **`web-optimized`** - Full view (1920px max, JPEG 85%)

## Upload Flow

### Admin Gallery Upload

**Route**: `POST /admin/gallery/upload`
**Controller**: `AdminGalleryController::upload()`

**Process**:
1. Validate uploaded files (10MB max, image types)
2. Store original in `storage/app/public/media/originals/{uuid}_{filename}`
3. Extract image dimensions using Intervention Image
4. Create `Media` record
5. Generate derivatives:
   - **Thumbnail**: 800px width, JPEG 80%
   - **Web-optimized**: 1920px max width, JPEG 85%
6. Create `MediaDerivative` records
7. Redirect with success/error toast

### Photo Upload (User-facing)

**Route**: `POST /photos/upload`
**Controller**: `PhotoController::store()`

Uses separate `Photo` model with async processing and status polling.

## Display Components

### `<x-image-thumbnail>`

Displays images in grid/list layouts with automatic optimization.

**Props**:
- `media` (required) - Media model instance
- `gallery` - Lightbox gallery group (default: 'gallery')
- `linkUrl` - Custom full image URL (optional)
- `showInfo` - Show filename/dimensions/size (default: true)

**Features**:
- Shows file size in MB
- Shows "✓ Optimized" badge when web-optimized exists
- Uses thumbnail for grid display
- Uses web-optimized for full view (lightbox)
- Lazy loading (`loading="lazy"`)
- Error handling with placeholder fallback

**Usage**:
```blade
<x-image-thumbnail :media="$image" gallery="memorial" />
```

**With custom actions**:
```blade
<x-image-thumbnail :media="$image" gallery="admin-media">
    <div class="p-2 border-t">
        <button>Delete</button>
    </div>
</x-image-thumbnail>
```

### `<x-image-detail>`

Displays single image with full metadata.

**Props**:
- `media` (required)
- `showMetadata` - Show file info (default: true)
- `showActions` - Show edit/delete buttons (default: false)
- `deleteUrl`, `editUrl` - Action URLs

## Pages Using Media Management

### Upload Pages
1. **`/admin/gallery`** - Admin gallery upload (multiple files)
2. **`/upload`** - Public upload page (photos/videos)
3. **`/photos/upload`** - Authenticated photo upload
4. **`/admin/updates/create`** - Update cover image
5. **`/admin/memorial/events/create`** - Event images

### Display Pages
1. **`/gallery`** - Public gallery (all images)
2. **`/admin/gallery`** - Admin gallery with management
3. **`/admin/updates`** - Updates with cover images
4. **`/admin/memorial/events`** - Events with images

## Optimization Strategy

### Storage Hierarchy
```
storage/app/public/media/
├── originals/              # Original uploaded files (full quality)
│   └── {uuid}_{filename}
└── derivatives/
    └── {media_id}/
        ├── thumb.jpg       # 800px thumbnail
        └── web-optimized.jpg  # 1920px web version
```

### Smart Image Selection

**Grid Display**:
- Prefer: `thumbnail` (800px, ~95KB)
- Fallback: Original

**Full View (Lightbox)**:
- Prefer: `web-optimized` (1920px, ~400-800KB)
- Fallback: Original

**Download/Archive**:
- Always use: Original (stored in `media/originals/`)

### Size Comparison

| Type | Width | Quality | Size | Use Case |
|------|-------|---------|------|----------|
| Original | 3024px | 100% | 4MB | Archive, download |
| Web-optimized | 1920px | 85% | 600KB | Lightbox, detail view |
| Thumbnail | 800px | 80% | 95KB | Grid display |

**Bandwidth Savings**: 85-90% for typical gallery browsing

## Configuration

### `.env` Settings
```env
APP_URL=http://localhost:8000  # Must match server port!
ENABLE_HEIC_SUPPORT=false      # iOS HEIC format support
```

### Upload Limits
- **Max file size**: 10MB (configurable per form)
- **Rate limiting**: 3 uploads per minute (public routes)
- **Accepted formats**: JPEG, PNG, GIF, WEBP, HEIC

## Database Schema

### `media` Table
```sql
CREATE TABLE media (
    id INTEGER PRIMARY KEY,
    original_filename TEXT,
    mime_type TEXT,
    size_bytes INTEGER,
    width INTEGER,
    height INTEGER,
    duration_seconds REAL,
    hash TEXT,
    storage_path TEXT,
    is_public BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### `media_derivatives` Table
```sql
CREATE TABLE media_derivatives (
    id INTEGER PRIMARY KEY,
    media_id INTEGER,
    type TEXT,              -- 'thumbnail', 'web-optimized'
    storage_path TEXT,
    width INTEGER,
    height INTEGER,
    size_bytes INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
);
```

## Error Handling

### Upload Errors
- **File too large**: Validation error with specific file name
- **Invalid format**: Shows accepted formats in error message
- **Processing failure**: Logs error, continues with other files
- **Derivative generation failure**: Logs warning, uses original as fallback

### Display Errors
- **Missing file**: Shows placeholder SVG (`/images/placeholder.svg`)
- **Empty path**: Validates path before URL generation
- **404 response**: `onerror` handler switches to placeholder

### Network Errors
- **Wrong port**: Ensure `APP_URL` matches actual server
- **Missing storage link**: Run `php artisan storage:link`
- **Permission denied**: Check `storage/app/public` permissions (755)

## Testing

### Manual Testing
1. Upload image to `/admin/gallery`
2. Verify thumbnail appears in grid
3. Check file size displays correctly
4. Click image to open lightbox (should load web-optimized)
5. Verify "✓ Optimized" badge shows
6. Check network tab: grid loads ~95KB thumbnails

### Database Verification
```sql
-- Check media and derivatives
SELECT m.id, m.original_filename, m.size_bytes,
       md.type, md.size_bytes as deriv_size
FROM media m
LEFT JOIN media_derivatives md ON m.id = md.media_id
WHERE m.id = 27;
```

### File System Verification
```bash
ls -lh storage/app/public/media/originals/
ls -lh storage/app/public/media/derivatives/27/
```

## Performance

### Grid Load
- **Before optimization**: 27 images × 4MB = 108MB
- **After optimization**: 27 thumbnails × 95KB = 2.5MB
- **Improvement**: 97.7% reduction

### Lightbox Load
- **Before**: 4MB original
- **After**: 600KB web-optimized
- **Improvement**: 85% reduction

### Additional Optimizations
- Lazy loading: Images only load when scrolled into view
- Pagination: 24 images per page (reduces initial load)
- HTTP caching: Browser caches derivatives

## Future Enhancements

### Planned Features
1. **Batch optimization** - CLI command to optimize existing uploads
2. **WebP format** - Modern format support for even smaller sizes
3. **Responsive images** - Multiple sizes for different viewports
4. **CDN integration** - Offload static assets to CDN
5. **Image metadata** - EXIF data display (camera, location, date)
6. **Bulk operations** - Select multiple images for delete/download

### Optimization Ideas
1. **Progressive JPEG** - Better perceived load time
2. **Blur placeholder** - Show blurred preview while loading
3. **Srcset** - Let browser choose optimal size
4. **Background processing** - Queue derivative generation
5. **Duplicate detection** - Use hash to prevent duplicate uploads

## Troubleshooting

### "404 on all images"
- Check `APP_URL` in `.env` includes port (e.g., `:8000`)
- Verify storage symlink: `php artisan storage:link`

### "No thumbnail generated"
- Check PHP GD extension installed
- Verify write permissions: `chmod -R 755 storage/app/public`
- Check logs: `tail -f storage/logs/laravel.log`

### "Original still loads instead of optimized"
- Clear browser cache (Ctrl+F5)
- Check derivative exists: `ls storage/app/public/media/derivatives/{id}/`
- Verify derivative record: `SELECT * FROM media_derivatives WHERE media_id = X`

### "File size not showing"
- Ensure `size_bytes` is set on Media record
- Check component has `showInfo` enabled
- Clear view cache: `php artisan view:clear`

## Summary

The Media Management system provides:
- ✅ Automatic image optimization on upload
- ✅ Multiple derivative sizes for different use cases
- ✅ Original preservation for archival
- ✅ Smart component-based display
- ✅ Comprehensive error handling
- ✅ Performance optimizations (lazy loading, caching)
- ✅ File size transparency for users
- ✅ Single point of truth for image display logic