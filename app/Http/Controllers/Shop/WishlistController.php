<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Session-based wishlist — works for guests and logged-in users alike.
 * Product ids live in session('wishlist'); hearts across the site stay
 * in sync because every view reads the same session array.
 */
class WishlistController extends Controller
{
    private function ids(): array
    {
        return array_values(array_unique(array_map('intval', (array) session('wishlist', []))));
    }

    public function index()
    {
        $ids = $this->ids();
        $products = Product::whereIn('id', $ids)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->get()
            ->sortByDesc(fn ($p) => array_search($p->id, $ids))
            ->values();

        // prune ids whose products were removed/deactivated
        session(['wishlist' => $products->pluck('id')->all()]);

        $cats = $products->pluck('category')->filter()->unique('id')->sortBy('name')->values();

        return view('shop.wishlist', compact('products', 'cats'));
    }

    /** Add/remove one product; also used as one-by-one delete. */
    public function toggle(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $ids  = $this->ids();
        $id   = (int) $data['product_id'];

        if (in_array($id, $ids, true)) {
            $ids   = array_values(array_diff($ids, [$id]));
            $added = false;
        } else {
            $ids[] = $id;
            $added = true;
        }
        session(['wishlist' => $ids]);

        return response()->json(['ok' => true, 'added' => $added, 'count' => count($ids)]);
    }

    public function clear()
    {
        session()->forget('wishlist');
        return redirect()->route('wishlist.index')->with('success', 'Wishlist cleared.');
    }
}
