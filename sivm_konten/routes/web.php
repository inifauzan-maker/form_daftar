<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontenMarketingController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Network access info page (public)
Route::get('/network-access', function () {
    return response()->file(public_path('network-access.html'));
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Content Marketing routes
    Route::resource('konten-marketing', KontenMarketingController::class);
    
    // API routes for Vue components
    Route::prefix('api')->group(function () {
        Route::get('/content-stats', [DashboardController::class, 'getContentStats']);
        Route::get('/content-calendar', [KontenMarketingController::class, 'getCalendarData']);
        Route::get('/content-analytics', [DashboardController::class, 'getAnalytics']);
        Route::get('/network-info', function () {
            return response()->json([
                'ip' => request()->server('SERVER_ADDR'),
                'host' => request()->getHost(),
                'port' => request()->getPort(),
                'url' => request()->getSchemeAndHttpHost()
            ]);
        });
        Route::get('/users', [DashboardController::class, 'getUsers']);
        
        // Content Research & AI APIs
        Route::get('/instagram/research', [\App\Http\Controllers\Api\ContentApiController::class, 'getInstagramResearch']);
        Route::get('/tiktok/trending', [\App\Http\Controllers\Api\ContentApiController::class, 'getTikTokTrending']);
        Route::post('/ai/generate', [\App\Http\Controllers\Api\ContentApiController::class, 'generateContent']);
        Route::post('/ai/check-duplicate', [\App\Http\Controllers\Api\ContentApiController::class, 'checkDuplicate']);
        Route::get('/suggestions', [\App\Http\Controllers\Api\ContentApiController::class, 'getContentSuggestions']);
        // Upload Konten dengan file
        Route::post('/upload-konten', [\App\Http\Controllers\KontenMarketingController::class, 'saveContent']);
    });
    
    // User Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Profile Photo Management
    Route::get('/profile/photo', [ProfileController::class, 'showPhotoForm'])->name('profile.photo.form');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo/delete', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    // Arsip Konten
    Route::get('/arsip-konten', [\App\Http\Controllers\KontenMarketingController::class, 'arsip'])->name('arsip.konten');

    // Social Media Integration
    Route::get('/social-integration', [\App\Http\Controllers\SocialIntegrationController::class, 'index'])->name('social.integration');
    Route::get('/social/callback/{platform}', [\App\Http\Controllers\SocialIntegrationController::class, 'callback'])->name('social.callback');
    Route::post('/social/disconnect/{platform}', [\App\Http\Controllers\SocialIntegrationController::class, 'disconnect'])->name('social.disconnect');
    Route::post('/social/settings', [\App\Http\Controllers\SocialIntegrationController::class, 'saveSettings'])->name('social.settings');
    Route::post('/social/post/instagram', [\App\Http\Controllers\SocialIntegrationController::class, 'postToInstagram'])->name('social.post.instagram');
    Route::post('/social/post/tiktok', [\App\Http\Controllers\SocialIntegrationController::class, 'postToTikTok'])->name('social.post.tiktok');
});
