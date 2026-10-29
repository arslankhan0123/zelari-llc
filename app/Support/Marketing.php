<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\WheelEntry;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Coupon validation + Spin-the-Wheel engine.
 * All money values here are in USD (the admin-facing base), matching how
 * coupons are stored; callers convert to the store's base currency with zusd().
 */
class Marketing
{
    /* ───────── Wheel config (stored in settings) ───────── */

    public static function wheelEnabled(): bool
    {
        try {
            return (string) Setting::get('wheel_enabled', '1') !== '0';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Default prize segments — mostly winning, to keep it engaging. */
    public static function defaultSegments(): array
    {
        return [
            ['label' => '5% OFF',        'type' => 'percent',  'value' => 5,  'weight' => 20, 'color' => '#6366f1'],
            ['label' => '10% OFF',       'type' => 'percent',  'value' => 10, 'weight' => 18, 'color' => '#ec4899'],
            ['label' => 'Free Shipping', 'type' => 'freeship', 'value' => 0,  'weight' => 14, 'color' => '#14b8a6'],
            ['label' => '15% OFF',       'type' => 'percent',  'value' => 15, 'weight' => 12, 'color' => '#f59e0b'],
            ['label' => 'Try Again',     'type' => 'none',     'value' => 0,  'weight' => 10, 'color' => '#64748b'],
            ['label' => '$5 OFF',        'type' => 'fixed',    'value' => 5,  'weight' => 14, 'color' => '#8b5cf6'],
            ['label' => '20% OFF',       'type' => 'percent',  'value' => 20, 'weight' => 7,  'color' => '#ef4444'],
            ['label' => 'Try Again',     'type' => 'none',     'value' => 0,  'weight' => 5,  'color' => '#475569'],
        ];
    }

    public static function segments(): array
    {
        try {
            $raw = Setting::get('wheel_segments', null);
            $arr = $raw ? json_decode((string) $raw, true) : null;
            if (is_array($arr) && count($arr) >= 2) {
                return $arr;
            }
        } catch (\Throwable $e) {
        }

        return self::defaultSegments();
    }

    public static function wheelConfig(): array
    {
        return [
            'enabled'   => self::wheelEnabled(),
            'title'     => Setting::get('wheel_title', 'Win a discount!'),
            'subtitle'  => Setting::get('wheel_subtitle', 'Enter your email and spin the wheel for an exclusive coupon code.'),
            'expiryDays'=> (int) Setting::get('wheel_expiry_days', 7),
            'minSpend'  => (float) Setting::get('wheel_min_spend', 0),
            'segments'  => array_values(array_map(fn ($s) => [
                'label' => $s['label'] ?? '',
                'color' => $s['color'] ?? '#6366f1',
            ], self::segments())),
        ];
    }

    /** Weighted-random pick → index into the segments array. */
    public static function pickSegment(array $segments): int
    {
        $total = array_sum(array_map(fn ($s) => max(0, (float) ($s['weight'] ?? 1)), $segments));
        if ($total <= 0) {
            return random_int(0, count($segments) - 1);
        }
        $r   = mt_rand() / mt_getrandmax() * $total;
        $acc = 0;
        foreach ($segments as $i => $s) {
            $acc += max(0, (float) ($s['weight'] ?? 1));
            if ($r <= $acc) {
                return $i;
            }
        }

        return count($segments) - 1;
    }

    /** Has this email already spun? (one spin per email) */
    public static function alreadySpun(string $email): bool
    {
        try {
            return WheelEntry::where('email', $email)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run a spin for an email: pick a prize, create a coupon if they won,
     * record the entry. Returns the result payload for the frontend.
     */
    public static function spin(string $email, ?string $ip = null): array
    {
        $segments = self::segments();
        $idx      = self::pickSegment($segments);
        $seg      = $segments[$idx];
        $won      = ($seg['type'] ?? 'none') !== 'none';

        $couponId = null;
        $code     = null;
        $expires  = null;

        if ($won) {
            $expiryDays = max(1, (int) Setting::get('wheel_expiry_days', 7));
            $expires    = now()->addDays($expiryDays);
            $code       = self::uniqueCode('SPIN');

            $coupon = Coupon::create([
                'code'         => $code,
                'type'         => $seg['type'],
                'value'        => (float) ($seg['value'] ?? 0),
                'min_spend'    => (float) Setting::get('wheel_min_spend', 0),
                'max_discount' => null,
                'usage_limit'  => 1,
                'used_count'   => 0,
                'expires_at'   => $expires,
                'active'       => true,
                'source'       => 'wheel',
                'email'        => $email,
            ]);
            $couponId = $coupon->id;
        }

        WheelEntry::create([
            'email'       => $email,
            'prize_label' => $seg['label'] ?? '',
            'coupon_id'   => $couponId,
            'ip'          => $ip,
        ]);

        return [
            'index'     => $idx,
            'won'       => $won,
            'label'     => $seg['label'] ?? '',
            'code'      => $code,
            'expiresAt' => $expires ? $expires->toDateString() : null,
        ];
    }

    public static function uniqueCode(string $prefix): string
    {
        do {
            $code = $prefix . '-' . strtoupper(Str::random(6));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /* ───────── Coupon validation / application ───────── */

    /**
     * Validate a coupon code against an order subtotal (USD).
     * Returns ['ok'=>true, 'coupon'=>Coupon, 'discount'=>float(USD), 'freeship'=>bool, 'label'=>string]
     * or ['ok'=>false, 'message'=>string].
     */
    public static function validateCoupon(string $code, float $subtotalUsd, ?string $email = null): array
    {
        $code   = trim(strtoupper($code));
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$coupon || !$coupon->active) {
            return ['ok' => false, 'message' => 'This coupon code is not valid.'];
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return ['ok' => false, 'message' => 'This coupon has expired.'];
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return ['ok' => false, 'message' => 'This coupon has already been used.'];
        }
        if ($subtotalUsd + 0.001 < (float) $coupon->min_spend) {
            return ['ok' => false, 'message' => 'Add more to your cart to use this coupon (minimum spend $' . number_format((float) $coupon->min_spend, 2) . ').'];
        }

        $discount = 0.0;
        $freeship = false;
        if ($coupon->type === 'percent') {
            $discount = $subtotalUsd * ((float) $coupon->value) / 100;
            if ($coupon->max_discount !== null) {
                $discount = min($discount, (float) $coupon->max_discount);
            }
        } elseif ($coupon->type === 'fixed') {
            $discount = (float) $coupon->value;
        } elseif ($coupon->type === 'freeship') {
            $freeship = true;
        }
        $discount = round(min($discount, $subtotalUsd), 2);

        return [
            'ok'       => true,
            'coupon'   => $coupon,
            'discount' => $discount,
            'freeship' => $freeship,
            'label'    => self::couponLabel($coupon),
        ];
    }

    public static function couponLabel(Coupon $c): string
    {
        if ($c->type === 'percent')  return rtrim(rtrim(number_format((float) $c->value, 2), '0'), '.') . '% off';
        if ($c->type === 'fixed')    return '$' . number_format((float) $c->value, 2) . ' off';
        if ($c->type === 'freeship') return 'Free shipping';

        return 'Discount';
    }
}
