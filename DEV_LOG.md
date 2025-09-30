# Development Log

## 2025-09-30: Fixed Image 404 Errors

### Issue
Network tab showed 404 errors for all thumbnail images:
- `http://localhost/storage/media/derivatives/*/thumb.jpg` → 404 Not Found
- Browser was requesting port 80 instead of port 8000

### Root Causes

1. **Seeded Media Records with Invalid Paths**
   - 22 seeded media records had incorrect storage paths
   - 12 records: `gallery/sample*.svg` (mapped to non-existent `/storage/gallery/`)
   - 10 records: `media/{uuid}.jpg` (without `originals/` subdirectory)
   - These paths didn't match actual file locations

2. **APP_URL Missing Port Number**
   - `.env` had `APP_URL=http://localhost` (without `:8000`)
   - Laravel's `Storage::disk('public')->url()` used APP_URL to generate URLs
   - Generated URLs: `http://localhost/storage/...` (port 80)
   - Actual server: `http://localhost:8000/...` (port 8000)
   - Result: All storage URLs returned 404

### Fixes Applied

1. **Cleaned up invalid seeded data**
   ```sql
   DELETE FROM media_derivatives
   WHERE media_id IN (SELECT id FROM media WHERE storage_path NOT LIKE '%originals%');

   DELETE FROM media WHERE storage_path NOT LIKE '%originals%';
   ```
   - Before: 27 media records (22 invalid, 5 valid)
   - After: 5 media records (all valid)

2. **Deleted orphaned derivative folders**
   ```bash
   rm -rf storage/app/public/media/derivatives/1
   rm -rf storage/app/public/media/derivatives/2
   ```

3. **Fixed APP_URL in .env**
   ```diff
   - APP_URL=http://localhost
   + APP_URL=http://localhost:8000
   ```

### Verification

All thumbnail URLs now work correctly:
```bash
curl -I http://localhost:8000/storage/media/derivatives/27/thumb.jpg
# HTTP/1.1 200 OK ✓
```

### Files Modified
- `.env` - Added port to APP_URL
- `database/database.sqlite` - Removed invalid media records

### Lesson Learned
Always ensure `APP_URL` in `.env` matches the actual server URL including port number. Laravel uses this for generating asset URLs via `Storage::url()` and `asset()` helpers.

---

## 2025-09-30: Media Management - Web-Optimized Images

### Feature: Web-Optimized Image Derivatives

Added automatic web optimization for uploaded images to reduce bandwidth and improve load times while preserving originals.

### Implementation

**1. File Size Display**
- `image-thumbnail.blade.php` now shows file size in MB
- Shows "✓ Optimized" badge when web-optimized version exists

**2. New Derivative Type: `web-optimized`**
- Maximum width: 1920px (maintains aspect ratio)
- Quality: JPEG 85%
- Stored in: `media/derivatives/{id}/web-optimized.jpg`

**3. Automatic Generation on Upload**
- `AdminGalleryController::upload()` now generates two derivatives:
  - **Thumbnail**: 800px width, JPEG 80% (for grid display)
  - **Web-optimized**: 1920px max width, JPEG 85% (for full view)

**4. Smart URL Selection**
- Thumbnail URLs: Use `thumbnail` derivative (or original if missing)
- Full image URLs: Use `web-optimized` derivative (or original if missing)
- Original files remain stored for archival/high-quality downloads

### Benefits

- **Reduced Bandwidth**: Web-optimized images are typically 60-80% smaller than originals
- **Faster Load Times**: Lightbox opens optimized version instead of multi-MB original
- **Original Preserved**: Can always access full-quality original from storage
- **Transparent**: Component automatically uses best available version

### Example

Original image: 4MB (3958604 bytes)
- Thumbnail: ~95KB (for grid display)
- Web-optimized: ~400-800KB (for full view)
- Original: Kept in `media/originals/` for archival

### Files Modified

- `resources/views/components/image-thumbnail.blade.php` - Added file size display and optimization badge
- `app/Http\Controllers\AdminGalleryController.php` - Generate web-optimized derivative on upload
- `app/Models/MediaDerivative.php` - Supports new `web-optimized` type

---

## 2025-09-30: Media Management - Bulk Image Optimization

### Feature: On-Demand Bulk Optimization

Added ability to optimize existing images that don't have web-optimized versions yet.

### Implementation

**1. Selectable Image Thumbnails**
- Added `selectable` prop to `<x-image-thumbnail>` component
- Shows checkbox in top-left corner when enabled
- Checkbox tracks media ID and optimization status

**2. Bulk Actions UI**
- "Select All / Deselect All" button
- "Optimize Selected" button (disabled when nothing selected)
- Button shows count of selected images and how many need optimization
- Example: "Optimize 5 Selected (5 need optimization)"

**3. Optimization Endpoint**
- Route: `POST /admin/gallery/optimize`
- Controller: `AdminGalleryController::optimize()`
- Validates media IDs exist
- Skips already-optimized images
- Generates both thumbnail and web-optimized if missing
- Returns detailed feedback (optimized, skipped, failed counts)

**4. Smart Button Text**
- Updates dynamically based on selection
- Shows how many images actually need optimization
- Changes to "Optimizing..." during processing
- Disabled when no selection

### User Flow

1. Admin goes to `/admin/gallery`
2. Each image shows a checkbox in top-left corner
3. Click "Select All" or manually select images
4. Button updates: "Optimize 5 Selected (5 need optimization)"
5. Click "Optimize Selected"
6. Button shows "Optimizing..."
7. Page reloads with success message
8. Selected images now show "✓ Optimized" badge

### Backend Logic

```php
public function optimize(Request $request)
{
    // For each selected media:
    // 1. Check if already has web-optimized (skip if yes)
    // 2. Read original file from storage
    // 3. Generate thumbnail if missing (800px, JPEG 80%)
    // 4. Generate web-optimized (1920px max, JPEG 85%)
    // 5. Save derivatives to database
    // Returns: "Optimized X image(s). Skipped Y (already optimized)"
}
```

### Use Cases

**Existing Uploads**
- Images uploaded before optimization feature was added
- Can now be optimized retroactively in bulk

**Failed Optimizations**
- If upload optimization failed (e.g., memory limit)
- Can retry optimization without re-uploading

**Selective Optimization**
- Choose specific images to optimize
- Don't optimize everything at once (resource management)

### Files Modified

- `resources/views/components/image-thumbnail.blade.php` - Added `selectable` prop and checkbox
- `resources/views/admin/gallery.blade.php` - Added bulk action UI and JavaScript
- `app/Http/Controllers/AdminGalleryController.php` - Added `optimize()` method
- `routes/web.php` - Added `admin.gallery.optimize` route