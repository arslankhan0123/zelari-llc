<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ContactMessage;

class HomeController extends Controller
{
    /** GET /dashboard — generic landing with a live overview */
    public function index()
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        $s = [
            'revenue' => 0.0, 'revenueMonth' => 0.0, 'orders' => 0, 'ordersMonth' => 0,
            'pending' => 0, 'unpaid' => 0, 'products' => 0, 'lowStock' => 0,
            'customers' => 0, 'messages' => 0, 'salesToday' => 0.0,
        ];
        $sales = fn () => Order::where('status', '!=', 'cancelled');

        try { $s['revenue']      = (float) $sales()->sum('total'); } catch (\Throwable $e) {}
        try { $s['revenueMonth'] = (float) $sales()->where('created_at', '>=', $monthStart)->sum('total'); } catch (\Throwable $e) {}
        try { $s['salesToday']   = (float) $sales()->whereDate('created_at', $now->toDateString())->sum('total'); } catch (\Throwable $e) {}
        try { $s['orders']       = Order::count(); } catch (\Throwable $e) {}
        try { $s['ordersMonth']  = Order::where('created_at', '>=', $monthStart)->count(); } catch (\Throwable $e) {}
        try { $s['pending']      = Order::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $s['unpaid']       = Order::where('status', '!=', 'cancelled')->where('payment_status', 'unpaid')->count(); } catch (\Throwable $e) {}
        try { $s['products']     = Product::count(); } catch (\Throwable $e) {}
        try { $s['lowStock']     = Product::where('status', 'active')->where('quantity', '<=', 5)->count(); } catch (\Throwable $e) {}
        try { $s['customers']    = Customer::count(); } catch (\Throwable $e) {}
        try { $s['messages']     = ContactMessage::where('status', 'new')->count(); } catch (\Throwable $e) {}

        // 12-month revenue trend
        $monthly = collect();
        try {
            $twelveAgo = $now->copy()->subMonths(11)->startOfMonth();
            $rev = $sales()->where('created_at', '>=', $twelveAgo)
                ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') ym, SUM(total) v")
                ->groupBy('ym')->pluck('v', 'ym');
            $monthly = collect(range(11, 0))->map(function ($i) use ($now, $rev) {
                $m = $now->copy()->subMonths($i);
                return ['label' => $m->format('M'), 'full' => $m->format('M Y'), 'val' => (float) ($rev[$m->format('Y-m')] ?? 0)];
            });
        } catch (\Throwable $e) {}

        // recent orders
        $recent = collect();
        try { $recent = Order::with('customer')->latest()->take(6)->get(); } catch (\Throwable $e) {}

        return view('admin.home', ['s' => $s, 'monthly' => $monthly, 'recent' => $recent]);
    }
}
