<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────────
Route::get('/',            [PublicController::class, 'home'])->name('home');
Route::get('/floor-plan',  [PublicController::class, 'floorPlan'])->name('floor-plan');
Route::get('/about',       [PublicController::class, 'about'])->name('about');

// ── Breeze auth routes ────────────────────────────────────────
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
});

// ── WhatsApp webhook ──────────────────────────────────────────
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'webhook'])
    ->name('webhook.whatsapp')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/auth.php';

