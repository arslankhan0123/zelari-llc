<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Vendor;

class VendorStoreController extends Controller
{
    /** Public vendors directory: /vendors */
    public function index()
    {
        $vendors = Vendor::where('status', Vendor::APPROVED)
            ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return view('shop.vendors', compact('vendors'));
    }

    /** Public vendor storefront: /vendor/{slug} */
    public function show(string $slug)
    {
        $vendor = Vendor::where('slug', $slug)
            ->where('status', Vendor::APPROVED)
            ->firstOrFail();

        $products = $vendor->products()
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->latest()
            ->paginate(12);

        return view('shop.vendor', compact('vendor', 'products'));
    }
}
