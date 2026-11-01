<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $vid = auth()->user()->vendorId();
        $threshold = (int) $request->get('threshold', 5);
        $lowStock = Product::with('vendor')->where('quantity', '<=', $threshold)
            ->when($vid, fn ($q) => $q->where('vendor_id', $vid))
            ->orderBy('quantity')->paginate(20)->withQueryString();
        $movements = StockMovement::with('product:id,name', 'user:id,name')
            ->when($vid, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('vendor_id', $vid)))
            ->latest()->take(30)->get();
        $products = Product::when($vid, fn ($q) => $q->where('vendor_id', $vid))
            ->orderBy('name')->get(['id', 'name', 'quantity']);
        return view('admin.inventory.index', compact('lowStock', 'movements', 'products', 'threshold'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'change'       => 'required|integer',
            'type'         => 'required|in:adjust,restock',
            'reason'       => 'nullable|string|max:255',
        ]);

        $vid = auth()->user()->vendorId();
        if ($vid) {
            abort_unless(Product::where('id', $data['product_id'])->where('vendor_id', $vid)->exists(), 403);
        }

        $variation = $data['variation_id'] ? ProductVariation::find($data['variation_id']) : null;
        $target = $variation ?: Product::find($data['product_id']);

        $newQty = max(0, (int) $target->quantity + (int) $data['change']);
        $target->forceFill(['quantity' => $newQty])->save();

        StockMovement::create([
            'product_id'   => $data['product_id'],
            'variation_id' => $data['variation_id'] ?? null,
            'change'       => (int) $data['change'],
            'balance'      => $newQty,
            'type'         => $data['type'],
            'reason'       => $data['reason'] ?? null,
            'user_id'      => auth()->id(),
        ]);

        return back()->with('success', 'Stock adjusted. New quantity: ' . $newQty);
    }
}
