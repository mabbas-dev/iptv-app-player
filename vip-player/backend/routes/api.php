<?php

use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\PlaylistContentController;
use App\Http\Controllers\Api\V1\ResellerApiController;
use App\Http\Controllers\Api\V1\SupportController;
use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // --- Player app endpoints (device-scoped, public) ---
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices/{deviceCode}/status', [DeviceController::class, 'status']);
    Route::get('/devices/{deviceCode}/playlists', [DeviceController::class, 'playlists']);
    Route::post('/devices/{deviceCode}/parental-lock', [DeviceController::class, 'setParentalLock']);
    Route::post('/devices/{deviceCode}/parental-lock/verify', [DeviceController::class, 'verifyParentalPin']);
    Route::post('/devices/{deviceCode}/mac-lock', [DeviceController::class, 'setMacLock']);
    Route::post('/devices/{deviceCode}/synced', [DeviceController::class, 'markSynced']);

    Route::get('/devices/{deviceCode}/playlists/{playlistId}/content/live', [PlaylistContentController::class, 'live']);
    Route::get('/devices/{deviceCode}/playlists/{playlistId}/content/vod', [PlaylistContentController::class, 'vod']);
    Route::get('/devices/{deviceCode}/playlists/{playlistId}/content/series', [PlaylistContentController::class, 'series']);
    Route::get('/devices/{deviceCode}/playlists/{playlistId}/content/series/{seriesId}/episodes', [PlaylistContentController::class, 'episodes']);
    Route::get('/devices/{deviceCode}/playlists/{playlistId}/content/m3u', [PlaylistContentController::class, 'm3u']);

    Route::post('/support', [SupportController::class, 'store']);
    Route::get('/devices/{deviceCode}/support', [SupportController::class, 'index']);

    // --- Reseller API (X-API-Key header) ---
    Route::prefix('reseller')->middleware(ApiKeyAuth::class)->group(function () {
        Route::get('/balance', [ResellerApiController::class, 'balance']);
        Route::get('/plans', [ResellerApiController::class, 'plans']);
        Route::get('/devices', [ResellerApiController::class, 'devices']);
        Route::post('/devices/activate', [ResellerApiController::class, 'activate']);
        Route::get('/transactions', [ResellerApiController::class, 'transactions']);
    });
});
