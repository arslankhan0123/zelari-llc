<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ManufacturerController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\SourcingRequestController;
use App\Http\Controllers\Admin\ReviewController;
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
use App\Http\Controllers\Shop\SourcingController;
use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\ReviewController as ShopReviewController;
use Illuminate\Support\Facades\Route;

// ── Storefront ──────────────────────────────────────────────────────────────
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/sitemap.xml', [\App\Http\Controllers\Shop\SitemapController::class, 'index'])->name('sitemap');
Route::get('/feed/google.xml', fn () => response(\App\Support\ProductFeed::google(), 200)->header('Content-Type', 'application/xml; charset=UTF-8'))->name('feed.google');
Route::get('/feed/meta.csv', fn () => response(\App\Support\ProductFeed::meta(), 200)->header('Content-Type', 'text/csv; charset=UTF-8'))->name('feed.meta');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Shop\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe')->middleware('throttle:10,1');
Route::get('/newsletter/unsubscribe/{token}', [\App\Http\Controllers\Shop\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');



Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/brands', [ShopController::class, 'brands'])->name('brands');
Route::get('/pdf-tools', [\App\Http\Controllers\Shop\ToolsController::class, 'index'])->name('tools.index');
Route::redirect('/tools', '/pdf-tools');
Route::get('/pdf-tools/convert-status/{slug}', [\App\Http\Controllers\Shop\ToolsConvertController::class, 'status'])->name('tools.convertStatus');
Route::post('/pdf-tools/convert/{slug}', [\App\Http\Controllers\Shop\ToolsConvertController::class, 'convert'])->name('tools.convert');
Route::get('/pdf-tools/{slug}', [\App\Http\Controllers\Shop\ToolsController::class, 'show'])->name('tools.show');
Route::get('/calculators', [\App\Http\Controllers\Shop\CalculatorsController::class, 'index'])->name('calculators.index');
Route::get('/calculators/{slug}', [\App\Http\Controllers\Shop\CalculatorsController::class, 'show'])->name('calculators.show');
Route::get('/image-tools', [\App\Http\Controllers\Shop\ImageToolsController::class, 'index'])->name('imgtools.index');
Route::get('/image-tools/{slug}', [\App\Http\Controllers\Shop\ImageToolsController::class, 'show'])->name('imgtools.show');
Route::get('/text-tools', [\App\Http\Controllers\Shop\TextToolsController::class, 'index'])->name('texttools.index');
Route::get('/text-tools/{slug}', [\App\Http\Controllers\Shop\TextToolsController::class, 'show'])->name('texttools.show');
// ZT_PUBLIC_ANCHOR
Route::get('/seller-tools', [\App\Http\Controllers\Shop\SellerToolsController::class, 'index'])->name('sellertools.index');
Route::get('/seller-tools/{slug}', [\App\Http\Controllers\Shop\SellerToolsController::class, 'show'])->name('sellertools.show');
Route::get('/random-tools', [\App\Http\Controllers\Shop\RandomToolsController::class, 'index'])->name('randomtools.index');
Route::get('/random-tools/{slug}', [\App\Http\Controllers\Shop\RandomToolsController::class, 'show'])->name('randomtools.show');
Route::get('/color-tools', [\App\Http\Controllers\Shop\ColorToolsController::class, 'index'])->name('colortools.index');
Route::get('/color-tools/{slug}', [\App\Http\Controllers\Shop\ColorToolsController::class, 'show'])->name('colortools.show');
Route::get('/qr-tools', [\App\Http\Controllers\Shop\QrToolsController::class, 'index'])->name('qrtools.index');
Route::get('/qr-tools/{slug}', [\App\Http\Controllers\Shop\QrToolsController::class, 'show'])->name('qrtools.show');
Route::get('/dev-tools', [\App\Http\Controllers\Shop\DevToolsController::class, 'index'])->name('devtools.index');
Route::get('/dev-tools/{slug}', [\App\Http\Controllers\Shop\DevToolsController::class, 'show'])->name('devtools.show');
Route::post('/coupon/apply', [\App\Http\Controllers\Shop\MarketingController::class, 'applyCoupon'])->name('coupon.apply');
Route::post('/coupon/remove', [\App\Http\Controllers\Shop\MarketingController::class, 'removeCoupon'])->name('coupon.remove');
Route::get('/spin-wheel', [\App\Http\Controllers\Shop\MarketingController::class, 'page'])->name('spinwheel.index');
Route::post('/wheel/spin', [\App\Http\Controllers\Shop\MarketingController::class, 'spin'])->name('wheel.spin');
Route::get('/tools/{slug}', fn (string $slug) => redirect()->route('tools.show', $slug));
Route::post('/chatbot', [\App\Http\Controllers\Shop\ChatbotController::class, 'message'])->name('chatbot.message')->middleware('throttle:40,1');
Route::get('/chatbot/poll', [\App\Http\Controllers\Shop\ChatbotController::class, 'poll'])->name('chatbot.poll')->middleware('throttle:120,1');
Route::post('/product/{slug}/review', [ShopReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
Route::get('/about', [ShopController::class, 'about'])->name('about');
Route::match(['get','post'], '/contact', [ShopController::class, 'contact'])->name('contact');
Route::get('/sourcing', [SourcingController::class, 'create'])->name('sourcing');
Route::post('/sourcing', [SourcingController::class, 'store'])->middleware('throttle:10,1')->name('sourcing.store');
Route::post('/location', [LocationController::class, 'set'])->name('location.set');

// ── Cart ────────────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/wishlist', [\App\Http\Controllers\Shop\WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [\App\Http\Controllers\Shop\WishlistController::class, 'toggle'])->name('wishlist.toggle')->middleware('throttle:60,1');
Route::post('/wishlist/clear', [\App\Http\Controllers\Shop\WishlistController::class, 'clear'])->name('wishlist.clear');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{productId}', [CartController::class, 'remove'])->where('productId', '[0-9:]+')->name('cart.remove');
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
Route::get('/dashboard', [\App\Http\Controllers\Admin\HomeController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('dashboard');
Route::post('/dashboard/task/{task}/toggle', [\App\Http\Controllers\Admin\HomeController::class, 'toggleTask'])->middleware(['auth', 'verified', 'admin:staff'])->name('dashboard.task.toggle');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'panelperm'])->prefix('admin/ecommerce')->name('admin.')->group(function () {

    // Ecommerce dashboard (the detailed store dashboard)
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

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

    Route::get('/notifications', [\App\Http\Controllers\Admin\LiveChatController::class, 'notifications'])->name('notifications.index');

    Route::prefix('pdf-tools')->name('pdftools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PdfToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\PdfToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\PdfToolsController::class, 'master'])->name('master');
    });

    Route::prefix('calculators')->name('calcs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CalculatorsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\CalculatorsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\CalculatorsController::class, 'master'])->name('master');
    });

    Route::prefix('image-tools')->name('imgtools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ImageToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\ImageToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\ImageToolsController::class, 'master'])->name('master');
    });

    Route::prefix('text-tools')->name('texttools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TextToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\TextToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\TextToolsController::class, 'master'])->name('master');
    });

    // ZT_ADMIN_ANCHOR
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MarketingController::class, 'index'])->name('index');
        Route::post('/plan', [\App\Http\Controllers\Admin\MarketingController::class, 'plan'])->name('plan');
        Route::post('/email', [\App\Http\Controllers\Admin\MarketingController::class, 'email'])->name('email');
        Route::post('/email/send', [\App\Http\Controllers\Admin\MarketingController::class, 'send'])->name('email.send');
        Route::post('/email/test', [\App\Http\Controllers\Admin\MarketingController::class, 'test'])->name('email.test');
    });
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('index');
        Route::post('/run', [\App\Http\Controllers\Admin\SeoController::class, 'run'])->name('run');
        Route::post('/fix', [\App\Http\Controllers\Admin\SeoController::class, 'fix'])->name('fix');
    });
    Route::prefix('seller-tools')->name('sellertools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SellerToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\SellerToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\SellerToolsController::class, 'master'])->name('master');
    });
    Route::prefix('random-tools')->name('randomtools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RandomToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\RandomToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\RandomToolsController::class, 'master'])->name('master');
    });
    Route::prefix('color-tools')->name('colortools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ColorToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\ColorToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\ColorToolsController::class, 'master'])->name('master');
    });
    Route::prefix('qr-tools')->name('qrtools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\QrToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\QrToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\QrToolsController::class, 'master'])->name('master');
    });
    Route::prefix('dev-tools')->name('devtools.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DevToolsController::class, 'index'])->name('index');
        Route::post('/toggle', [\App\Http\Controllers\Admin\DevToolsController::class, 'toggle'])->name('toggle');
        Route::post('/master', [\App\Http\Controllers\Admin\DevToolsController::class, 'master'])->name('master');
    });
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CouponsController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\CouponsController::class, 'store'])->name('store');
        Route::post('/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponsController::class, 'toggle'])->name('toggle');
        Route::delete('/{coupon}', [\App\Http\Controllers\Admin\CouponsController::class, 'destroy'])->name('destroy');
        Route::delete('/entry/{entry}', [\App\Http\Controllers\Admin\CouponsController::class, 'destroyEntry'])->name('entry.destroy');
        Route::post('/wheel', [\App\Http\Controllers\Admin\CouponsController::class, 'wheel'])->name('wheel');
        Route::post('/segments', [\App\Http\Controllers\Admin\CouponsController::class, 'segments'])->name('segments');
        Route::get('/export-emails', [\App\Http\Controllers\Admin\CouponsController::class, 'exportEmails'])->name('export');
    });

    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LiveChatController::class, 'index'])->name('index');
        Route::get('/threads', [\App\Http\Controllers\Admin\LiveChatController::class, 'threads'])->name('threads');
        Route::get('/messages', [\App\Http\Controllers\Admin\LiveChatController::class, 'messages'])->name('messages');
        Route::post('/send', [\App\Http\Controllers\Admin\LiveChatController::class, 'send'])->name('send');
        Route::post('/hours', [\App\Http\Controllers\Admin\LiveChatController::class, 'saveHours'])->name('hours');
        Route::get('/notify-counts', [\App\Http\Controllers\Admin\LiveChatController::class, 'notifyCounts'])->name('notify');
    });

    Route::prefix('sourcing')->name('sourcing.')->group(function () {
        Route::get('/', [SourcingRequestController::class, 'index'])->name('index');
        Route::get('/{sourcing}', [SourcingRequestController::class, 'show'])->name('show')->whereNumber('sourcing');
        Route::post('/{sourcing}/status', [SourcingRequestController::class, 'status'])->name('status');
        Route::post('/{sourcing}/note', [SourcingRequestController::class, 'note'])->name('note');
        Route::delete('/{sourcing}', [SourcingRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index');
        Route::get('/{message}', [ContactMessageController::class, 'show'])->name('show');
        Route::post('/{message}/status', [ContactMessageController::class, 'status'])->name('status');
        Route::post('/{message}/note', [ContactMessageController::class, 'note'])->name('note');
        Route::delete('/{message}', [ContactMessageController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::post('/{review}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::post('/{review}/reject', [ReviewController::class, 'reject'])->name('reject');
        Route::post('/{review}/unpublish', [ReviewController::class, 'unpublish'])->name('unpublish');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/', [ManufacturerController::class, 'index'])->name('index');
        Route::get('/create', [ManufacturerController::class, 'create'])->name('create');
        Route::post('/', [ManufacturerController::class, 'store'])->name('store');
        Route::get('/{manufacturer}/edit', [ManufacturerController::class, 'edit'])->name('edit');
        Route::put('/{manufacturer}', [ManufacturerController::class, 'update'])->name('update');
        Route::delete('/{manufacturer}', [ManufacturerController::class, 'destroy'])->name('destroy');
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

    // Marketplace vendors
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VendorController::class, 'index'])->name('index');
        Route::get('/{vendor}', [\App\Http\Controllers\Admin\VendorController::class, 'show'])->name('show');
        Route::put('/{vendor}', [\App\Http\Controllers\Admin\VendorController::class, 'update'])->name('update');
        Route::post('/{vendor}/approve', [\App\Http\Controllers\Admin\VendorController::class, 'approve'])->name('approve');
        Route::post('/{vendor}/suspend', [\App\Http\Controllers\Admin\VendorController::class, 'suspend'])->name('suspend');
        Route::post('/{vendor}/reject', [\App\Http\Controllers\Admin\VendorController::class, 'reject'])->name('reject');
        Route::post('/{vendor}/reactivate', [\App\Http\Controllers\Admin\VendorController::class, 'reactivate'])->name('reactivate');
        Route::delete('/{vendor}', [\App\Http\Controllers\Admin\VendorController::class, 'destroy'])->name('destroy');
    });

    // Vendor product approvals
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ApprovalController::class, 'index'])->name('index');
        Route::post('/{product}/approve', [\App\Http\Controllers\Admin\ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{product}/reject', [\App\Http\Controllers\Admin\ApprovalController::class, 'reject'])->name('reject');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::get('/content', [ContentController::class, 'index'])->name('content.index');
    Route::put('/content/{section}', [ContentController::class, 'update'])->name('content.update');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/{section}', [SettingController::class, 'index'])->name('settings.show')->where('section', 'branding|email|store|payments');
    Route::post('/settings/email', [SettingController::class, 'updateEmail'])->name('settings.email');
    Route::post('/settings/store', [SettingController::class, 'updateStore'])->name('settings.store');
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

    // ── Phase 3: Commissions, Earnings & Payouts ──────────────────────
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\CommissionController::class, 'update'])->name('update');
    });

    Route::prefix('earnings')->name('earnings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\EarningsController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Admin\EarningsController::class, 'export'])->name('export');
    });

    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\PayoutController::class, 'store'])->name('store');
        Route::post('/{payout}/approve', [\App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('approve');
        Route::post('/{payout}/reject', [\App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('reject');
        Route::post('/{payout}/pay', [\App\Http\Controllers\Admin\PayoutController::class, 'pay'])->name('pay');
    });

    // ── Phase 5: Shipping & Tax ───────────────────────────────────────
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('index');
        Route::post('/zones', [\App\Http\Controllers\Admin\ShippingController::class, 'storeZone'])->name('zones.store');
        Route::put('/zones/{zone}', [\App\Http\Controllers\Admin\ShippingController::class, 'updateZone'])->name('zones.update');
        Route::delete('/zones/{zone}', [\App\Http\Controllers\Admin\ShippingController::class, 'destroyZone'])->name('zones.destroy');
        Route::post('/zones/{zone}/methods', [\App\Http\Controllers\Admin\ShippingController::class, 'storeMethod'])->name('methods.store');
        Route::put('/methods/{method}', [\App\Http\Controllers\Admin\ShippingController::class, 'updateMethod'])->name('methods.update');
        Route::delete('/methods/{method}', [\App\Http\Controllers\Admin\ShippingController::class, 'destroyMethod'])->name('methods.destroy');
    });

    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TaxController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\TaxController::class, 'update'])->name('update');
    });

    // ── Phase 7: Operations ───────────────────────────────────────────
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [\App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [\App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
        Route::post('/adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('adjust');
    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
        Route::get('/canned', [\App\Http\Controllers\Admin\CannedReplyController::class, 'index'])->name('canned');
        Route::post('/canned', [\App\Http\Controllers\Admin\CannedReplyController::class, 'store'])->name('canned.store');
        Route::put('/canned/{canned}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'update'])->name('canned.update');
        Route::delete('/canned/{canned}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'destroy'])->name('canned.destroy');
        Route::get('/create', [\App\Http\Controllers\Admin\TicketController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
        Route::put('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'update'])->name('update');
    });

    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReturnController::class, 'index'])->name('index');
        Route::get('/{return}', [\App\Http\Controllers\Admin\ReturnController::class, 'show'])->name('show');
        Route::put('/{return}', [\App\Http\Controllers\Admin\ReturnController::class, 'update'])->name('update');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/export/sales', [\App\Http\Controllers\Admin\ReportController::class, 'exportSales'])->name('export.sales');
        Route::get('/export/segments', [\App\Http\Controllers\Admin\ReportController::class, 'exportSegments'])->name('export.segments');
    });

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('store');
        Route::delete('/{staff}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('destroy');
    });

    // ── Phase 8: Automation, Growth & Security ────────────────────────
    Route::prefix('automations')->name('automations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AutomationController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AutomationController::class, 'store'])->name('store');
        Route::put('/{automation}', [\App\Http\Controllers\Admin\AutomationController::class, 'update'])->name('update');
        Route::delete('/{automation}', [\App\Http\Controllers\Admin\AutomationController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CampaignController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\CampaignController::class, 'store'])->name('store');
        Route::post('/{campaign}/send', [\App\Http\Controllers\Admin\CampaignController::class, 'send'])->name('send');
    });
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LoyaltyController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\LoyaltyController::class, 'update'])->name('update');
    });
    Route::prefix('redirects')->name('redirects.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RedirectController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\RedirectController::class, 'store'])->name('store');
        Route::put('/{redirect}', [\App\Http\Controllers\Admin\RedirectController::class, 'update'])->name('update');
        Route::delete('/{redirect}', [\App\Http\Controllers\Admin\RedirectController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BlogController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\BlogController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\BlogController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [\App\Http\Controllers\Admin\BlogController::class, 'edit'])->name('edit');
        Route::put('/{post}', [\App\Http\Controllers\Admin\BlogController::class, 'update'])->name('update');
        Route::delete('/{post}', [\App\Http\Controllers\Admin\BlogController::class, 'destroy'])->name('destroy');
    });
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\PlanController::class, 'store'])->name('store');
        Route::put('/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'destroy'])->name('destroy');
    });
});


// HRM module (placeholder shell)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin/hrm')->name('admin.hrm.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\HrmController::class, 'index'])->name('index');
});

// ── Vendor panel ──────────────────────────────────────────────────────────
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Public application
    Route::get('/register', [\App\Http\Controllers\Vendor\RegistrationController::class, 'show'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Vendor\RegistrationController::class, 'register'])->name('register.submit');

    // Authenticated vendor area → unified admin panel (permission-scoped)
    Route::middleware(['auth', 'vendor'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    });
});

// ── Public vendors directory + storefront ──────────────────────────────────
Route::get('/vendors', [\App\Http\Controllers\Shop\VendorStoreController::class, 'index'])->name('shop.vendors');
Route::get('/vendor/{slug}', [\App\Http\Controllers\Shop\VendorStoreController::class, 'show'])->name('shop.vendor');
Route::get('/download/{item}', [\App\Http\Controllers\Shop\DownloadController::class, 'download'])->name('shop.download');
Route::post('/order/{order}/return', [\App\Http\Controllers\Shop\ReturnRequestController::class, 'store'])->name('shop.return.store');


// ── Two-factor authentication ───────────────────────────────────────────────
Route::get('/two-factor/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
Route::post('/two-factor/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('two-factor.verify');
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [\App\Http\Controllers\Auth\TwoFactorController::class, 'settings'])->name('two-factor.settings');
    Route::post('/two-factor/enable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/disable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

// ── Public blog ─────────────────────────────────────────────────────────────
Route::get('/blog', [\App\Http\Controllers\Shop\BlogController::class, 'index'])->name('shop.blog');
Route::get('/blog/{slug}', [\App\Http\Controllers\Shop\BlogController::class, 'show'])->name('shop.blog.show');

require __DIR__ . '/auth.php';
