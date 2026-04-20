<?php

use App\Http\Controllers\API\AppConfigController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\StationController;
use App\Http\Controllers\API\UpdateController;
use Illuminate\Support\Facades\Route;

// ── App Config (exempt from maintenance - always available) ────
Route::get('v1/config', [AppConfigController::class, 'index']);

// ── Public Routes ──────────────────────────────────────────────
Route::prefix('v1')->middleware('maintenance')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('otp/send',   [AuthController::class, 'sendOtp']);
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('fcm-token',  [AuthController::class, 'updateFcmToken']); // Public for guests too
    });

    // Stations (public browse)
    Route::get('stations',        [StationController::class, 'index']);
    Route::get('stations/nearby', [StationController::class, 'nearby']);
    Route::get('stations/search',       [StationController::class, 'search']);
    Route::post('stations/import-route', [StationController::class, 'importRoute']);
    Route::get('stations/{id}',         [StationController::class, 'show']);

    // Updates (public read)
    Route::get('stations/{stationId}/updates', [UpdateController::class, 'forStation']);

    // Notifications (public index for broadcasts)
    Route::get('notifications',            [NotificationController::class, 'index']);

    // ── Authenticated Routes ────────────────────────────────────
    Route::middleware(['auth:sanctum', 'banned'])->group(function () {

        // Profile
        Route::get('auth/me',                  [AuthController::class, 'me']);
        Route::put('auth/profile',             [AuthController::class, 'updateProfile']);
        Route::post('auth/logout',             [AuthController::class, 'logout']);

        // Favorites
        Route::get('favorites',                [FavoriteController::class, 'index']);
        Route::post('favorites/{stationId}',   [FavoriteController::class, 'toggle']);

        // Submit update
        Route::post('stations/{stationId}/updates',  [UpdateController::class, 'store']);
        Route::post('stations/{stationId}/interact', [UpdateController::class, 'interact']);
        Route::post('updates/{updateId}/confirm',    [UpdateController::class, 'confirm']); // Legacy

        // Report station (Throttled to 3 per minute)
        Route::post('stations/{stationId}/report',   [StationController::class, 'report'])->middleware('throttle:3,1');

        // Notifications
        Route::put('notifications/read-all',   [NotificationController::class, 'markAllRead']);
        Route::put('notifications/{id}/read',  [NotificationController::class, 'markRead']);
    });
});
