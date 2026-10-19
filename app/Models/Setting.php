<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Keys stored encrypted at rest. */
    protected const SECRET_KEYS = ['mail_password', 'stripe_secret_key', 'stripe_webhook_secret', 'paypal_secret'];

    public static function defaults(): array
    {
        return [
            'mail_enabled'       => '1',
            'mail_mailer'        => 'smtp',
            'mail_host'          => '',
            'mail_port'          => '587',
            'mail_encryption'    => 'tls',
            'mail_verify_peer'   => '1',
            'mail_username'      => '',
            'mail_password'      => '',
            'mail_from_address'  => '',
            'mail_from_name'     => 'Zelari',
            'mail_admin_address' => '',
            'store_name'         => 'Zelari',
            'store_tagline'      => 'Smart Shopping',
            'store_phone'        => '+92 300 0000000',
            'store_whatsapp'     => '',
            'store_email'        => 'support@zelari.com',
            'store_address'      => 'Gulberg III, Lahore, Pakistan',
            'store_hours'        => 'Mon – Sat, 9 AM – 6 PM PKT',
            'store_facebook'     => '',
            'store_instagram'    => '',
            'store_twitter'      => '',
            'store_pinterest'    => '',
            // Shipping & delivery promises (shown on product pages, cart, checkout, emails, PDF)
            'ship_free_over_usd'     => '30',     // free delivery when order subtotal ≥ this (entered in USD, converted to each currency)
            'ship_fee_usd'           => '0',      // flat delivery fee below the threshold (USD); 0 = always free
            'ship_delivery_days'     => '2–5',    // e.g. "2–5" (business days)
            'ship_pack_hours'        => '24',
            'ship_return_days'       => '7',
            'ship_coverage'          => 'nationwide',
            // Deal-of-the-week countdown banner (home page)
            'deal_enabled'           => '1',
            'deal_eyebrow'           => 'Deal of the week',
            'deal_title'             => 'Special offers',
            'deal_title_accent'      => 'ending soon',
            'deal_text'              => 'Our biggest markdowns of the week — once the timer hits zero, prices go back up. Grab yours before they\'re gone.',
            'deal_badge'             => 'Up to 50% OFF',
            'deal_button'            => 'Shop the deals',
            'deal_ends_at'           => '',       // Y-m-d H:i, blank = end of the current week
            'deal_link'              => '',       // blank = all products on sale
            // Home-page promo banner ("Everything you love, delivered to your door")
            'promo_enabled'          => '1',
            'promo_title'            => 'Everything you love,',
            'promo_title_accent'     => 'delivered to your door',
            'promo_text'             => 'Thousands of products, honest prices and cash on delivery across Pakistan. Pay when it arrives — no risk, no hassle.',
            'promo_btn1_label'       => 'Start shopping',
            'promo_btn1_link'        => '',
            'promo_btn2_label'       => "Today's deals",
            'promo_btn2_link'        => '',
            'promo_perk1_icon'       => 'fa-money-bill-wave', 'promo_perk1_title' => 'Cash on delivery', 'promo_perk1_text' => 'Pay at your doorstep',
            'promo_perk2_icon'       => 'fa-rotate-left',     'promo_perk2_title' => '{returns}-day returns', 'promo_perk2_text' => 'Changed your mind? No problem',
            'promo_perk3_icon'       => 'fa-shield-halved',   'promo_perk3_title' => 'Secure checkout', 'promo_perk3_text' => 'Prices verified on our server',
            'promo_perk4_icon'       => 'fa-headset',         'promo_perk4_title' => '24/7 support', 'promo_perk4_text' => 'Real people, fast replies',
            // Contact page FAQs — one per line: "Question? | Answer"
            'store_faqs'             => '',
            'auth_title'             => 'Shopping that feels simple, honest and fast.',
            'auth_text'              => 'One account for faster checkout, order tracking and invoices — across {store}.',
            'auth_perk1_icon' => 'fa-truck-fast',       'auth_perk1_title' => '{days}-day delivery',     'auth_perk1_text' => '{coverage}',
            'auth_perk2_icon' => 'fa-rotate-left',      'auth_perk2_title' => '{returns}-day returns',   'auth_perk2_text' => 'Changed your mind? No problem',
            'auth_perk3_icon' => 'fa-shield-halved',    'auth_perk3_title' => 'Secure checkout',         'auth_perk3_text' => 'Card details never touch our server',
            'auth_perk4_icon' => 'fa-money-bill-wave',  'auth_perk4_title' => 'Free delivery over {free}','auth_perk4_text' => 'Cash on delivery available',
            // Branding (paths relative to /public, uploaded from Admin → Settings)
            'brand_logo'             => '',
            'brand_icon'             => '',
            'brand_print'            => '',
            // Payments
            'pay_cod_enabled'        => '1',
            'pay_stripe_enabled'     => '0',
            'stripe_publishable_key' => '',
            'stripe_secret_key'      => '',
            'stripe_webhook_secret'  => '',
            'pay_paypal_enabled'     => '0',
            'paypal_mode'            => 'sandbox',
            'paypal_client_id'       => '',
            'paypal_secret'          => '',
        ];
    }

    /** All settings as [key => value], cached. */
    public static function allValues(): array
    {
        return Cache::remember('settings.all', 300, function () {
            if (!Schema::hasTable('settings')) {
                return static::defaults();
            }
            $rows = static::query()->pluck('value', 'key')->all();
            foreach (static::SECRET_KEYS as $k) {
                if (!empty($rows[$k])) {
                    try { $rows[$k] = Crypt::decryptString($rows[$k]); } catch (\Throwable) { $rows[$k] = ''; }
                }
            }
            return array_merge(static::defaults(), $rows);
        });
    }

    public static function get(string $key, $default = null)
    {
        $all = static::allValues();
        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '' ? $all[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        if (in_array($key, static::SECRET_KEYS, true) && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $k => $v) {
            static::set($k, $v);
        }
    }

    /** Remember the outcome of the last send attempt (shown on the settings page). */
    public static function recordMailResult(?string $error): void
    {
        try {
            static::set('mail_last_error', $error ? mb_substr($error, 0, 500) : '');
            static::set('mail_last_at', now()->toDateTimeString());
        } catch (\Throwable) {}
    }

    /** Push the saved SMTP settings into the runtime mail config. Returns true when mail is configured. */
    public static function applyMailConfig(): bool
    {
        $s = static::allValues();
        if (($s['mail_enabled'] ?? '1') !== '1') {
            return false;
        }
        $mailer = $s['mail_mailer'] ?: 'smtp';
        if ($mailer === 'smtp' && empty($s['mail_host'])) {
            return false; // nothing configured yet
        }
        $port = (int) ($s['mail_port'] ?: 587);
        // Implicit TLS ("SSL") only works on 465; every other port uses STARTTLS/plain, which
        // Symfony negotiates automatically. Picking by port avoids a mismatched dropdown breaking mail.
        $scheme = $port === 465 ? 'smtps' : 'smtp';
        config([
            'mail.default'                   => $mailer,
            'mail.mailers.smtp.transport'    => 'smtp',
            'mail.mailers.smtp.scheme'       => $scheme,
            'mail.mailers.smtp.url'          => null,
            'mail.mailers.smtp.host'         => trim($s['mail_host']),
            'mail.mailers.smtp.port'         => $port,
            'mail.mailers.smtp.username'     => $s['mail_username'] !== '' ? trim($s['mail_username']) : null,
            'mail.mailers.smtp.password'     => $s['mail_password'] !== '' ? $s['mail_password'] : null,
            'mail.mailers.smtp.timeout'      => 60,
            'mail.mailers.smtp.verify_peer'  => (($s['mail_verify_peer'] ?? '1') === '1'),
            'mail.mailers.smtp.auto_tls'     => true,
            'mail.from.address'              => $s['mail_from_address'] ?: ($s['mail_username'] ?: 'no-reply@zelari.com'),
            'mail.from.name'                 => $s['mail_from_name'] ?: ($s['store_name'] ?? 'Zelari'),
        ]);
        // Forget any resolved mailer so the new config is used
        app('mail.manager')->forgetMailers();
        return true;
    }
}
