<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AffiliateDashboardController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Checkout
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

// Route::middleware(['auth'])->group(function () {
//     Route::get('/affiliate/dashboard', [AffiliateDashboardController::class, 'index'])
//         ->name('affiliate.dashboard');
// });

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);


require __DIR__.'/auth.php';
