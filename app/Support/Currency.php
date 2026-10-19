<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Geo detection + currency selection for the storefront.
 *
 * Session keys:
 *   geo.country   two-letter country code the visitor is in (detected or chosen)
 *   geo.currency  currency code shown to the visitor
 *   geo.manual    true once the visitor picked a location themselves
 *   geo.ip        last IP lookup result (country/region/city) for order records
 */
class Currency
{
    public static function all(): array
    {
        return config('currencies.currencies', []);
    }

    public static function countries(): array
    {
        return config('currencies.countries', []);
    }

    public static function base(): string
    {
        return config('currencies.base', 'PKR');
    }

    public static function default(): string
    {
        $d = strtoupper((string) config('currencies.default', 'USD'));
        return isset(self::all()[$d]) ? $d : 'USD';
    }

    /** Currency a country uses, or the default. */
    public static function forCountry(?string $cc): string
    {
        $cc = strtoupper((string) $cc);
        $c  = self::countries()[$cc]['currency'] ?? config('currencies.country_currency_extra.' . $cc);
        return ($c && isset(self::all()[$c])) ? $c : self::default();
    }

    /** Full record for the currently active currency. */
    public static function current(): array
    {
        $code = self::currentCode();
        $c = self::all()[$code] ?? self::all()[self::default()] ?? ['symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'decimals' => 2];
        return ['code' => $code] + $c;
    }

    public static function currentCode(): string
    {
        try {
            $code = session('geo.currency');
        } catch (\Throwable) {
            $code = null;
        }
        return ($code && isset(self::all()[$code])) ? $code : self::default();
    }

    public static function currentCountry(): ?string
    {
        try {
            return session('geo.country');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Visitor picked a country from the header dropdown. */
    public static function choose(string $cc): void
    {
        $cc = strtoupper($cc);
        if (!isset(self::countries()[$cc])) return;
        session([
            'geo.country'  => $cc,
            'geo.currency' => self::forCountry($cc),
            'geo.manual'   => true,
        ]);
    }

    /**
     * Called by the middleware: detect on the first request, and again whenever the visitor's
     * IP changes (e.g. localhost → ngrok/VPN) unless they chose a location themselves.
     */
    public static function detect(string $ip): void
    {
        if (session()->has('geo.currency')) {
            if (session('geo.manual')) return;
            if (session('geo.detected_ip') === $ip) return;
            // IP changed: only re-detect when the new IP is public (don't downgrade to the dev default)
            if (self::isPrivate($ip)) return;
        }

        $geo = self::lookup($ip);
        $cc  = $geo['country_code'] ?? null;

        if (!$cc && self::isPrivate($ip)) {
            $cc = strtoupper((string) config('currencies.geoip.dev_country', 'US'));
        }
        if (!$cc) $cc = 'US';

        // Default to the first country that uses the resolved currency so the dropdown shows a selection.
        $currency = self::forCountry($cc);
        if (!isset(self::countries()[$cc])) {
            foreach (self::countries() as $code => $c) {
                if ($c['currency'] === $currency) { $cc = $code; break; }
            }
        }

        session([
            'geo.country'     => $cc,
            'geo.currency'    => $currency,
            'geo.manual'      => false,
            'geo.ip'          => $geo,
            'geo.detected_ip' => $ip,
        ]);
    }

    /** Last lookup for this session (used when saving an order); re-looks up if the IP changed. */
    public static function ipDetails(string $ip): array
    {
        $cached = session('geo.ip');
        if (is_array($cached) && ($cached['ip'] ?? null) === $ip) {
            return $cached;
        }
        $geo = self::lookup($ip);
        session(['geo.ip' => $geo]);
        return $geo;
    }

    /**
     * IP → ['ip','country_code','country','region','city'] (values null when unknown).
     * Results are cached for a day; failures are swallowed so the page never breaks.
     */
    public static function lookup(string $ip): array
    {
        $empty = ['ip' => $ip, 'country_code' => null, 'country' => null, 'region' => null, 'city' => null];

        if (!config('currencies.geoip.enabled', true) || $ip === '' || self::isPrivate($ip)) {
            return $empty;
        }

        return Cache::remember('geoip:' . $ip, now()->addDay(), function () use ($ip, $empty) {
            $timeout = (int) config('currencies.geoip.timeout', 3);

            // Provider 1: ipwho.is
            try {
                $r = Http::timeout($timeout)->acceptJson()->get("https://ipwho.is/{$ip}");
                if ($r->ok() && ($r->json('success') ?? false)) {
                    return [
                        'ip'           => $ip,
                        'country_code' => strtoupper((string) $r->json('country_code')) ?: null,
                        'country'      => $r->json('country'),
                        'region'       => $r->json('region'),
                        'city'         => $r->json('city'),
                    ];
                }
            } catch (\Throwable) {}

            // Provider 2: geojs.io
            try {
                $r = Http::timeout($timeout)->acceptJson()->get("https://get.geojs.io/v1/ip/geo/{$ip}.json");
                if ($r->ok() && $r->json('country_code')) {
                    return [
                        'ip'           => $ip,
                        'country_code' => strtoupper((string) $r->json('country_code')),
                        'country'      => $r->json('country'),
                        'region'       => $r->json('region'),
                        'city'         => $r->json('city'),
                    ];
                }
            } catch (\Throwable) {}

            return $empty;
        });
    }

    public static function isPrivate(string $ip): bool
    {
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') return true;
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /** Convert a base-currency amount to the given currency record and format it. */
    public static function format($amount, array $cur): string
    {
        $value    = (float) $amount * (float) ($cur['rate'] ?? 1);
        $decimals = (int) ($cur['decimals'] ?? 2);
        $symbol   = (string) ($cur['symbol'] ?? '');
        $num      = number_format($value, $decimals);
        // Short symbols hug the number ($12.50); word-like codes get a space (AED 45.00)
        return (Str::length($symbol) <= 2 && !ctype_alpha($symbol)) ? $symbol . $num : $symbol . ' ' . $num;
    }
}
