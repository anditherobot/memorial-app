<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

use App\Http\Controllers\WishController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PhotoController;

// Rate limiting

// Rate limiting
RateLimiter::for('wish-submit', function (Request $request) {
    return [Limit::perMinute(5)->by($request->ip())];
});
RateLimiter::for('uploads', function (Request $request) {
    return [Limit::perMinute(3)->by($request->ip())];
});

use App\Http\Controllers\HomeController;

Route::get('/', HomeController::class)->name('home');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// Wishes (public)
Route::get('/wishes', [WishController::class, 'index'])->name('wishes.index');
Route::post('/wishes', [WishController::class, 'store'])
    ->middleware('throttle:wish-submit')
    ->name('wishes.store');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin (session auth + is_admin)
Route::middleware(['auth','admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/wishes', [WishController::class, 'adminIndex'])->name('admin.wishes');
    Route::post('/wishes/{wish}/approve', [WishController::class, 'approve'])->name('admin.wishes.approve');
    Route::delete('/wishes/{wish}', [WishController::class, 'destroy'])->name('admin.wishes.delete');

    // Gallery management
    Route::get('/gallery', [\App\Http\Controllers\AdminGalleryController::class, 'index'])->name('admin.gallery');
    Route::post('/gallery/upload', [\App\Http\Controllers\AdminGalleryController::class, 'upload'])->name('admin.gallery.upload');
    Route::delete('/media/{media}', [\App\Http\Controllers\AdminGalleryController::class, 'destroy'])->name('admin.media.destroy');

    // Updates management
    Route::get('/updates', [\App\Http\Controllers\AdminPostController::class, 'index'])->name('admin.updates.index');
    Route::get('/updates/create', [\App\Http\Controllers\AdminPostController::class, 'create'])->name('admin.updates.create');
    Route::post('/updates', [\App\Http\Controllers\AdminPostController::class, 'store'])->name('admin.updates.store');
    Route::get('/updates/{post}/edit', [\App\Http\Controllers\AdminPostController::class, 'edit'])->name('admin.updates.edit');
    Route::put('/updates/{post}', [\App\Http\Controllers\AdminPostController::class, 'update'])->name('admin.updates.update');
    Route::delete('/updates/{post}', [\App\Http\Controllers\AdminPostController::class, 'destroy'])->name('admin.updates.destroy');

    // Task/Feature Tracker
    Route::get('/tasks', [\App\Http\Controllers\AdminTaskController::class, 'index'])->name('admin.tasks.index');
    Route::post('/tasks', [\App\Http\Controllers\AdminTaskController::class, 'store'])->name('admin.tasks.store');
    Route::get('/tasks/{task}', [\App\Http\Controllers\AdminTaskController::class, 'show'])->name('admin.tasks.show');
    Route::put('/tasks/{task}', [\App\Http\Controllers\AdminTaskController::class, 'update'])->name('admin.tasks.update');
    Route::patch('/tasks/{task}/status', [\App\Http\Controllers\AdminTaskController::class, 'updateStatus'])->name('admin.tasks.update-status');
    Route::delete('/tasks/{task}', [\App\Http\Controllers\AdminTaskController::class, 'destroy'])->name('admin.tasks.destroy');

    // Memorial Events management
    Route::prefix('memorial/events')->name('memorial.events.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'store'])->name('store');
        Route::get('/{event}', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'edit'])->name('edit');
        Route::put('/{event}', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'update'])->name('update');
        Route::delete('/{event}', [\App\Http\Controllers\Admin\MemorialEventsController::class, 'destroy'])->name('destroy');
    });

    // Memorial Content management
    Route::prefix('memorial/content')->name('memorial.content.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MemorialContentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\MemorialContentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\MemorialContentController::class, 'store'])->name('store');
        Route::get('/{contentType}/show', [\App\Http\Controllers\Admin\MemorialContentController::class, 'show'])->name('show');
        Route::get('/{content}/edit', [\App\Http\Controllers\Admin\MemorialContentController::class, 'edit'])->name('edit');
        Route::put('/{content}', [\App\Http\Controllers\Admin\MemorialContentController::class, 'update'])->name('update');
        Route::delete('/{content}', [\App\Http\Controllers\Admin\MemorialContentController::class, 'destroy'])->name('destroy');

        // Convenient type-based editing routes
        Route::get('/{contentType}/edit-type', [\App\Http\Controllers\Admin\MemorialContentController::class, 'editByType'])->name('edit-by-type');
        Route::put('/{contentType}/update-type', [\App\Http\Controllers\Admin\MemorialContentController::class, 'updateByType'])->name('update-by-type');
    });

    // Documentation
    Route::get('/docs', [\App\Http\Controllers\AdminDocumentationController::class, 'index'])->name('admin.docs');
});

// Optional: Token-based moderation endpoints (no login), separate namespace to avoid route name collisions
Route::middleware('admin.token')->prefix('admin-token')->group(function () {
    Route::get('/wishes', [WishController::class, 'adminIndex'])->name('adminToken.wishes');
    Route::post('/wishes/{wish}/approve', [WishController::class, 'approve'])->name('adminToken.wishes.approve');
    Route::delete('/wishes/{wish}', [WishController::class, 'destroy'])->name('adminToken.wishes.delete');
});

// Public photo upload
Route::get('/photos/upload', [PhotoController::class, 'create'])->name('photos.create');
Route::post('/photos/upload', [PhotoController::class, 'store'])->name('photos.store');
Route::get('/photos/{photo:uuid}/status', [PhotoController::class, 'status'])->name('photos.status');
Route::get('/photos/{photo:uuid}/thumb', [PhotoController::class, 'thumb'])->name('photos.thumb');

Route::get('/upload', [UploadController::class, 'show'])->name('upload.show');
Route::post('/upload', [UploadController::class, 'upload'])
    ->middleware('throttle:uploads')
    ->name('upload.store');

// Uploads (legacy API endpoint)
Route::post('/uploads', [UploadController::class, 'store'])
    ->middleware('throttle:uploads')
    ->name('uploads.store');

// Updates (posts)
Route::get('/updates', [PostController::class, 'index'])->name('updates.index');
Route::get('/updates/{post}', [PostController::class, 'show'])->name('updates.show');
