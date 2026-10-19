<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
