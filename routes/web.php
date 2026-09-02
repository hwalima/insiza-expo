<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\DeployWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────────
Route::get('/',            [PublicController::class, 'home'])->name('home');
Route::get('/floor-plan',  [PublicController::class, 'floorPlan'])->name('floor-plan');
Route::get('/about',       [PublicController::class, 'about'])->name('about');
Route::get('/attend',      [AttendeeController::class, 'showForm'])->name('attend');
Route::post('/attend',     [AttendeeController::class, 'register'])->name('attend.register');
Route::get('/attend/success/{code}', [AttendeeController::class, 'success'])->name('attend.success');
Route::get('/verify/{code}',         [AttendeeController::class, 'verify'])->name('attend.verify');

// ── Breeze auth routes ────────────────────────────────────────
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Force-password-change (exempt from the middleware itself via route name check)
    Route::get('/change-password',  [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin routes ──────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',            [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/expo',        [DashboardController::class, 'expoEditor'])->name('expo');
    Route::get('/expo/create', [DashboardController::class, 'expoCreate'])->name('expo.create');
    Route::get('/floor-plan',  [DashboardController::class, 'floorPlan'])->name('floor-plan');
    Route::get('/bookings',    [DashboardController::class, 'bookings'])->name('bookings');
    Route::get('/attendees',                  [AttendeeController::class, 'adminList'])->name('attendees');
    Route::post('/attendees',                 [AttendeeController::class, 'adminStore'])->name('attendees.store');
    Route::put('/attendees/{attendee}',       [AttendeeController::class, 'adminUpdate'])->name('attendees.update');
    Route::delete('/attendees/{attendee}',    [AttendeeController::class, 'adminDestroy'])->name('attendees.destroy');
    Route::post('/attendees/checkin/{code}',  [AttendeeController::class, 'checkIn'])->name('attendees.checkin');

    // Settings (super_admin only)
    Route::get('/settings',                   [SettingsController::class, 'show'])->name('settings');
    Route::patch('/settings/branding',        [SettingsController::class, 'updateBranding'])->name('settings.branding');
    Route::patch('/settings/email',           [SettingsController::class, 'updateEmail'])->name('settings.email');
    Route::post('/settings/test-email',       [SettingsController::class, 'testEmail'])->name('settings.test-email');
    Route::patch('/settings/integrations',    [SettingsController::class, 'updateIntegrations'])->name('settings.integrations');
    Route::patch('/settings/general',         [SettingsController::class, 'updateGeneral'])->name('settings.general');

    // Admin user management
    Route::get('/users',                      [AdminUserController::class, 'index'])->name('users');
    Route::post('/users',                     [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}',             [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-password',[AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}',            [AdminUserController::class, 'destroy'])->name('users.destroy');
});

// ── WhatsApp + Deploy webhooks (CSRF exempt) ─────────────────
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::post('/webhook/whatsapp',     [WhatsAppController::class, 'webhook'])->name('webhook.whatsapp');
        Route::post('/api/webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('api.webhook.whatsapp');
        Route::post('/deploy',               [DeployWebhookController::class, 'handle'])->name('deploy.webhook');
    });

require __DIR__.'/auth.php';

