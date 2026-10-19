<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Payment methods available at checkout + a tiny Stripe REST client.
 *
 * Stripe is called directly over HTTPS (no SDK needed). The secret key and
 * webhook secret live encrypted in the settings table and are only ever used
 * server-side; the browser receives the publishable key and a client_secret.
 */
class Payments
{
    public const COD    = 'cash_on_delivery';
    public const STRIPE = 'card';
    public const PAYPAL = 'paypal';

    /** Methods the admin has switched on, in display order. */
    public static function enabledMethods(): array
    {
        $m = [];
        if (Setting::get('pay_cod_enabled', '1') === '1') {
            $m[self::COD] = [
                'label' => 'Cash on Delivery',
                'desc'  => 'Pay in cash when your order arrives at your door.',
                'icon'  => 'fa-money-bill-wave',
                'tag'   => 'Popular',
            ];
        }
        if (self::stripeReady()) {
            $m[self::STRIPE] = [
                'label' => 'Credit / Debit Card',
                'desc'  => 'Visa, Mastercard, Amex and more. Secured by Stripe.',
                'icon'  => 'fa-credit-card',
                'tag'   => 'Instant',
            ];
        }
        if (PayPal::enabled()) {
            $m[self::PAYPAL] = [
                'label' => 'PayPal',
                'desc'  => 'Pay with your PayPal balance, linked bank or card — no account needed for guest checkout.',
                'icon'  => 'fa-brands fa-paypal',
                'tag'   => null,
            ];
        }
        return $m;
    }

    public static function isEnabled(string $method): bool
    {
        return array_key_exists($method, self::enabledMethods());
    }

    /** Stripe is on AND both keys are present. */
    public static function stripeReady(): bool
    {
        return Setting::get('pay_stripe_enabled', '0') === '1'
            && self::stripePublishableKey() !== ''
            && self::stripeSecretKey() !== '';
    }

    public static function stripePublishableKey(): string
    {
        return trim((string) Setting::get('stripe_publishable_key', ''));
    }

    protected static function stripeSecretKey(): string
    {
        return trim((string) Setting::get('stripe_secret_key', ''));
    }

    public static function stripeTestMode(): bool
    {
        return str_starts_with(self::stripePublishableKey(), 'pk_test_');
    }

    // ── Amounts ─────────────────────────────────────────────────────────

    /** Currencies Stripe charges in whole units (no cents). */
    protected const ZERO_DECIMAL = ['bif','clp','djf','gnf','jpy','kmf','krw','mga','pyg','rwf','ugx','vnd','vuv','xaf','xof','xpf'];

    /** Currencies Stripe charges in thousandths (fils/baisa). */
    protected const THREE_DECIMAL = ['bhd','jod','kwd','omr','tnd'];

    /** Convert a base-currency amount to Stripe minor units in the given currency. */
    public static function minorUnits(float $baseAmount, string $currency, float $rate): int
    {
        $value = $baseAmount * $rate;
        $cur   = strtolower($currency);
        if (in_array($cur, self::ZERO_DECIMAL, true))  return (int) round($value);
        if (in_array($cur, self::THREE_DECIMAL, true)) return (int) round($value * 1000);
        return (int) round($value * 100);
    }

    // ── Stripe REST ─────────────────────────────────────────────────────

    /**
     * @throws \RuntimeException with a customer-safe message on failure
     */
    public static function stripe(string $method, string $path, array $params = []): array
    {
        $key = self::stripeSecretKey();
        if ($key === '') {
            throw new \RuntimeException('Card payments are not configured.');
        }
        try {
            $req = Http::withToken($key)->timeout(20)->acceptJson()->asForm()
                ->withHeaders(['Stripe-Version' => '2024-06-20']);
            $res = strtoupper($method) === 'GET'
                ? $req->get('https://api.stripe.com' . $path, $params)
                : $req->post('https://api.stripe.com' . $path, $params);
        } catch (\Throwable $e) {
            report($e);
            throw new \RuntimeException('Could not reach the payment provider. Please try again.');
        }
        $json = $res->json() ?? [];
        if (!$res->ok()) {
            $msg = $json['error']['message'] ?? ('Payment provider error (' . $res->status() . ').');
            report(new \RuntimeException('Stripe ' . $method . ' ' . $path . ': ' . $msg));
            // Key problems are an admin issue; don't leak details to shoppers
            if (($json['error']['type'] ?? '') === 'invalid_request_error' && str_contains($msg, 'API key')) {
                $msg = 'Card payments are temporarily unavailable.';
            }
            throw new \RuntimeException($msg);
        }
        return $json;
    }

    public static function createIntent(int $amount, string $currency, array $metadata = [], ?string $receiptEmail = null): array
    {
        $params = [
            'amount'   => $amount,
            'currency' => strtolower($currency),
            'automatic_payment_methods[enabled]' => 'true',
            'description' => (Setting::get('store_name', 'Zelari')) . ' order',
        ];
        foreach ($metadata as $k => $v) $params["metadata[$k]"] = (string) $v;
        if ($receiptEmail) $params['receipt_email'] = $receiptEmail;
        return self::stripe('POST', '/v1/payment_intents', $params);
    }

    public static function updateIntent(string $id, array $params): array
    {
        return self::stripe('POST', '/v1/payment_intents/' . $id, $params);
    }

    public static function retrieveIntent(string $id, bool $withCharge = false): array
    {
        return self::stripe('GET', '/v1/payment_intents/' . $id, $withCharge ? ['expand' => ['latest_charge']] : []);
    }

    public static function cancelIntent(string $id): void
    {
        try { self::stripe('POST', '/v1/payment_intents/' . $id . '/cancel'); } catch (\Throwable) {}
    }

    /** Verify a Stripe-Signature header (HMAC-SHA256 over "t.payload"). */
    public static function verifyWebhook(string $payload, ?string $sigHeader, int $tolerance = 300): bool
    {
        $secret = trim((string) Setting::get('stripe_webhook_secret', ''));
        if ($secret === '' || !$sigHeader) return false;

        $t = null; $sigs = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($k === 't') $t = $v;
            if ($k === 'v1' && $v) $sigs[] = $v;
        }
        if (!$t || !$sigs || abs(time() - (int) $t) > $tolerance) return false;

        $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
        foreach ($sigs as $s) {
            if (hash_equals($expected, $s)) return true;
        }
        return false;
    }

    // ── Order state ─────────────────────────────────────────────────────

    /** Create the payment record for a freshly placed order. */
    public static function openAttempt(Order $order): Payment
    {
        return Payment::create([
            'order_id'          => $order->id,
            'provider'          => match ($order->payment_method) { self::STRIPE => 'stripe', self::PAYPAL => 'paypal', default => 'cod' },
            'status'            => 'pending',
            'payment_intent_id' => $order->payment_intent_id,
            'amount'            => round((float) $order->total * (float) ($order->exchange_rate ?: 1), 2),
            'currency'          => $order->currency_code ?: 'USD',
            'billing_email'     => $order->customer_email,
            'ip_address'        => $order->ip_address,
        ]);
    }

    /** The order's payment row (creating one for legacy orders). */
    public static function attemptFor(Order $order): Payment
    {
        return Payment::where('order_id', $order->id)->latest()->first() ?: self::openAttempt($order);
    }

    /** Mark an order paid (idempotent). Returns true when this call made the change. */
    public static function markPaid(Order $order, ?string $intentId = null): bool
    {
        if ($order->payment_status === 'paid') return false;
        $order->forceFill([
            'payment_status'    => 'paid',
            'paid_at'           => now(),
            'payment_intent_id' => $intentId ?: $order->payment_intent_id,
            'status'            => $order->status === 'pending' ? 'processing' : $order->status,
        ])->save();

        $pay = self::attemptFor($order);
        $pay->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
        if ($order->payment_method === self::STRIPE && $order->payment_intent_id) {
            self::syncCardDetails($pay, $order->payment_intent_id);
        }
        return true;
    }

    /** Store what PayPal told us about a capture on the ledger row. */
    public static function fillFromPayPal(Payment $pay, array $summary): void
    {
        $pay->forceFill(array_filter([
            'charge_id'       => $summary['capture_id'] ?? null,
            'amount'          => isset($summary['amount']) ? (float) $summary['amount'] : null,
            'currency'        => $summary['currency'] ?? null,
            'billing_email'   => $summary['payer_email'] ?? null,
            'cardholder_name' => $summary['payer_name'] ?? null,
            'card_country'    => $summary['country'] ?? null,
            'card_wallet'     => 'paypal',
            'failure_message' => $summary['reason'] ?? null,
        ], fn ($v) => $v !== null))->save();
    }

    /** True for any online method whose order is only real once paid. */
    public static function isOnline(?string $method): bool
    {
        return in_array($method, [self::STRIPE, self::PAYPAL], true);
    }

    /** Record a failed / abandoned attempt with Stripe's reason. */
    public static function markFailed(Order $order, string $status, ?string $message = null, ?array $pi = null): void
    {
        $pay = self::attemptFor($order);
        if ($pay->status === 'paid') return;
        $pay->forceFill(['status' => $status, 'failure_message' => $message ? mb_substr($message, 0, 500) : $pay->failure_message])->save();
        if ($pi) self::fillFromIntent($pay, $pi);
    }

    /**
     * Pull what Stripe knows about the card (brand, last 4, expiry, funding, country, wallet,
     * cardholder, risk) onto the payment row. Stripe never exposes the full card number.
     */
    public static function syncCardDetails(Payment $pay, string $intentId): void
    {
        try {
            $pi = self::retrieveIntent($intentId, true);
        } catch (\Throwable $e) {
            report($e);
            return;
        }
        self::fillFromIntent($pay, $pi);
    }

    public static function fillFromIntent(Payment $pay, array $pi): void
    {
        $charge = is_array($pi['latest_charge'] ?? null) ? $pi['latest_charge'] : null;
        $card   = $charge['payment_method_details']['card'] ?? null;
        $data   = [
            'payment_intent_id' => $pi['id'] ?? $pay->payment_intent_id,
            'charge_id'         => $charge['id'] ?? $pay->charge_id,
        ];
        if (isset($pi['amount_received']) && $pi['amount_received'] > 0) {
            $cur = strtolower((string) ($pi['currency'] ?? $pay->currency));
            $div = in_array($cur, self::ZERO_DECIMAL, true) ? 1 : (in_array($cur, self::THREE_DECIMAL, true) ? 1000 : 100);
            $data['amount']   = $pi['amount_received'] / $div;
            $data['currency'] = strtoupper($cur);
        }
        if ($card) {
            $data += [
                'card_brand'     => $card['brand'] ?? null,
                'card_last4'     => $card['last4'] ?? null,
                'card_fingerprint' => $card['fingerprint'] ?? null,
                'card_exp_month' => $card['exp_month'] ?? null,
                'card_exp_year'  => $card['exp_year'] ?? null,
                'card_funding'   => $card['funding'] ?? null,
                'card_country'   => $card['country'] ?? null,
                'card_wallet'    => $card['wallet']['type'] ?? null,
            ];
        }
        if ($charge) {
            $data['cardholder_name'] = $charge['billing_details']['name'] ?? $pay->cardholder_name;
            $data['billing_email']   = $charge['billing_details']['email'] ?? $pay->billing_email;
            $data['receipt_url']     = $charge['receipt_url'] ?? $pay->receipt_url;
            $data['risk_level']      = $charge['outcome']['risk_level'] ?? $pay->risk_level;
            $data['risk_score']      = $charge['outcome']['risk_score'] ?? $pay->risk_score;
            if (!empty($charge['failure_message'])) $data['failure_message'] = mb_substr($charge['failure_message'], 0, 500);
            if (!empty($charge['refunded'])) $data['status'] = 'refunded';
        }
        if (!empty($pi['last_payment_error']['message']) && $pay->status !== 'paid') {
            $data['failure_message'] = mb_substr($pi['last_payment_error']['message'], 0, 500);
            $pmCard = $pi['last_payment_error']['payment_method']['card'] ?? null;
            if ($pmCard && empty($data['card_last4'])) {
                $data += ['card_brand' => $pmCard['brand'] ?? null, 'card_last4' => $pmCard['last4'] ?? null, 'card_fingerprint' => $pmCard['fingerprint'] ?? null, 'card_exp_month' => $pmCard['exp_month'] ?? null, 'card_exp_year' => $pmCard['exp_year'] ?? null, 'card_funding' => $pmCard['funding'] ?? null, 'card_country' => $pmCard['country'] ?? null];
            }
        }
        $pay->forceFill(array_filter($data, fn ($v) => $v !== null))->save();
    }

    /** Cancel an unpaid order and put its stock back. */
    public static function cancelAndRestock(Order $order): void
    {
        if ($order->status === 'cancelled') return;
        $order->loadMissing('items');
        foreach ($order->items as $it) {
            if ($it->product_id) {
                \App\Models\Product::whereKey($it->product_id)->increment('quantity', (int) $it->quantity);
            }
        }
        $order->forceFill(['status' => 'cancelled'])->save();
        $pay = Payment::where('order_id', $order->id)->latest()->first();
        if ($pay && $pay->status === 'pending') {
            $pay->forceFill(['status' => 'cancelled'])->save();
        }
        if ($order->payment_method === self::STRIPE && $order->payment_intent_id) {
            self::cancelIntent($order->payment_intent_id);
        }
    }
}
