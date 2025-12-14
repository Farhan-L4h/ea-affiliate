<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\{
    ProfileController,
    AffiliateDashboardController,
    AffiliatePayoutController,
    CheckoutController,
    TelegramWebhookController,
    ReferralTrackController
};

use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboardController,
    AffiliateController as AdminAffiliateController,
    ProspectController as AdminProspectController,
    OrderController as AdminOrderController,
    SaleController as AdminSaleController,
    AdminPayoutController
};

use App\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====== PUBLIC + TRACKING (klik link affiliate) ======
Route::middleware('affiliate.tracker')->group(function () {

    // Landing page - redirect to login
    Route::get('/', function () {
        return redirect()->route('login');
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

    Route::get('/prospects', [AffiliateDashboardController::class, 'prospects'])
        ->name('affiliate.prospects');

    Route::get('/prospects/{id}', [AffiliateDashboardController::class, 'prospectDetail'])
        ->name('affiliate.prospects.detail');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Payout / Pencairan Komisi
    Route::get('/payout', [AffiliatePayoutController::class, 'index'])
        ->name('affiliate.payout.index');
    Route::get('/payout/bank-info', [AffiliatePayoutController::class, 'bankInfo'])
        ->name('affiliate.payout.bank-info');
    Route::put('/payout/bank-info', [AffiliatePayoutController::class, 'updateBankInfo'])
        ->name('affiliate.payout.bank-info.update');
    Route::post('/payout/request', [AffiliatePayoutController::class, 'requestPayout'])
        ->name('affiliate.payout.request');
    Route::get('/payout/{id}', [AffiliatePayoutController::class, 'show'])
        ->name('affiliate.payout.show');
    Route::delete('/payout/{id}/cancel', [AffiliatePayoutController::class, 'cancelRequest'])
        ->name('affiliate.payout.cancel');

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
    Route::post('/prospects/bulk-delete', [AdminProspectController::class, 'bulkDelete'])
        ->name('prospects.bulk-delete');
    
    // Manage Orders
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'destroy']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
        ->name('orders.update-status');
    
    // Manage Sales
    Route::resource('sales', AdminSaleController::class)->only(['index', 'show']);
    
    // Manage Payouts (Pencairan Komisi)
    Route::get('/payouts', [AdminPayoutController::class, 'index'])
        ->name('payouts.index');
    Route::get('/payouts/{id}', [AdminPayoutController::class, 'show'])
        ->name('payouts.show');
    Route::post('/payouts/{id}/approve', [AdminPayoutController::class, 'approve'])
        ->name('payouts.approve');
    Route::post('/payouts/{id}/reject', [AdminPayoutController::class, 'reject'])
        ->name('payouts.reject');
    Route::post('/payouts/{id}/mark-paid', [AdminPayoutController::class, 'markAsPaid'])
        ->name('payouts.mark-paid');
});

// ====== TELEGRAM WEBHOOK (BOT) ======
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramBotController::class, 'webhook'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

// ====== PAYMENT ROUTES ======
Route::get('/payment/{orderId}', [\App\Http\Controllers\PaymentController::class, 'show'])
    ->name('payment.show');
Route::get('/payment/{orderId}/status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])
    ->name('payment.check-status');

// ====== MOOTA WEBHOOK ======
Route::post('/webhook/moota', [\App\Http\Controllers\MootaWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhook.moota');

require __DIR__.'/auth.php';


//  Bebas

Route::get('/Landing', function () {
    return view('Landing');
});
