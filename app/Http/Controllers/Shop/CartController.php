<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /** GET /cart */
    public function index()
    {
        $cart = session('cart', []);
        return view('shop.cart', compact('cart'));
    }

    /** POST /cart/add */
    public function add(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
            'quantity'     => 'required|integer|min:1|max:100',
        ]);

        $product = Product::where('id', $request->product_id)
            ->where('status', 'active')
            ->firstOrFail();

        $qty = (int) $request->quantity;

        // Resolve a chosen variation, if any
        $variation = null;
        if ($request->filled('variation_id')) {
            $variation = \App\Models\ProductVariation::where('id', $request->variation_id)
                ->where('product_id', $product->id)->where('enabled', true)->first();
        }
        $effStock = $variation ? (int) $variation->quantity : (int) $product->quantity;
        $effPrice = $variation ? $variation->effectivePrice() : (float) ($product->sale_price ?? $product->purchase_price);
        $effImage = ($variation && $variation->image) ? $variation->image : $product->feature_image;
        $label    = $variation ? $variation->label() : null;
        $dispName = $product->name . ($label ? " ($label)" : '');
        $key      = $variation ? ($product->id . ':' . $variation->id) : (string) $product->id;

        if ($effStock <= 0) {
            $msg = "Sorry, \"{$dispName}\" is out of stock.";
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('cart_error', $msg);
        }

        $cart   = session('cart', []);
        $notice = null;

        if (isset($cart[$key])) {
            $wanted = $cart[$key]['quantity'] + $qty;
            $newQty = min($wanted, $effStock);
            if ($wanted > $effStock) {
                $notice = "You asked for {$wanted}, but only {$effStock} available — quantity set to {$effStock}.";
            }
            $cart[$key]['quantity']     = $newQty;
            $cart[$key]['max_quantity'] = $effStock;
        } else {
            if ($qty > $effStock) {
                $notice = "You asked for {$qty}, but only {$effStock} available — quantity set to {$effStock}.";
                $qty = $effStock;
            }
            $cart[$key] = [
                'product_id'      => $product->id,
                'variation_id'    => $variation?->id,
                'variation_label' => $label,
                'name'            => $dispName,
                'slug'            => $product->slug,
                'price'           => $effPrice,
                'image'           => $effImage,
                'quantity'        => $qty,
                'max_quantity'    => $effStock,
            ];
        }

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json([
                'count'    => $this->cartCount($cart),
                'total'    => array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart)),
                'name'     => $product->name,
                'quantity' => $cart[$key]['quantity'],
                'notice'   => $notice,
                'message'  => 'Added to cart!',
            ]);
        }

        $flash = back()->with('cart_success', "\"{$product->name}\" added to your cart.");
        return $notice ? $flash->with('info', $notice) : $flash;
    }

    /** POST /cart/update */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',   // cart key: "<productId>" or "<productId>:<variationId>"
            'quantity'   => 'required|integer|min:0|max:100',
        ]);

        $cart      = session('cart', []);
        $key       = (string) $request->product_id;
        $requested = (int) $request->quantity;
        $notice    = null;
        $removed   = false;
        $final     = 0;

        if (str_contains($key, ':')) {
            [$pid, $vid] = explode(':', $key, 2);
            $stock = (int) (\App\Models\ProductVariation::where('id', $vid)->value('quantity') ?? 0);
        } else {
            $stock = (int) (Product::where('id', $key)->value('quantity') ?? 0);
        }

        if (isset($cart[$key])) {
            if ($requested === 0 || $stock <= 0) {
                unset($cart[$key]);
                $removed = true;
                if ($stock <= 0 && $requested > 0) {
                    $notice = 'This item just went out of stock and was removed from your cart.';
                }
            } else {
                $final = min($requested, $stock);
                if ($requested > $stock) {
                    $notice = "You asked for {$requested}, but only {$stock} available — quantity set to {$stock}.";
                }
                $cart[$key]['quantity']     = $final;
                $cart[$key]['max_quantity'] = $stock;
            }
            session(['cart' => $cart]);
        }

        if ($request->wantsJson()) {
            $item  = $cart[$key] ?? null;
            $total = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart));
            return response()->json([
                'removed'    => $removed,
                'quantity'   => $final,
                'stock'      => $stock,
                'line_total' => $item ? $item['price'] * $item['quantity'] : 0,
                'count'      => $this->cartCount($cart),
                'total'      => $total,
                'empty'      => empty($cart),
                'notice'     => $notice,
            ]);
        }

        return $notice
            ? redirect()->route('cart.index')->with('info', $notice)
            : redirect()->route('cart.index');
    }

    /** DELETE /cart/remove/{productId} */
    public function remove(string $productId)
    {
        $cart = session('cart', []);
        unset($cart[(string) $productId]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('cart_success', 'Item removed from cart.');
    }

    /** DELETE /cart/clear */
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index');
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function cartCount(array $cart): int
    {
        return array_sum(array_column($cart, 'quantity'));
    }
}
