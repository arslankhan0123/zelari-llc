<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;

        $stats = ['products' => 0, 'active' => 0];
        if ($vendor) {
            try {
                $stats['products'] = $vendor->products()->count();
                $stats['active']   = $vendor->products()->where('status', 'active')->count();
            } catch (\Throwable $e) {}
        }

        return view('vendor.dashboard', compact('vendor', 'stats'));
    }
}
