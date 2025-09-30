# Image Optimization Flow

## How It Works

### 1. Upload Flow

```
User uploads image (e.g., 4MB original)
         ↓
AdminGalleryController::upload()
         ↓
Saves original to storage/app/public/media/originals/
         ↓
Creates Media record in database
         ↓
Dispatches ProcessImageOptimization job to queue
         ↓
Returns to gallery (shows "✗ Not Optimized")
```

### 2. Background Optimization (Queue Worker)

```
Queue worker picks up job
         ↓
ProcessImageOptimization::handle()
         ↓
Reads original from storage
         ↓
Creates Thumbnail (800px width)
  • Starts at 80% quality
  • Reduces by 5% until under 200KB
  • Minimum quality: 50%
  • Saves to: media/derivatives/{id}/thumb.jpg
         ↓
Creates Web-Optimized (1920px max width)
  • Starts at 85% quality
  • Reduces by 5% until under 2MB
  • Minimum quality: 60%
  • Saves to: media/derivatives/{id}/web-optimized.jpg
         ↓
Creates MediaDerivative records in database
```

### 3. UI Display (After Refresh)

**Before Optimization:**
```
┌─────────────────────────┐
│  [Image Thumbnail]      │
│                         │
│ filename.jpg  3000×2000 │
│ 4.12 MB  ✗ Not Optimized│
└─────────────────────────┘
```

**After Optimization:**
```
┌─────────────────────────┐
│  [Image Thumbnail]      │
│                         │
│ filename.jpg  3000×2000 │
│ 4.12 MB (crossed out)   │
│ 0.65 MB  ✓ Optimized    │
│ Saved 84% (Thumb: 95 KB)│
└─────────────────────────┘
```

## File Size Examples

### Example 1: Large Photo (4000×3000, 4.2MB original)

| Type | Dimensions | Quality | Size | Savings |
|------|------------|---------|------|---------|
| **Original** | 4000×3000 | 100% | 4.2 MB | - |
| **Thumbnail** | 800×600 | 65% | 95 KB | 97.7% |
| **Web-optimized** | 1920×1440 | 75% | 650 KB | 84.5% |

### Example 2: Medium Photo (2000×1500, 1.8MB original)

| Type | Dimensions | Quality | Size | Savings |
|------|------------|---------|------|---------|
| **Original** | 2000×1500 | 100% | 1.8 MB | - |
| **Thumbnail** | 800×600 | 75% | 82 KB | 95.4% |
| **Web-optimized** | 1920×1440 | 85% | 480 KB | 73.3% |

### Example 3: Small Photo (1000×800, 500KB original)

| Type | Dimensions | Quality | Size | Savings |
|------|------------|---------|------|---------|
| **Original** | 1000×800 | 100% | 500 KB | - |
| **Thumbnail** | 800×640 | 80% | 65 KB | 87.0% |
| **Web-optimized** | 1000×800 | 85% | 180 KB | 64.0% |

*Note: Web-optimized doesn't upscale, keeps original dimensions if smaller than 1920px*

## Bulk Optimization Flow

```
User selects multiple images
         ↓
Clicks "Optimize Selected"
         ↓
AJAX POST to /admin/gallery/optimize
         ↓
AdminGalleryController::optimize()
  • Validates media IDs
  • Checks if already optimized
  • Dispatches jobs for unoptimized images
         ↓
Returns JSON: {"success": true, "dispatched": 5}
         ↓
JavaScript reloads page
         ↓
Queue worker processes jobs in background
         ↓
After refresh: UI shows new sizes and badges
```

## What the UI Shows

### File Size Display

**Unoptimized Image:**
- Shows original file size only
- Yellow badge: "✗ Not Optimized"

**Optimized Image:**
- Shows ~~original size~~ (crossed out)
- Shows new optimized size in green
- Green badge: "✓ Optimized"
- Shows percentage saved
- Shows thumbnail size in KB

### Example UI Text

**Before:**
```
4.12 MB  ✗ Not Optimized
```

**After:**
```
4.12 MB  (crossed out)
0.65 MB  ✓ Optimized
Saved 84% (Thumb: 95 KB)
```

## Size Guarantees

### Thumbnails (Grid Display)
- **Target:** Under 200KB
- **Dimensions:** 800px width (maintains aspect ratio)
- **Format:** JPEG
- **Quality:** 50-80% (dynamic adjustment)
- **Use case:** Fast loading grid gallery

### Web-Optimized (Full View / Lightbox)
- **Target:** Under 2MB
- **Dimensions:** Max 1920px width (maintains aspect ratio)
- **Format:** JPEG
- **Quality:** 60-85% (dynamic adjustment)
- **Use case:** High-quality full-size viewing

### Original (Archival)
- **Preserved:** Always kept in originals/ folder
- **Use case:** Downloads, high-res prints, re-processing

## Dynamic Quality Adjustment

The optimization job automatically adjusts JPEG quality to meet size targets:

```php
// Thumbnail: Target under 200KB
$quality = 80;  // Start optimistic
do {
    $data = compress($image, $quality);
    if (filesize($data) <= 200KB || $quality <= 50) {
        save($data);
        break;
    }
    $quality -= 5;  // Reduce quality
} while ($quality >= 50);
```

This ensures:
- ✅ Files always meet size targets
- ✅ Best possible quality within size limit
- ✅ No manual quality tuning needed
- ✅ Works with any image complexity

## Browser Benefits

### Grid View
- Loads thumbnails (~95KB each)
- Fast page load even with 100+ images
- Smooth scrolling

### Lightbox / Full View
- Loads web-optimized (~650KB)
- Quick lightbox open
- Good quality for screen viewing
- No need to download 4MB originals

### Bandwidth Savings
- 100 images × 4MB = 400MB (originals)
- 100 images × 95KB = 9.5MB (thumbnails)
- **97.6% bandwidth reduction!**

## Queue Worker

Must be running for optimization to work:

```bash
# Start queue worker
php artisan queue:work

# Or in production with supervisor
php artisan queue:work --tries=3 --timeout=60
```

When queue worker is running, you'll see:
```
[2025-09-30 18:11:59] App\Jobs\ProcessImageOptimization .... DONE
```

## Checking Results

### View in Browser
1. Go to `/admin/gallery`
2. Look for file size changes
3. Check for "✓ Optimized" badges
4. See percentage saved

### Check Database
```sql
SELECT
    m.id,
    m.original_filename,
    m.size_bytes as original_size,
    COUNT(md.id) as derivative_count,
    GROUP_CONCAT(md.type) as types
FROM media m
LEFT JOIN media_derivatives md ON m.id = md.media_id
GROUP BY m.id;
```

### Check Files
```bash
# Original
ls -lh storage/app/public/media/originals/

# Derivatives
ls -lh storage/app/public/media/derivatives/*/
```

## Summary

**Optimization happens automatically:**
1. Upload triggers optimization job
2. Queue worker processes in background
3. Creates 2 derivatives (thumbnail + web-optimized)
4. UI shows before/after sizes
5. Gallery loads optimized versions
6. Original preserved for downloads

**User sees:**
- ~~4.12 MB~~ → 0.65 MB (84% smaller)
- Fast loading thumbnails
- Quick lightbox opens
- Bandwidth savings
- Clear optimization status
