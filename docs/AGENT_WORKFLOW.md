# Agent Workflow Guide — Memorial Project

**Last Updated:** 2025-09-30
**Purpose:** Define best practices and workflows for AI agents working on this Laravel + Tailwind memorial application.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Core Workflow Principles](#core-workflow-principles)
3. [Standard Workflows](#standard-workflows)
4. [Testing Strategy](#testing-strategy)
5. [Documentation Standards](#documentation-standards)
6. [Common Patterns](#common-patterns)
7. [Tools & Commands](#tools--commands)

---

## Project Overview

**Stack:**
- Laravel 11 (PHP 8.3)
- Tailwind CSS 3
- Alpine.js (minimal)
- SQLite (development)
- Playwright (E2E testing)
- Intervention Image (image processing)

**Key Features:**
- Public memorial page (gallery, wishes, updates, events)
- Admin panel (content management, photo upload, moderation)
- Photo upload with preview, progress tracking, and thumbnail generation
- Responsive design (desktop 1280×800, mobile 390×844)

**File Structure:**
```
memorial/
├── app/
│   ├── Http/Controllers/      # Route controllers
│   ├── Models/                # Eloquent models
│   └── Jobs/                  # Background jobs
├── resources/
│   ├── views/                 # Blade templates
│   │   ├── components/        # Reusable components
│   │   ├── admin/             # Admin pages
│   │   └── layouts/           # Layout templates
│   └── css/                   # Tailwind styles
├── tests/
│   ├── Feature/               # Feature tests (PHPUnit)
│   ├── Unit/                  # Unit tests (PHPUnit)
│   └── e2e/                   # E2E tests (Playwright)
├── public/
│   ├── images/                # Static images
│   └── storage/               # Symlink to storage
└── storage/
    └── app/public/            # User uploads
```

---

## Core Workflow Principles

### 1. **Use Todo Lists for Multi-Step Tasks**

**When to use TodoWrite:**
- Tasks with 3+ distinct steps
- Complex, non-trivial implementations
- User provides multiple tasks
- When tracking progress is helpful

**Example:**
```javascript
[
  {"content": "Create controller method", "status": "completed", "activeForm": "Creating controller method"},
  {"content": "Add route definition", "status": "in_progress", "activeForm": "Adding route definition"},
  {"content": "Create blade view", "status": "pending", "activeForm": "Creating blade view"},
  {"content": "Add validation", "status": "pending", "activeForm": "Adding validation"},
  {"content": "Test the feature", "status": "pending", "activeForm": "Testing the feature"}
]
```

**Best Practices:**
- Mark tasks as `in_progress` BEFORE starting work
- Mark as `completed` IMMEDIATELY after finishing
- Only ONE task should be `in_progress` at a time
- Don't batch completions - update in real-time
- Remove stale/irrelevant tasks

### 2. **Read Before Edit**

**Always read files before editing them:**
```javascript
// ✅ Correct
Read('app/Models/Photo.php')
Edit('app/Models/Photo.php', old_string, new_string)

// ❌ Wrong
Edit('app/Models/Photo.php', old_string, new_string) // Will fail
```

### 3. **Prefer Editing Over Writing New Files**

- Use `Edit` for existing files whenever possible
- Only use `Write` for genuinely new files
- Check if a similar file exists first

### 4. **Test Changes**

After implementing features:
1. Run relevant PHPUnit tests: `php artisan test --filter=TestName`
2. For UI changes, consider Playwright tests
3. Manual browser testing when appropriate
4. Document test results

### 5. **Document As You Go**

- Update relevant `.md` files when adding features
- Create analysis documents for complex changes
- Write implementation summaries for major features

---

## Standard Workflows

### Workflow 1: Adding a New Feature

**Steps:**

1. **Create Todo List**
   ```
   - Analyze requirements
   - Design solution
   - Implement backend
   - Implement frontend
   - Add tests
   - Document feature
   ```

2. **Understand Context**
   - Read related models: `Read('app/Models/...')`
   - Check existing controllers: `Grep('class.*Controller', path: 'app/Http/Controllers')`
   - Review routes: `Read('routes/web.php')`
   - Check similar components: `Glob('resources/views/components/*.blade.php')`

3. **Implement Backend**
   - Model changes (if needed)
   - Controller method
   - Route definition
   - Validation rules
   - Error handling

4. **Implement Frontend**
   - Blade view/component
   - Tailwind styling
   - JavaScript (if needed)
   - Form handling

5. **Add Tests**
   - Feature tests for HTTP routes
   - Unit tests for models/logic
   - E2E tests for critical user flows

6. **Document**
   - Update relevant docs
   - Add code comments
   - Create summary if complex

**Example: Adding Photo Upload Feature**
```markdown
1. Analyze: Review upload requirements, file types, size limits
2. Backend: Update controller, add validation, error handling
3. Frontend: Create upload-form component with preview/progress
4. Tests: Add feature test, E2E test with Playwright
5. Document: Create PHOTO_UPLOAD_ANALYSIS.md and IMPLEMENTATION_SUMMARY.md
```

### Workflow 2: Fixing a Bug

**Steps:**

1. **Reproduce the Bug**
   - Read relevant code
   - Check logs
   - Understand the flow

2. **Identify Root Cause**
   - Use Grep to find related code
   - Read models and controllers
   - Check database interactions

3. **Implement Fix**
   - Make minimal changes
   - Add error handling
   - Consider edge cases

4. **Add Regression Test**
   - Write test that would have caught the bug
   - Ensure test fails before fix, passes after

5. **Verify Fix**
   - Run tests
   - Manual verification
   - Check for side effects

**Example: Storage 404 Errors**
```markdown
1. Issue: Images returning 403/404
2. Root cause: Storage::disk()->url() returning invalid paths
3. Fix: Add URL accessors with file existence checking
4. Test: Added test_gallery_storage_urls_return_404_when_files_missing()
5. Fallback: Created placeholder.svg for missing images
```

### Workflow 3: Refactoring Code

**Steps:**

1. **Understand Current Implementation**
   - Read all related files
   - Document current behavior
   - Identify pain points

2. **Design Improvement**
   - Plan new structure
   - Consider backwards compatibility
   - Identify breaking changes

3. **Implement Gradually**
   - Make small, testable changes
   - Run tests after each step
   - Keep git history clean

4. **Update Tests**
   - Modify tests to match new structure
   - Add tests for new behavior
   - Remove obsolete tests

5. **Document Changes**
   - Update inline comments
   - Update documentation
   - Create migration guide if needed

### Workflow 4: Adding Tests

**Steps:**

1. **Identify What to Test**
   - Critical user flows
   - Business logic
   - Error handling
   - Edge cases

2. **Choose Test Type**
   - **Unit tests** - Models, services, utilities
   - **Feature tests** - HTTP requests, controllers
   - **E2E tests** - Full user flows, UI interactions

3. **Write Tests**
   ```php
   // Feature Test
   public function test_user_can_upload_photo(): void
   {
       $response = $this->actingAs($admin)
           ->post('/admin/gallery/upload', [
               'photos' => [UploadedFile::fake()->image('test.jpg')]
           ]);

       $response->assertRedirect()->assertSessionHas('status');
   }
   ```

   ```typescript
   // E2E Test
   test('should upload photo with progress bar', async ({ page }) => {
       await page.goto('/admin/gallery');
       await page.setInputFiles('input[type="file"]', 'test.jpg');
       await page.click('button[type="submit"]');
       await expect(page.locator('#progressBar')).toBeVisible();
   });
   ```

4. **Run Tests**
   - PHPUnit: `php artisan test --filter=TestName`
   - Playwright: `npx playwright test tests/e2e/test-name.spec.ts`

5. **Document Test Coverage**
   - Add to test documentation
   - Update test matrix

---

## Testing Strategy

### PHPUnit Tests (Backend)

**Location:** `tests/Feature/` and `tests/Unit/`

**Run Commands:**
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/GalleryTest.php

# Specific test method
php artisan test --filter=test_gallery_shows_samples_when_empty

# With coverage
php artisan test --coverage
```

**Best Practices:**
- Use `RefreshDatabase` trait for database tests
- Use `Storage::fake()` for file upload tests
- Test both success and failure cases
- Test validation rules
- Test authorization/permissions

**Example:**
```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_upload_validates_file_size(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($admin)
            ->post('/admin/gallery/upload', [
                'photos' => [
                    UploadedFile::fake()->create('huge.jpg', 20000) // > 10MB
                ]
            ]);

        $response->assertSessionHasErrors('photos.0');
    }
}
```

### Playwright Tests (E2E)

**Location:** `tests/e2e/`

**Run Commands:**
```bash
# All E2E tests
npx playwright test

# Specific test
npx playwright test tests/e2e/photo-upload.spec.ts

# Specific project
npx playwright test --project=chromium-desktop

# With UI
npx playwright test --ui

# Debug mode
npx playwright test --debug
```

**Best Practices:**
- Test critical user flows end-to-end
- Use page objects for complex interactions
- Take screenshots on failure
- Test both desktop and mobile viewports
- Use data-testid for stable selectors

**Example:**
```typescript
test('photo upload with preview', async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await page.click('button[type="submit"]');

    // Upload
    await page.goto('/admin/gallery');
    await page.setInputFiles('input[type="file"]', 'test-image.jpg');

    // Verify preview
    await expect(page.locator('#previewArea')).toBeVisible();

    // Submit
    await page.click('button:has-text("Upload")');

    // Verify success
    await expect(page.locator('#successToast')).toBeVisible();
});
```

### Visual Regression Tests

**See:** `docs/AGENT_PLAYBOOK.visual-ui.md`

**Quick Commands:**
```bash
# Run visual tests
npm run ui:check

# Update baselines
npm run ui:update

# View report
open playwright-report/index.html
```

---

## Documentation Standards

### When to Document

1. **New Features** - Always create documentation
2. **Complex Changes** - Write analysis + implementation summary
3. **Bug Fixes** - Document root cause and solution
4. **Refactoring** - Explain why and what changed
5. **Breaking Changes** - Migration guide required

### Documentation Types

#### 1. Analysis Documents
**Purpose:** Deep dive into a problem before solving it
**Format:** `FEATURE_ANALYSIS.md`

**Sections:**
- Current state assessment
- Problems identified
- Proposed solutions
- Implementation plan
- Expected outcomes

**Example:** `PHOTO_UPLOAD_ANALYSIS.md`

#### 2. Implementation Summaries
**Purpose:** Record what was done and how
**Format:** `IMPLEMENTATION_SUMMARY.md` or inline in code

**Sections:**
- What was implemented
- Files changed
- Before/after comparison
- Testing performed
- Usage examples

**Example:** `IMPLEMENTATION_SUMMARY.md`

#### 3. Playbooks
**Purpose:** Step-by-step guides for agents
**Format:** `AGENT_PLAYBOOK.feature-name.md`

**Sections:**
- Objective
- Prerequisites
- Commands
- Standard procedure
- Troubleshooting

**Example:** `AGENT_PLAYBOOK.visual-ui.md`

#### 4. Inline Code Documentation

**Blade Components:**
```blade
{{--
  Upload Form Component

  Features:
  - File preview with thumbnails
  - Progress bar
  - Drag & drop support
  - Client-side validation

  Usage:
  <x-upload-form
      :action="route('upload')"
      input-name="photos"
      :max-file-size="10485760" />
--}}
```

**PHP Methods:**
```php
/**
 * Upload photos with progress tracking and error handling.
 *
 * Validates file size (max 10MB) and type (images only).
 * Generates thumbnails automatically.
 * Returns per-file error messages on failure.
 *
 * @param Request $request
 * @return RedirectResponse
 */
public function upload(Request $request): RedirectResponse
```

---

## Common Patterns

### Pattern 1: Blade Component with Props

**Structure:**
```blade
@props([
    'action',
    'title' => 'Default Title',
    'inputName' => 'file',
    'maxFileSize' => 10485760, // 10MB
])

<div>
    <!-- Component markup -->
    {{ $slot }}
</div>

<script>
// Component JavaScript
(function() {
    // Scoped to avoid global pollution
})();
</script>
```

**Usage:**
```blade
<x-upload-form
    :action="route('admin.gallery.upload')"
    title="Upload Photos"
    input-name="photos" />
```

### Pattern 2: Controller with Error Handling

**Structure:**
```php
public function upload(Request $request)
{
    // 1. Validate
    $validated = $request->validate([
        'photos' => ['required', 'array'],
        'photos.*' => ['required', 'file', 'max:10240', 'mimes:jpeg,png'],
    ], [
        'photos.*.max' => 'Each file must not exceed 10MB.',
    ]);

    // 2. Track results
    $successCount = 0;
    $failedFiles = [];

    // 3. Process with error handling
    foreach ($validated['photos'] as $file) {
        try {
            // Process file
            $successCount++;
        } catch (\Throwable $e) {
            \Log::error('Upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            $failedFiles[] = $file->getClientOriginalName();
        }
    }

    // 4. Provide detailed feedback
    if ($successCount > 0 && count($failedFiles) === 0) {
        return redirect()->back()->with('status', 'All uploads successful!');
    } elseif ($successCount > 0) {
        return redirect()->back()->with('warning', "Uploaded {$successCount}. Failed: " . implode(', ', $failedFiles));
    } else {
        return redirect()->back()->with('error', 'All uploads failed.');
    }
}
```

### Pattern 3: Model with URL Accessors

**Structure:**
```php
class Photo extends Model
{
    protected $appends = ['thumbnail_url', 'display_url'];

    public function getThumbnailUrlAttribute(): string
    {
        $path = $this->variants['thumbnail'] ?? $this->display_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/placeholder.svg');
    }

    public function fileExists(): bool
    {
        return $this->original_path
            && Storage::disk('public')->exists($this->original_path);
    }
}
```

**Usage:**
```blade
<img src="{{ $photo->thumbnail_url }}"
     loading="lazy"
     onerror="this.src='{{ asset('images/placeholder.svg') }}'"
     alt="{{ $photo->uuid }}" />
```

### Pattern 4: Toast Notifications

**In Blade:**
```blade
@if(session('status'))
  <div id="successToast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('status') }}
  </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('successToast');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
});
</script>
```

**In Controller:**
```php
return redirect()->back()->with('status', 'Success message');
return redirect()->back()->with('warning', 'Warning message');
return redirect()->back()->with('error', 'Error message');
```

### Pattern 5: JavaScript Upload with Progress

**Structure:**
```javascript
async function handleUpload(files) {
    const formData = new FormData();
    files.forEach(file => formData.append('photos[]', file));

    const xhr = new XMLHttpRequest();

    // Progress tracking
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            updateProgressBar(percent);
        }
    });

    // Success/error handling
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            window.location.reload();
        } else {
            showError('Upload failed');
        }
    });

    xhr.open('POST', '/upload');
    xhr.send(formData);
}
```

---

## Tools & Commands

### Essential Laravel Commands

```bash
# Development server
php artisan serve

# Run migrations
php artisan migrate
php artisan migrate:fresh --seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Create components
php artisan make:controller AdminGalleryController
php artisan make:model Photo -mf
php artisan make:component UploadForm

# Run tests
php artisan test
php artisan test --filter=PhotoUploadTest
php artisan test --coverage

# Queue work
php artisan queue:work
php artisan queue:listen

# Storage
php artisan storage:link
```

### Essential Node/NPM Commands

```bash
# Install dependencies
npm ci                    # Clean install
npm install              # Regular install

# Development
npm run dev              # Watch mode
npm run build            # Production build

# Testing
npx playwright test                          # All tests
npx playwright test --ui                     # Interactive UI
npx playwright test tests/e2e/photo.spec.ts  # Specific test
npx playwright test --project=chromium-desktop

# Visual regression
npm run ui:check         # Run visual tests
npm run ui:update        # Update baselines
```

### Git Workflow

```bash
# Feature branch
git checkout -b feature/photo-upload-improvements

# Commit with meaningful messages
git add .
git commit -m "feat: add photo upload preview and progress bar

- Add file preview with thumbnails
- Add progress bar with real-time updates
- Add client-side validation
- Add drag-and-drop support
- Fix storage 404 errors with fallback placeholders"

# Push
git push origin feature/photo-upload-improvements
```

### Useful Grep Patterns

```bash
# Find controllers
Grep('class.*Controller', path: 'app/Http/Controllers')

# Find routes
Grep('Route::', path: 'routes')

# Find blade components
Glob('resources/views/components/*.blade.php')

# Find models
Grep('class.*extends Model', path: 'app/Models')

# Find where component is used
Grep('x-upload-form', path: 'resources/views')
```

---

## Quality Checklist

Before marking a feature complete, verify:

- [ ] **Functionality**
  - Feature works as expected
  - Edge cases handled
  - Error states handled gracefully

- [ ] **Code Quality**
  - Files read before editing
  - No duplicate code
  - Proper error handling
  - Logging where appropriate

- [ ] **Testing**
  - PHPUnit tests pass
  - E2E tests pass (if applicable)
  - Manual testing performed

- [ ] **UI/UX**
  - Responsive (desktop + mobile)
  - Loading states present
  - Error messages clear
  - Success feedback provided

- [ ] **Documentation**
  - Code comments added
  - Inline documentation updated
  - Analysis/summary documents created
  - README updated (if needed)

- [ ] **Performance**
  - No N+1 queries
  - Assets optimized
  - Lazy loading used
  - Caching considered

---

## Troubleshooting

### Common Issues

**1. Storage 404 Errors**
- Check symlink: `ls -la public/storage`
- Recreate: `php artisan storage:link`
- Verify paths: `Storage::disk('public')->exists($path)`

**2. Tests Timing Out**
- Increase timeout in `playwright.config.ts`
- Check if server is running
- Disable global setup temporarily

**3. Image Processing Fails**
- Install GD: `apt-get install php8.3-gd`
- Check memory limit: `php -i | grep memory_limit`
- Try with smaller images first

**4. Blade Component Not Found**
- Check namespace: `x-upload-form` → `upload-form.blade.php`
- Clear view cache: `php artisan view:clear`
- Verify file exists: `ls resources/views/components/`

**5. JavaScript Not Working**
- Check browser console for errors
- Verify script placement (before `</body>`)
- Check for variable name conflicts
- Use IIFE to scope: `(function() { /* code */ })()`

---

## Best Practices Summary

1. ✅ Use TodoWrite for multi-step tasks
2. ✅ Read files before editing
3. ✅ Prefer editing over creating new files
4. ✅ Run tests after changes
5. ✅ Document as you go
6. ✅ Use descriptive commit messages
7. ✅ Handle errors gracefully
8. ✅ Provide user feedback (toasts, progress bars)
9. ✅ Consider mobile responsiveness
10. ✅ Keep code DRY and maintainable
11. ✅ Log errors for debugging
12. ✅ Use placeholders for missing assets
13. ✅ Validate on both client and server
14. ✅ Write tests that would catch regressions
15. ✅ Update documentation when behavior changes

---

## Conclusion

This workflow guide provides a foundation for consistent, high-quality development on the memorial project. Adapt and extend these patterns as new challenges arise. When in doubt, prioritize user experience, code maintainability, and test coverage.

**Next Steps:**
- Review this guide before starting new features
- Update this guide when discovering new patterns
- Share improvements with team
- Keep documentation in sync with codebase