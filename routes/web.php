<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

// ====== PUBLIC + TRACKING (klik link affiliate) ======
Route::middleware(['affiliate.tracker'])->group(function () {

    // Landing (kalau nanti mau ada halaman)
    Route::get('/', function () {
        return view('welcome');
    })->name('landing');

    // LINK YANG DIBAGIKAN AFFILIATE
    Route::get('/r', function (Request $request) {
        $ref = strtoupper($request->query('ref', ''));

        if ($ref === '') {
            abort(404);
        }

        $botUsername = config('services.telegram.username');

        // Setelah middleware jalan (set cookie + log klik),
        // kita lempar ke bot Telegram bawa kode yang sama
        return redirect()->away("https://t.me/{$botUsername}?start={$ref}");
    })->name('redirect.ref');

    // Kalau nanti ada form checkout di web
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');
});

// ====== AREA LOGIN (AFFILIATE DASHBOARD) ======
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Update prospek dari modal
    Route::patch('/leads/{lead}', [ReferralTrackController::class, 'update'])
        ->name('leads.update');
});

// ====== TELEGRAM WEBHOOK (BOT) ======
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

require __DIR__.'/auth.php';
