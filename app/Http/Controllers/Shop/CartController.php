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
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        $product = Product::where('id', $request->product_id)
            ->where('status', 'active')
            ->firstOrFail();

        $qty = (int) $request->quantity;

        if ($product->quantity <= 0) {
            $msg = "Sorry, \"{$product->name}\" is out of stock.";
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('cart_error', $msg);
        }

        $cart   = session('cart', []);
        $key    = (string) $product->id;
        $notice = null;

        if (isset($cart[$key])) {
            $wanted = $cart[$key]['quantity'] + $qty;
            $newQty = min($wanted, $product->quantity);
            if ($wanted > $product->quantity) {
                $notice = "You asked for {$wanted}, but only {$product->quantity} available — quantity set to {$product->quantity}.";
            }
            $cart[$key]['quantity']     = $newQty;
            $cart[$key]['max_quantity'] = $product->quantity;
        } else {
            if ($qty > $product->quantity) {
                $notice = "You asked for {$qty}, but only {$product->quantity} available — quantity set to {$product->quantity}.";
                $qty = $product->quantity;
            }
            $cart[$key] = [
                'product_id'   => $product->id,
                'name'         => $product->name,
                'slug'         => $product->slug,
                'price'        => (float) ($product->sale_price ?? $product->purchase_price),
                'image'        => $product->feature_image,
                'quantity'     => $qty,
                'max_quantity' => $product->quantity,
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
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:0|max:100',
        ]);

        $cart      = session('cart', []);
        $key       = (string) $request->product_id;
        $requested = (int) $request->quantity;
        $notice    = null;
        $removed   = false;
        $final     = 0;
        $stock     = (int) (Product::where('id', $request->product_id)->value('quantity') ?? 0);

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
    public function remove(int $productId)
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
