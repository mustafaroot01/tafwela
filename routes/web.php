<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// ── Admin Auth ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');

    // ── Protected Admin Routes ──────────────────────────────────
    Route::middleware(['auth', 'admin'])->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Stations
        Route::resource('stations', StationController::class)
            ->except(['show']);

        // Admin station updates
        Route::post('stations/{station}/update-status', [UpdateController::class, 'adminUpdate'])
            ->name('stations.admin-update');
        Route::post('stations/{station}/force-verify', [StationController::class, 'forceVerify'])
            ->name('stations.force-verify');
        Route::post('stations/{station}/toggle-status', [StationController::class, 'toggleStatus'])
            ->name('stations.toggle-status');

        // Users
        Route::get('users',          [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}',   [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/ban',   [UserController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
        Route::post('users/{user}/toggle-trusted', [UserController::class, 'toggleTrusted'])->name('users.toggle-trusted');

        // Employees
        Route::resource('employees', EmployeeController::class)
            ->except(['show']);

        // Updates monitoring
        Route::get('updates',             [UpdateController::class, 'index'])->name('updates.index');
        Route::post('updates/approve-all', [UpdateController::class, 'approveAll'])->name('updates.approve-all');
        Route::post('updates/{update}/approve', [UpdateController::class, 'approve'])->name('updates.approve');
        Route::delete('updates/{update}', [UpdateController::class, 'destroy'])->name('updates.destroy');

        // Notifications broadcast
        Route::get('notifications',                        [BroadcastController::class, 'index'])->name('notifications.index');
        Route::post('notifications/send',                  [BroadcastController::class, 'send'])->name('notifications.send');
        Route::delete('notifications/{notification}',      [BroadcastController::class, 'destroy'])->name('notifications.destroy');

        // Settings
        Route::get('settings',           [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings',          [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/test-otp', [SettingsController::class, 'testOtp'])->name('settings.test-otp');
        Route::post('settings/test-telegram', [SettingsController::class, 'testTelegram'])->name('settings.test-telegram');
        Route::post('settings/test-fcm',      [SettingsController::class, 'testFcm'])->name('settings.test-fcm');

        // Reports
        Route::get('reports',            [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::put('reports/{report}',   [\App\Http\Controllers\Admin\ReportController::class, 'updateStatus'])->name('reports.update');
        Route::delete('reports/{report}',[\App\Http\Controllers\Admin\ReportController::class, 'destroy'])->name('reports.destroy');

        // Profile
        Route::get('profile',  [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    });
});
