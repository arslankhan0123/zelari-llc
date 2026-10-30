<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Marketing;
use App\Models\Setting;
use App\Mail\CouponWon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        if (!$request->user()) {
            return response()->json(['ok' => false, 'message' => 'Please log in to apply a coupon.', 'login' => true], 401);
        }

        $res = Marketing::validateCoupon($request->input('code'), $this->subtotalUsd($base), $request->user()->id);
        if (!$res['ok']) {
            session()->forget('coupon');
            return response()->json(['ok' => false, 'message' => $res['message']], 422);
        }

        session(['coupon' => strtoupper(trim($request->input('code')))]);
        $discountBase = Marketing::discountBase($res, $base);
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

    /** GET /spin-wheel — the dedicated prize-wheel page. */
    public function page(Request $request)
    {
        if (!Marketing::wheelEnabled()) {
            return redirect()->route('home')->with('ztoast', 'The prize wheel is currently closed. Please check back later.');
        }

        $myCoupons = collect();
        $spun = false;
        if ($user = $request->user()) {
            $spun = Marketing::alreadySpun($user);
            $myCoupons = \App\Models\Coupon::where('user_id', $user->id)
                ->orderByDesc('id')->take(12)->get();
        }

        return view('spinwheel.index', [
            'wcfg'      => Marketing::wheelConfig(),
            'spun'      => $spun,
            'myCoupons' => $myCoupons,
        ]);
    }

    /** POST /wheel/spin */
    public function spin(Request $request)
    {
        if (!Marketing::wheelEnabled()) {
            return response()->json(['ok' => false, 'message' => 'The prize wheel is currently closed.'], 422);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Please log in to spin and claim your coupon.', 'login' => true], 401);
        }
        if (Marketing::alreadySpun($user)) {
            return response()->json(['ok' => false, 'message' => 'You have already claimed your spin. One prize per account.'], 422);
        }

        $result = Marketing::spin($user, (string) $request->ip());

        // Email the won coupon via the store's SMTP settings — AFTER the response is
        // sent, so a slow or misconfigured SMTP server never delays or breaks the spin.
        if (($result['won'] ?? false) && !empty($result['coupon'])) {
            $coupon = $result['coupon'];
            $email  = $user->email;
            $name   = $user->name ?? '';
            $label  = $result['label'] ?? '';
            dispatch(function () use ($coupon, $email, $name, $label) {
                try {
                    if (Setting::applyMailConfig()) {
                        Mail::to($email, $name)->send(new CouponWon($coupon, $label, $name));
                        Setting::recordMailResult(null);
                    }
                } catch (\Throwable $e) {
                    report($e);
                    Setting::recordMailResult('Coupon email: ' . $e->getMessage());
                }
            })->afterResponse();
            $result['emailed'] = true;
        }

        unset($result['coupon']); // don't leak the model to the browser

        return response()->json(array_merge(['ok' => true], $result));
    }
}
