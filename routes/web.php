<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\Shop\StripeWebhookController;
use App\Http\Controllers\Shop\AccountController;
use Illuminate\Support\Facades\Route;

// ── Storefront ──────────────────────────────────────────────────────────────
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/about', [ShopController::class, 'about'])->name('about');
Route::match(['get','post'], '/contact', [ShopController::class, 'contact'])->name('contact');
Route::post('/location', [LocationController::class, 'set'])->name('location.set');

// ── Cart ────────────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// ── Checkout ────────────────────────────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/stripe/intent', [CheckoutController::class, 'stripeIntent'])->name('checkout.stripe.intent');
Route::get('/checkout/stripe/return/{orderNumber}', [CheckoutController::class, 'stripeReturn'])->name('checkout.stripe.return');
Route::post('/checkout/paypal/capture', [CheckoutController::class, 'paypalCapture'])->name('checkout.paypal.capture');
Route::get('/checkout/paypal/return/{orderNumber}', [CheckoutController::class, 'paypalReturn'])->name('checkout.paypal.return');
Route::get('/checkout/paypal/cancel/{orderNumber}', [CheckoutController::class, 'paypalCancel'])->name('checkout.paypal.cancel');
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::get('/order-confirmation/{orderNumber}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
Route::get('/order/{orderNumber}/invoice', [CheckoutController::class, 'invoice'])->name('order.invoice');

// ── Customer account (storefront) ───────────────────────────────────────────
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password');
});

// ── Dashboard ───────────────────────────────────────────────────────────────
Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::patch('/{category}', [CategoryController::class, 'update'])->name('patch');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/export', [ImportExportController::class, 'export'])->name('export');
        Route::get('/sample-template', [ImportExportController::class, 'sampleTemplate'])->name('sample');
        Route::get('/import', [ImportExportController::class, 'importForm'])->name('import');
        Route::post('/import', [ImportExportController::class, 'importProcess'])->name('import.process');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::patch('/{product}', [ProductController::class, 'update'])->name('patch');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('admin:super')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::get('/content', [ContentController::class, 'index'])->name('content.index');
    Route::put('/content/{section}', [ContentController::class, 'update'])->name('content.update');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [SettingController::class, 'test'])->name('settings.test');
    Route::put('/settings/payments', [SettingController::class, 'updatePayments'])->name('settings.payments');
    Route::post('/settings/branding', [SettingController::class, 'updateBranding'])->name('settings.branding');

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{payment}/sync', [PaymentController::class, 'sync'])->name('sync');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';
