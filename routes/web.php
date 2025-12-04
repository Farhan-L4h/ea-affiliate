<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AffiliateDashboardController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\ReferralTrackController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============== PUBLIC + AFFILIATE TRACKER ==============
Route::middleware(['affiliate.tracker'])->group(function () {
    // Landing / halaman utama - redirect ke login
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('landing');

    // Checkout (prospek / pembelian)
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');
});

// ============== AUTH AREA (USER LOGIN) ==============
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Affiliate
    Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])
        ->name('dashboard');

    // Edit profil
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Update data prospek (modal edit di tabel dashboard)
    Route::patch('/leads/{lead}', [ReferralTrackController::class, 'update'])
        ->name('leads.update');
});

// ============== TELEGRAM WEBHOOK (BOT) ==============
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

require __DIR__ . '/auth.php';
