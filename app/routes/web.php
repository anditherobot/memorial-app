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

// Rate limiting
RateLimiter::for('wish-submit', function (Request $request) {
    return [Limit::perMinute(5)->by($request->ip())];
});
RateLimiter::for('uploads', function (Request $request) {
    return [Limit::perMinute(3)->by($request->ip())];
});

Route::view('/', 'home')->name('home');

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
});

// Optional: Token-based moderation endpoints (no login), separate namespace to avoid route name collisions
Route::middleware('admin.token')->prefix('admin-token')->group(function () {
    Route::get('/wishes', [WishController::class, 'adminIndex'])->name('adminToken.wishes');
    Route::post('/wishes/{wish}/approve', [WishController::class, 'approve'])->name('adminToken.wishes.approve');
    Route::delete('/wishes/{wish}', [WishController::class, 'destroy'])->name('adminToken.wishes.delete');
});

// Uploads
Route::post('/uploads', [UploadController::class, 'store'])
    ->middleware('throttle:uploads')
    ->name('uploads.store');

// Updates (posts)
Route::get('/updates', [PostController::class, 'index'])->name('updates.index');
Route::get('/updates/{post}', [PostController::class, 'show'])->name('updates.show');
