<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.shopnest');

        // Always drive mail from Admin → Settings → Email (DB), for every outgoing email.
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \App\Models\Setting::applyMailConfig();
            }
        } catch (\Throwable $e) {
            // never block boot over mail config
        }

        // ── RBAC gates: master admin passes everything; others by role permissions ──
        Gate::before(fn ($user) => (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? true : null);
        foreach (array_keys(config('permissions', [])) as $module) {
            foreach (config("permissions.$module.actions", []) as $action) {
                $perm = "$module.$action";
                Gate::define($perm, fn ($user) => $user->hasPermission($perm));
            }
        }

        // ── Audit logging for key models ──
        foreach ([
            \App\Models\Product::class, \App\Models\Order::class, \App\Models\Vendor::class,
            \App\Models\Category::class, \App\Models\Role::class, \App\Models\User::class,
            \App\Models\SubscriptionPlan::class, \App\Models\ReturnRequest::class, \App\Models\PayoutRequest::class,
            \App\Models\ShippingZone::class, \App\Models\AutomationRule::class, \App\Models\BlogPost::class,
        ] as $auditModel) {
            if (class_exists($auditModel)) {
                $auditModel::observe(\App\Observers\AuditObserver::class);
            }
        }

        // Shared data for the storefront layout: category navigation + cart count
        View::composer('layouts.store', function ($view) {
            $navCategories = Category::whereNull('parent_id')
                ->where('status', 'active')
                ->with('childrenRecursive')
                ->orderBy('name')
                ->get();
            $countMap = Category::productCountMap();
            $navCategories->each(fn ($c) => $c->assignCounts($countMap));

            $cart      = session('cart', []);
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart));

            $view->with(compact('navCategories', 'cartCount', 'cartTotal'));
        });
    }
}
