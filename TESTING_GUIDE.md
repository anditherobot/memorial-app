# Media Optimization Testing Guide

## Prerequisites

1. **Queue Worker Running**
   ```bash
   php artisan queue:work
   ```
   Keep this running in a separate terminal during testing.

2. **Test Images Available**
   - Location: `ui/jar/*.jpg` (22 test images)
   - Use these for consistent testing

## Test Scenario 1: New Upload with Auto-Optimization

### Steps
1. Navigate to `http://localhost:8000/login`
2. Login as admin (`admin@example.com` / `secret`)
3. Go to `/admin/gallery`
4. Click file input and select image from `ui/jar/`
5. Wait for preview to appear
6. Click "Upload" button
7. Wait for page reload

### Expected Results
- ✅ Image appears in gallery grid
- ✅ Toast shows "Image uploaded successfully!"
- ✅ Queue worker logs show job processing
- ✅ After ~2-3 seconds, badge shows "✓ Optimized" (may need refresh)

### Verification
```bash
# Check database
sqlite3 database/database.sqlite "
SELECT m.id, m.original_filename,
       COUNT(CASE WHEN md.type = 'thumbnail' THEN 1 END) as has_thumb,
       COUNT(CASE WHEN md.type = 'web-optimized' THEN 1 END) as has_web
FROM media m
LEFT JOIN media_derivatives md ON m.id = md.media_id
WHERE m.id = (SELECT MAX(id) FROM media)
GROUP BY m.id;
"
```

**Expected Output**:
```
id|original_filename|has_thumb|has_web
28|IMG_9499.jpg|1|1
```

```bash
# Check file system
ls -lh storage/app/public/media/originals/
ls -lh storage/app/public/media/derivatives/28/
```

**Expected Files**:
- `originals/{uuid}_IMG_9499.jpg` (~4MB)
- `derivatives/28/thumb.jpg` (~95KB)
- `derivatives/28/web-optimized.jpg` (~600KB)

---

## Test Scenario 2: Bulk Optimization of Existing Images

### Setup
If you don't have unoptimized images, create some:
```bash
# Delete derivatives but keep originals
sqlite3 database/database.sqlite "DELETE FROM media_derivatives WHERE type IN ('thumbnail', 'web-optimized');"
rm -rf storage/app/public/media/derivatives/*/
```

### Steps
1. Go to `/admin/gallery`
2. Verify images show "✗ Not Optimized" badge
3. Click checkbox on 2-3 images
4. Observe button text: "Optimize 3 Selected (3 need optimization)"
5. Click "Optimize Selected"
6. Button changes to "Optimizing..."
7. Wait for page reload

### Expected Results
- ✅ Page reloads to `/admin/gallery`
- ✅ Toast shows "Optimization job dispatched for 3 image(s)"
- ✅ After jobs process (~5-10 seconds), badges show "✓ Optimized"
- ✅ Checkboxes are unchecked
- ✅ Button resets to "Optimize Selected" (disabled)

### Verification
```bash
# Watch queue worker logs
tail -f storage/logs/laravel.log | grep ProcessImageOptimization

# Check job status
sqlite3 database/database.sqlite "SELECT * FROM jobs;"  # Should be empty when done
sqlite3 database/database.sqlite "SELECT * FROM failed_jobs;"  # Should be empty

# Check derivatives
sqlite3 database/database.sqlite "
SELECT md.media_id, md.type, md.size_bytes, md.width, md.height
FROM media_derivatives md
ORDER BY md.media_id, md.type;
"
```

---

## Test Scenario 3: Mixed Selection (Some Already Optimized)

### Steps
1. Upload 3 new images (these will auto-optimize)
2. Wait for optimization to complete
3. Go to gallery
4. Click "Select All" (selects both optimized and unoptimized)
5. Observe button text

### Expected Results
- ✅ Button shows: "Optimize 5 Selected (2 need optimization)"
- ✅ Click optimize
- ✅ Toast shows: "Optimization job dispatched for 2 image(s)"
- ✅ Already-optimized images are skipped

---

## Test Scenario 4: Select All / Deselect All

### Steps
1. Go to `/admin/gallery`
2. Click "Select All" button
3. Verify all checkboxes are checked
4. Button text changes to "Deselect All"
5. Optimize button shows total count
6. Click "Deselect All"

### Expected Results
- ✅ All checkboxes check/uncheck together
- ✅ Button toggles between "Select All" / "Deselect All"
- ✅ Optimize button enables/disables correctly
- ✅ Count updates dynamically

---

## Test Scenario 5: Lightbox Uses Optimized Version

### Steps
1. Go to `/gallery` (public gallery)
2. Click on an optimized image thumbnail
3. GLightbox opens with full image
4. Open browser DevTools → Network tab
5. Check the image URL loaded

### Expected Results
- ✅ Lightbox opens smoothly
- ✅ Network tab shows URL like `/storage/media/derivatives/X/web-optimized.jpg`
- ✅ File size is ~600KB (not 4MB original)
- ✅ Image quality is good (JPEG 85%)

---

## Test Scenario 6: Error Handling

### Test A: Queue Worker Not Running
1. Stop queue worker (`Ctrl+C`)
2. Upload image or bulk optimize
3. Check behavior

**Expected**:
- ✅ Upload/optimize succeeds (returns to gallery)
- ✅ Jobs sit in `jobs` table waiting
- ❌ No derivatives created immediately
- ✅ Start worker → Jobs process

### Test B: Invalid Image File
1. Try uploading a `.txt` file renamed to `.jpg`

**Expected**:
- ✅ Validation error: "Only image files allowed"
- ✅ No job dispatched

### Test C: Missing Original File
```bash
# Simulate missing file
mv storage/app/public/media/originals/some_image.jpg /tmp/
```

**Expected**:
- ✅ Job logs error: "Original file not found"
- ✅ Job marked as failed
- ✅ Badge shows "✗ Not Optimized"

---

## Test Scenario 7: File Size Verification

### Steps
```bash
# Original
ls -lh storage/app/public/media/originals/{uuid}_IMG_9499.jpg

# Thumbnail
ls -lh storage/app/public/media/derivatives/28/thumb.jpg

# Web-optimized
ls -lh storage/app/public/media/derivatives/28/web-optimized.jpg
```

### Expected Sizes
| Type | Size | Savings |
|------|------|---------|
| Original | ~4MB | - |
| Thumbnail (800px) | ~95KB | 97.6% |
| Web-optimized (1920px) | ~600KB | 85% |

---

## Debugging Commands

### Check Queue Status
```bash
# See pending jobs
sqlite3 database/database.sqlite "SELECT * FROM jobs;"

# See failed jobs
sqlite3 database/database.sqlite "SELECT * FROM failed_jobs;"

# Retry failed jobs
php artisan queue:retry all
```

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filter for optimization
tail -f storage/logs/laravel.log | grep ProcessImageOptimization
```

### Check Database State
```bash
# All media with derivatives
sqlite3 database/database.sqlite "
SELECT m.id, m.original_filename,
       GROUP_CONCAT(md.type) as derivatives,
       m.size_bytes as original_size
FROM media m
LEFT JOIN media_derivatives md ON m.id = md.media_id
GROUP BY m.id;
"
```

### Manual Job Dispatch
```php
php artisan tinker

// Dispatch single job
$media = Media::find(28);
ProcessImageOptimization::dispatch($media);

// Dispatch for all unoptimized
Media::whereDoesntHave('derivatives', function($q) {
    $q->where('type', 'web-optimized');
})->each(function($media) {
    ProcessImageOptimization::dispatch($media);
});

exit
```

---

## Playwright Automated Tests

### Run Tests
```bash
npx playwright test tests/e2e/bulk-optimization.spec.ts --headed
```

### Tests Included
1. ✅ Show optimize button and checkboxes
2. ✅ Enable button when images selected
3. ✅ Update button text for select all
4. ✅ Deselect all functionality
5. ✅ Show optimization badges
6. ✅ Show file sizes
7. ✅ Track optimization status in data attributes
8. ⏭️ Bulk optimize (skipped - requires CSRF handling)
9. ✅ Mobile responsiveness

---

## Success Criteria

### ✅ All Systems Working When:
1. New uploads auto-generate derivatives
2. Bulk optimize processes selected images
3. Already-optimized images are skipped
4. Badges show correct optimization status
5. Button text updates dynamically
6. Lightbox uses web-optimized version
7. File sizes reduced 85-97%
8. Queue worker processes jobs
9. No failed jobs in database
10. Logs show successful completions

### 🎯 Performance Targets
- Upload + optimization: < 5 seconds
- Bulk optimization (5 images): < 15 seconds
- Gallery page load: < 500ms (with thumbnails)
- Lightbox image load: < 1 second (web-optimized)

---

## Common Issues & Fixes

### Issue: Badge doesn't update after optimization
**Fix**: Hard refresh page (Ctrl+F5) or wait for queue to finish

### Issue: "Optimizing..." never completes
**Check**: Is queue worker running? `ps aux | grep queue:work`
**Fix**: Start worker in separate terminal

### Issue: Jobs failing silently
**Check**: `sqlite3 database/database.sqlite "SELECT * FROM failed_jobs;"`
**Fix**: Check logs, fix issue, retry: `php artisan queue:retry all`

### Issue: Original file not found error
**Check**: Does file exist in `storage/app/public/media/originals/`?
**Fix**: Ensure storage symlink: `php artisan storage:link`

---

## Next Steps After Testing

1. ✅ Verify all test scenarios pass
2. ✅ Document any issues found
3. ✅ Update IMAGE_COMPONENTS.md if needed
4. ✅ Run Playwright tests for regression
5. ✅ Consider adding more derivative sizes if needed
6. ✅ Plan for production queue monitoring