<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\WheelEntry;
use App\Models\Setting;
use App\Models\User;
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
            ['label' => '3% OFF',        'type' => 'percent',  'value' => 3,  'weight' => 24, 'color' => '#6366f1'],
            ['label' => '5% OFF',        'type' => 'percent',  'value' => 5,  'weight' => 22, 'color' => '#ec4899'],
            ['label' => '8% OFF',        'type' => 'percent',  'value' => 8,  'weight' => 14, 'color' => '#8b5cf6'],
            ['label' => '10% OFF',       'type' => 'percent',  'value' => 10, 'weight' => 10, 'color' => '#f59e0b'],
            ['label' => 'Free Shipping', 'type' => 'freeship', 'value' => 0,  'weight' => 14, 'color' => '#14b8a6'],
            ['label' => 'Try Again',     'type' => 'none',     'value' => 0,  'weight' => 16, 'color' => '#64748b'],
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

    /** Has this user already spun? (lifetime — used for display). */
    public static function alreadySpun(User $user): bool
    {
        try {
            return WheelEntry::where('user_id', $user->id)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** How often a user may spin: 'once' | 'daily' | 'unlimited'. */
    public static function spinLimit(): string
    {
        $v = (string) Setting::get('wheel_spin_limit', 'once');
        return in_array($v, ['once', 'daily', 'unlimited'], true) ? $v : 'once';
    }

    /**
     * Can this user spin right now, per the configured limit?
     * Returns null when allowed, or a message explaining why not.
     */
    public static function spinBlockedReason(User $user): ?string
    {
        try {
            $limit = self::spinLimit();
            if ($limit === 'unlimited') {
                return null;
            }
            if ($limit === 'daily') {
                $today = WheelEntry::where('user_id', $user->id)
                    ->whereDate('created_at', now()->toDateString())->exists();
                return $today ? 'You have already spun today. Come back tomorrow for another spin!' : null;
            }
            // once (default) — one spin per account, ever
            return WheelEntry::where('user_id', $user->id)->exists()
                ? 'You have already claimed your spin. One prize per account.' : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Run a spin for an email: pick a prize, create a coupon if they won,
     * record the entry. Returns the result payload for the frontend.
     */
    public static function spin(User $user, ?string $ip = null): array
    {
        $segments = self::segments();
        $idx      = self::pickSegment($segments);
        $seg      = $segments[$idx];
        $won      = ($seg['type'] ?? 'none') !== 'none';

        $coupon   = null;
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
                'email'        => $user->email,
                'user_id'      => $user->id,
            ]);
        }

        WheelEntry::create([
            'email'       => $user->email,
            'user_id'     => $user->id,
            'prize_label' => $seg['label'] ?? '',
            'coupon_id'   => $coupon?->id,
            'ip'          => $ip,
        ]);

        return [
            'index'     => $idx,
            'won'       => $won,
            'label'     => $seg['label'] ?? '',
            'code'      => $code,
            'coupon'    => $coupon,
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
    public static function validateCoupon(string $code, float $subtotalUsd, ?int $userId = null): array
    {
        $code   = trim(strtoupper($code));
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$coupon || !$coupon->active) {
            return ['ok' => false, 'message' => 'This coupon code is not valid.'];
        }
        if ($coupon->user_id !== null) {
            if ($userId === null) {
                return ['ok' => false, 'message' => 'Please log in to use this coupon — it is tied to an account.'];
            }
            if ((int) $coupon->user_id !== (int) $userId) {
                return ['ok' => false, 'message' => 'This coupon belongs to a different account.'];
            }
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
            'discount' => $discount,   // USD, for display only
            'freeship' => $freeship,
            'label'    => self::couponLabel($coupon),
        ];
    }

    /**
     * The discount to take off, computed directly in the store's base currency
     * so percent coupons are exact (no USD-cents round-trip). Fixed coupons and
     * the percent cap are defined in USD and converted with zusd().
     */
    public static function discountBase(array $res, float $subtotalBase): float
    {
        if (!($res['ok'] ?? false) || ($res['freeship'] ?? false)) {
            return 0.0;
        }
        $c = $res['coupon'];
        $d = 0.0;
        if ($c->type === 'percent') {
            $d = $subtotalBase * ((float) $c->value) / 100;
            if ($c->max_discount !== null) {
                $d = min($d, (float) zusd((float) $c->max_discount));
            }
        } elseif ($c->type === 'fixed') {
            $d = (float) zusd((float) $c->value);
        }

        return round(min($d, $subtotalBase), 2);
    }

    public static function couponLabel(Coupon $c): string
    {
        if ($c->type === 'percent')  return rtrim(rtrim(number_format((float) $c->value, 2), '0'), '.') . '% off';
        if ($c->type === 'fixed')    return '$' . number_format((float) $c->value, 2) . ' off';
        if ($c->type === 'freeship') return 'Free shipping';

        return 'Discount';
    }
}
