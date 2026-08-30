<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\CarController as AdminCarController;
use App\Http\Controllers\Api\V1\Admin\CarStatusController as AdminCarStatusController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\V1\Admin\MediaEnhancementController as AdminMediaEnhancementController;
use App\Http\Controllers\Api\V1\Admin\MediaSignatureController as AdminMediaSignatureController;
use App\Http\Controllers\Api\V1\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\V1\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\V1\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Public\BrandController as PublicBrandController;
use App\Http\Controllers\Api\V1\Public\CarController as PublicCarController;
use App\Http\Controllers\Api\V1\Public\CarEventController as PublicCarEventController;
use App\Http\Controllers\Api\V1\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\V1\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Api\V1\Public\SettingController as PublicSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Prefix: /api/v1)
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| All routes in this file are prefixed with /api/v1.
|
*/

// Health check endpoint (M0)
Route::get('/health', HealthController::class)->name('health');

// Authentication routes (M0)
Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class)
        ->middleware('throttle:login')
        ->name('auth.login');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class)->name('auth.logout');
        Route::get('/me', MeController::class)->name('auth.me');
    });
});

// Public endpoints (M1 + M2)
Route::get('/brands', [PublicBrandController::class, 'index'])->name('brands.index');
Route::get('/cars', [PublicCarController::class, 'index'])->name('cars.index');
Route::get('/cars/{slug}', [PublicCarController::class, 'show'])->name('cars.show');
Route::post('/cars/{slug}/events', PublicCarEventController::class)
    ->middleware('throttle:car-events')
    ->name('cars.events.store');
Route::get('/services', [PublicServiceController::class, 'index'])->name('services.index');
Route::get('/posts', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/posts/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
Route::get('/settings', PublicSettingController::class)->name('settings.index');

// Admin backoffice endpoints (M1 + M2 + M3 + M4)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    // Dashboard (BE-39)
    Route::get('/dashboard', AdminDashboardController::class)->name('admin.dashboard');

    // Brands
    Route::get('/brands', [AdminBrandController::class, 'index'])->name('admin.brands.index');
    Route::post('/brands', [AdminBrandController::class, 'store'])->name('admin.brands.store');

    // Services (BE-24)
    Route::get('/services', [AdminServiceController::class, 'index'])->name('admin.services.index');
    Route::post('/services', [AdminServiceController::class, 'store'])->name('admin.services.store');
    Route::patch('/services/{id}', [AdminServiceController::class, 'update'])->name('admin.services.update');

    // Posts (BE-25)
    Route::get('/posts', [AdminPostController::class, 'index'])->name('admin.posts.index');
    Route::post('/posts', [AdminPostController::class, 'store'])->name('admin.posts.store');
    Route::get('/posts/{id}', [AdminPostController::class, 'show'])->name('admin.posts.show');
    Route::patch('/posts/{id}', [AdminPostController::class, 'update'])->name('admin.posts.update');

    // Settings (BE-29)
    Route::get('/settings', [AdminSettingController::class, 'show'])->name('admin.settings.show');
    Route::patch('/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');

    // Cars CRUD
    Route::get('/cars', [AdminCarController::class, 'index'])->name('admin.cars.index');
    Route::post('/cars', [AdminCarController::class, 'store'])->name('admin.cars.store');
    Route::get('/cars/{id}', [AdminCarController::class, 'show'])->name('admin.cars.show');
    Route::patch('/cars/{id}', [AdminCarController::class, 'update'])->name('admin.cars.update');
    Route::delete('/cars/{id}', [AdminCarController::class, 'destroy'])->name('admin.cars.destroy');

    // Car status transition (BE-16)
    Route::patch('/cars/{id}/status', AdminCarStatusController::class)->name('admin.cars.status');

    // Media endpoints (BE-18, BE-19, BE-20)
    Route::post('/media/upload-signature', AdminMediaSignatureController::class)
        ->middleware('throttle:upload-signature')
        ->name('admin.media.upload-signature');
    Route::get('/cars/{id}/media', [AdminMediaController::class, 'index'])->name('admin.cars.media.index');
    Route::post('/cars/{id}/media', [AdminMediaController::class, 'store'])->name('admin.cars.media.store');
    Route::post('/cars/{id}/media/reorder', [AdminMediaController::class, 'reorder'])->name('admin.cars.media.reorder');
    Route::patch('/cars/{id}/media/reorder', [AdminMediaController::class, 'reorder']);
    Route::patch('/media/{id}', [AdminMediaController::class, 'update'])->name('admin.media.update');
    Route::delete('/media/{id}', [AdminMediaController::class, 'destroy'])->name('admin.media.destroy');

    // Media enhancements (BE-33, BE-34, BE-35, BE-36)
    Route::get('/admin/quotas', [AdminMediaEnhancementController::class, 'quotas'])->name('admin.quotas.index');
    Route::get('/media/{id}/enhancements', [AdminMediaEnhancementController::class, 'index'])->name('admin.media.enhancements.index');
    Route::post('/media/{id}/enhance', [AdminMediaEnhancementController::class, 'store'])->name('admin.media.enhance');
    Route::post('/enhancements/{id}/approve', [AdminMediaEnhancementController::class, 'approve'])->name('admin.enhancements.approve');
});
