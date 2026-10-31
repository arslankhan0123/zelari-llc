<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ContactMessage;

class HomeController extends Controller
{
    /** GET /dashboard — generic landing that links out to each module */
    public function index()
    {
        $s = [
            'revenue'   => 0.0,
            'orders'    => 0,
            'pending'   => 0,
            'products'  => 0,
            'lowStock'  => 0,
            'customers' => 0,
            'messages'  => 0,
        ];

        try { $s['revenue']   = (float) Order::where('status', '!=', 'cancelled')->sum('total'); } catch (\Throwable $e) {}
        try { $s['orders']    = Order::count(); } catch (\Throwable $e) {}
        try { $s['pending']   = Order::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $s['products']  = Product::count(); } catch (\Throwable $e) {}
        try { $s['lowStock']  = Product::where('status', 'active')->where('quantity', '<=', 5)->count(); } catch (\Throwable $e) {}
        try { $s['customers'] = Customer::count(); } catch (\Throwable $e) {}
        try { $s['messages']  = ContactMessage::where('status', 'new')->count(); } catch (\Throwable $e) {}

        return view('admin.home', ['s' => $s]);
    }
}
