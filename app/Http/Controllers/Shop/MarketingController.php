<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Marketing;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    /** Base-currency subtotal of the current session cart. */
    private function cartSubtotalBase(): float
    {
        $cart = session('cart', []);
        return (float) array_sum(array_map(fn ($i) => (float) $i['price'] * (int) $i['quantity'], $cart));
    }

    /** USD subtotal (coupons are defined in USD). */
    private function subtotalUsd(float $base): float
    {
        try {
            $rate = (float) (\App\Support\Currency::all()['USD']['rate'] ?? 1); // base -> USD
        } catch (\Throwable $e) {
            $rate = 1;
        }
        return $base * ($rate > 0 ? $rate : 1);
    }

    /** POST /coupon/apply */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:40']);
        $base = $this->cartSubtotalBase();
        if ($base <= 0) {
            return response()->json(['ok' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $res = Marketing::validateCoupon($request->input('code'), $this->subtotalUsd($base), optional($request->user())->email);
        if (!$res['ok']) {
            session()->forget('coupon');
            return response()->json(['ok' => false, 'message' => $res['message']], 422);
        }

        session(['coupon' => strtoupper(trim($request->input('code')))]);
        $discountBase = zusd($res['discount']);
        $ship         = zshipping($base);

        return response()->json([
            'ok'            => true,
            'message'       => 'Coupon applied — ' . $res['label'] . '.',
            'label'         => $res['label'],
            'freeship'      => $res['freeship'],
            'discountBase'  => round($discountBase, 2),
            'discount'      => zprice($discountBase),
            'shipAfter'     => $res['freeship'] ? 0 : $ship,
            'totalBase'     => round(max(0, $base - $discountBase) + ($res['freeship'] ? 0 : $ship), 2),
            'total'         => zprice(max(0, $base - $discountBase) + ($res['freeship'] ? 0 : $ship)),
        ]);
    }

    /** POST /coupon/remove */
    public function removeCoupon(Request $request)
    {
        session()->forget('coupon');
        $base = $this->cartSubtotalBase();
        $ship = zshipping($base);
        return response()->json([
            'ok'        => true,
            'message'   => 'Coupon removed.',
            'totalBase' => round($base + $ship, 2),
            'total'     => zprice($base + $ship),
            'ship'      => $ship,
        ]);
    }

    /** POST /wheel/spin */
    public function spin(Request $request)
    {
        if (!Marketing::wheelEnabled()) {
            return response()->json(['ok' => false, 'message' => 'The prize wheel is currently closed.'], 422);
        }
        $request->validate(['email' => 'required|email|max:190']);
        $email = strtolower(trim($request->input('email')));

        if (Marketing::alreadySpun($email)) {
            return response()->json(['ok' => false, 'message' => 'This email has already claimed a spin. One prize per email.'], 422);
        }

        $result = Marketing::spin($email, (string) $request->ip());

        return response()->json(array_merge(['ok' => true], $result));
    }
}
