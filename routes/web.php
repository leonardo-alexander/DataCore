<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Clean1Controller;
use App\Http\Controllers\Clean2Controller;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EndSurveyController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Middleware\PreventBackHistoryCache;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'root'])->name('home');

// Google OAuth: kept outside the {locale} prefix since the redirect URI
// registered with Google must be a fixed, static URL.
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::prefix('{locale}')
    ->where(['locale' => 'en|id'])
    ->group(function () {

        Route::get('/', HomeController::class)->name('locale.home');

        Route::put('language', [LanguageController::class, 'update'])->name('language.update');

        // Guest-only
        Route::middleware('guest')->group(function () {
            Route::get('login', [AuthController::class, 'viewLogin'])->name('login');
            Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
            Route::get('register', [AuthController::class, 'viewRegister'])->name('register');
            Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
        });

        // Admin
        Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('dashboard');
            Route::post('users/{user}/approve', [AdminController::class, 'approve'])->name('users.approve');
            Route::post('users/{user}/reject', [AdminController::class, 'reject'])->name('users.reject');
            Route::get('verifications/{user}/{type}', [AdminController::class, 'document'])->name('verifications.document');
        });

        // Authenticated
        Route::middleware(['auth', PreventBackHistoryCache::class])->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');

            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('surveys', SurveyController::class)->name('surveys.index');

            Route::prefix('marketplace')->name('marketplace.')->group(function () {
                Route::get('/', [MarketplaceController::class, 'index'])->name('index');
                Route::get('{collection}', [MarketplaceController::class, 'show'])->name('show');
                Route::post('{collection}/purchase', [PurchaseController::class, 'store'])->name('purchase');
                Route::post('{collection}/reviews', [ReviewController::class, 'store'])->name('review');
                Route::delete('{collection}/reviews', [ReviewController::class, 'delete'])->name('review.delete');
            });

            Route::get('settings', [SettingsController::class, 'edit'])->name('settings');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

            Route::prefix('purchases')->name('purchases.')->group(function () {
                Route::get('/', [PurchaseController::class, 'index'])->name('index');
            });

            Route::prefix('collections')->name('collections.')->group(function () {
                Route::get('/', [CollectionController::class, 'index'])->name('index');
                Route::get('create', [CollectionController::class, 'create'])->name('create');
                Route::post('/', [CollectionController::class, 'store'])->name('store');
                Route::post('import', [UploadController::class, 'store'])->middleware('throttle:import')->name('import');
                Route::get('{collection}/edit', [CollectionController::class, 'edit'])->name('edit');
                Route::put('{collection}', [CollectionController::class, 'update'])->name('update');
                Route::delete('{collection}', [CollectionController::class, 'destroy'])->name('destroy');
                Route::get('{collection}/analytics', [CollectionController::class, 'analytics'])->name('analytics');
                Route::get('{collection}/export', [CollectionController::class, 'export'])->name('export');
                Route::post('{collection}/end-survey', EndSurveyController::class)->name('end-survey');
                Route::post('{collection}/clean1', Clean1Controller::class)->name('clean1');
                Route::post('{collection}/clean2', Clean2Controller::class)->name('clean2');
            });

            Route::prefix('collections/{collection}/entries')->name('entries.')->group(function () {
                Route::get('create', [EntryController::class, 'create'])->name('create');
                Route::post('/', [EntryController::class, 'store'])->middleware('throttle:entry')->name('store');
            });

            Route::prefix('wallet')->name('wallet.')->middleware('throttle:wallet')->group(function () {
                Route::get('/', [WalletController::class, 'index'])->withoutMiddleware('throttle:wallet')->name('index');
                Route::post('topup', [WalletController::class, 'topup'])->name('topup');
                Route::post('topup/confirm', [WalletController::class, 'confirm'])->name('topup.confirm');
                Route::get('topup/cancel', [WalletController::class, 'cancel'])->withoutMiddleware('throttle:wallet')->name('topup.cancel');
                Route::post('withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
            });

            Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
                Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
                Route::delete('{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/', [TransactionController::class, 'index'])->name('index');
            });

            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('/', [ProfileController::class, 'edit'])->name('edit');
                Route::put('/', [ProfileController::class, 'update'])->name('update');
            });

            Route::prefix('verification')->name('verification.')->group(function () {
                Route::get('/', [VerificationController::class, 'index'])->name('index');
                Route::post('/', [VerificationController::class, 'store'])->middleware('throttle:verification')->name('store');
            });

            Route::post('activities/read', [ActivityController::class, 'read'])->name('activities.read');
        });
    });
