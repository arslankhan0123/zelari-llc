<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth();
        $prevStart  = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd    = $monthStart->copy()->subSecond();
        $twelveAgo  = $now->copy()->subMonths(11)->startOfMonth();

        // Revenue counts every order that wasn't cancelled (COD included; it's money owed/collected)
        $sales = Order::where('status', '!=', 'cancelled');

        $revenueTotal = (float) (clone $sales)->sum('total');
        $revenueThis  = (float) (clone $sales)->where('created_at', '>=', $monthStart)->sum('total');
        $revenuePrev  = (float) (clone $sales)->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');

        $ordersTotal = Order::count();
        $ordersThis  = Order::where('created_at', '>=', $monthStart)->count();
        $ordersPrev  = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $productsTotal = Product::count();
        $productsWeek  = Product::where('created_at', '>=', $now->copy()->startOfWeek())->count();

        $customersTotal = Customer::count();
        $customersThis  = Customer::where('created_at', '>=', $monthStart)->count();
        $customersPrev  = Customer::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        // 12-month sparklines (oldest → newest)
        $months = collect(range(11, 0))->map(fn ($i) => $now->copy()->subMonths($i)->format('Y-m'));
        $byMonth = function ($query, string $agg = 'count', string $col = 'id') use ($twelveAgo, $months) {
            $rows = $query->where('created_at', '>=', $twelveAgo)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, " . ($agg === 'sum' ? "SUM($col)" : 'COUNT(*)') . ' as v')
                ->groupBy('ym')->pluck('v', 'ym');
            return $months->map(fn ($m) => (float) ($rows[$m] ?? 0))->values();
        };
        $spark = [
            'revenue'   => $byMonth(Order::where('status', '!=', 'cancelled'), 'sum', 'total'),
            'orders'    => $byMonth(Order::query()),
            'products'  => $byMonth(Product::query()),
            'customers' => $byMonth(Customer::query()),
        ];

        $recentOrders = Order::with('customer')->withCount('items')->latest()->take(5)->get();

        $topCategories = Category::withCount(['products', 'children'])->orderByDesc('products_count')->take(5)->get();

        // Bottom tiles
        $salesToday     = (float) (clone $sales)->whereDate('created_at', $now->toDateString())->sum('total');
        $salesYesterday = (float) (clone $sales)->whereDate('created_at', $now->copy()->subDay()->toDateString())->sum('total');
        $pendingOrders  = Order::where('status', 'pending')->count();
        $unpaidOrders   = Order::where('status', '!=', 'cancelled')->where('payment_status', 'unpaid')->count();
        $lowStock       = Product::where('status', 'active')->where('quantity', '<=', 5)->count();
        $outOfStock     = Product::where('status', 'active')->where('quantity', '<=', 0)->count();

        $paidToday = $paidTodayCount = 0;
        if (Schema::hasTable('payments')) {
            $pt = Payment::where('status', 'paid')->whereDate('paid_at', $now->toDateString());
            $paidTodayCount = (clone $pt)->count();
            // payments.amount is in each payment's own currency; use the order's base total instead
            $paidToday = (float) Order::whereIn('id', (clone $pt)->pluck('order_id'))->sum('total');
        }

        return view('dashboard', [
            'revenueTotal'   => $revenueTotal,
            'revenueDelta'   => $this->delta($revenueThis, $revenuePrev),
            'ordersTotal'    => $ordersTotal,
            'ordersDelta'    => $this->delta($ordersThis, $ordersPrev),
            'productsTotal'  => $productsTotal,
            'productsWeek'   => $productsWeek,
            'customersTotal' => $customersTotal,
            'customersDelta' => $this->delta($customersThis, $customersPrev),
            'spark'          => $spark,
            'recentOrders'   => $recentOrders,
            'topCategories'  => $topCategories,
            'salesToday'     => $salesToday,
            'salesDelta'     => $this->delta($salesToday, $salesYesterday),
            'pendingOrders'  => $pendingOrders,
            'unpaidOrders'   => $unpaidOrders,
            'lowStock'       => $lowStock,
            'outOfStock'     => $outOfStock,
            'paidToday'      => $paidToday,
            'paidTodayCount' => $paidTodayCount,
        ]);
    }

    /** % change, or null when there's nothing to compare against. */
    private function delta(float $now, float $prev): ?float
    {
        if ($prev <= 0) return $now > 0 ? null : 0.0;
        return round(($now - $prev) / $prev * 100, 1);
    }
}
