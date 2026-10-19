<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Minimal PayPal Orders v2 client (no SDK). Credentials live encrypted in the
 * settings table and are only used server-side; the browser only gets the
 * client ID, which PayPal designs to be public.
 */
class PayPal
{
    /** Currencies PayPal can settle. Anything else is charged in USD at our rate. */
    public const SUPPORTED = ['AUD','BRL','CAD','CNY','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','SGD','SEK','CHF','THB','USD'];
    protected const ZERO_DECIMAL = ['HUF','JPY','TWD'];

    public static function enabled(): bool
    {
        return Setting::get('pay_paypal_enabled', '0') === '1'
            && self::clientId() !== ''
            && trim((string) Setting::get('paypal_secret', '')) !== '';
    }

    public static function clientId(): string
    {
        return trim((string) Setting::get('paypal_client_id', ''));
    }

    public static function mode(): string
    {
        return Setting::get('paypal_mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
    }

    public static function baseUrl(): string
    {
        return self::mode() === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    // ── Amounts ─────────────────────────────────────────────────────────

    /**
     * Decide what to charge: the order's own currency when PayPal supports it,
     * otherwise USD converted at our configured rate. Returns [amountString, currency].
     */
    public static function chargeAmount(Order $order): array
    {
        $cur = strtoupper((string) $order->currency_code);
        if (in_array($cur, self::SUPPORTED, true)) {
            $value = (float) $order->total * (float) ($order->exchange_rate ?: 1);
        } else {
            $cur   = 'USD';
            $usd   = Currency::all()['USD']['rate'] ?? 1;
            $value = (float) $order->total * (float) $usd;
        }
        $amount = in_array($cur, self::ZERO_DECIMAL, true) ? number_format(round($value), 0, '.', '') : number_format($value, 2, '.', '');
        return [$amount, $cur];
    }

    // ── REST ────────────────────────────────────────────────────────────

    /** @throws \RuntimeException */
    public static function token(bool $fresh = false): string
    {
        $id  = self::clientId();
        $sec = trim((string) Setting::get('paypal_secret', ''));
        if ($id === '' || $sec === '') {
            throw new \RuntimeException('PayPal is not configured.');
        }
        $key = 'paypal:token:' . md5($id . self::mode());
        if ($fresh) Cache::forget($key);

        return Cache::remember($key, now()->addMinutes(50), function () use ($id, $sec) {
            try {
                $r = Http::withBasicAuth($id, $sec)->asForm()->timeout(20)
                    ->post(self::baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);
            } catch (\Throwable $e) {
                report($e);
                throw new \RuntimeException('Could not reach PayPal. Please try again.');
            }
            if (!$r->ok() || !$r->json('access_token')) {
                $msg = $r->json('error_description') ?? $r->json('error') ?? ('HTTP ' . $r->status());
                throw new \RuntimeException('PayPal rejected the credentials: ' . $msg);
            }
            return (string) $r->json('access_token');
        });
    }

    /** @throws \RuntimeException */
    public static function api(string $method, string $path, array $body = [], array $headers = []): array
    {
        $attempt = function (bool $fresh) use ($method, $path, $body, $headers) {
            $req = Http::withToken(self::token($fresh))->acceptJson()->timeout(25)->withHeaders($headers);
            return strtoupper($method) === 'GET'
                ? $req->get(self::baseUrl() . $path)
                : $req->post(self::baseUrl() . $path, $body ?: null);
        };
        try {
            $r = $attempt(false);
            if ($r->status() === 401) $r = $attempt(true); // expired cached token
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new \RuntimeException('Could not reach PayPal. Please try again.');
        }
        $json = $r->json() ?? [];
        if ($r->failed()) {
            $msg = $json['details'][0]['description'] ?? $json['message'] ?? $json['error_description'] ?? ('PayPal error ' . $r->status());
            report(new \RuntimeException("PayPal $method $path: $msg"));
            throw new \RuntimeException($msg);
        }
        return $json;
    }

    /** Create a PayPal order for ours; returns PayPal's order id. */
    public static function createOrder(Order $order): string
    {
        [$amount, $currency] = self::chargeAmount($order);
        $store = Setting::get('store_name', 'Zelari');
        $res = self::api('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->order_number,
                'custom_id'    => (string) $order->id,
                'invoice_id'   => $order->order_number,
                'description'  => $store . ' order ' . $order->order_number,
                'amount'       => ['currency_code' => $currency, 'value' => $amount],
            ]],
            'payment_source' => ['paypal' => [
                'experience_context' => [
                    'brand_name'          => $store,
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action'         => 'PAY_NOW',
                    'return_url'          => route('checkout.paypal.return', $order->order_number),
                    'cancel_url'          => route('checkout.paypal.cancel', $order->order_number),
                ],
            ]],
        ], ['PayPal-Request-Id' => 'create-' . $order->order_number . '-' . $order->id]);

        return (string) ($res['id'] ?? '');
    }

    public static function captureOrder(string $paypalOrderId): array
    {
        return self::api('POST', '/v2/checkout/orders/' . $paypalOrderId . '/capture', [], ['PayPal-Request-Id' => 'capture-' . $paypalOrderId]);
    }

    public static function getOrder(string $paypalOrderId): array
    {
        return self::api('GET', '/v2/checkout/orders/' . $paypalOrderId);
    }

    /** Pull the useful bits out of a captured PayPal order. */
    public static function summarize(array $po): array
    {
        $pu  = $po['purchase_units'][0] ?? [];
        $cap = $pu['payments']['captures'][0] ?? [];
        $payer = $po['payer'] ?? [];
        $name  = trim(($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? ''));
        return [
            'status'     => $cap['status'] ?? ($po['status'] ?? 'UNKNOWN'),
            'capture_id' => $cap['id'] ?? null,
            'amount'     => $cap['amount']['value'] ?? ($pu['amount']['value'] ?? null),
            'currency'   => $cap['amount']['currency_code'] ?? ($pu['amount']['currency_code'] ?? null),
            'payer_email'=> $payer['email_address'] ?? null,
            'payer_name' => $name !== '' ? $name : null,
            'payer_id'   => $payer['payer_id'] ?? null,
            'country'    => $payer['address']['country_code'] ?? null,
            'reason'     => $cap['status_details']['reason'] ?? null,
        ];
    }
}
