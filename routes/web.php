<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\{
    ProfileController,
    AffiliateDashboardController,
    CheckoutController,
    TelegramWebhookController,
    ReferralTrackController
};

use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboardController,
    AffiliateController as AdminAffiliateController,
    ProspectController as AdminProspectController
};

use App\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====== PUBLIC + TRACKING (klik link affiliate) ======
Route::middleware('affiliate.tracker')->group(function () {

    // Landing page (kalau nanti mau dipakai)
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
        // redirect ke bot Telegram dengan kode yang sama
        return redirect()->away("https://t.me/{$botUsername}?start={$ref}");
    })->name('affiliate.redirect');

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

    // Update prospek dari modal (DISABLED - hanya admin yang bisa edit)
    // Route::patch('/leads/{lead}', [ReferralTrackController::class, 'update'])
    //     ->name('leads.update');
});

// ====== ADMIN AREA ======
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('dashboard');
    
    // Manage Affiliates
    Route::resource('affiliates', AdminAffiliateController::class);
    Route::patch('/affiliates/{affiliate}/toggle-status', [AdminAffiliateController::class, 'toggleStatus'])
        ->name('affiliates.toggle-status');
    
    // Manage Prospects
    Route::resource('prospects', AdminProspectController::class)->only(['index', 'show', 'update', 'destroy']);
});

// ====== TELEGRAM WEBHOOK (BOT) ======
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

require __DIR__.'/auth.php';
