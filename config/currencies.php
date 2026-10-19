<?php

/*
|--------------------------------------------------------------------------
| Currencies & locations
|--------------------------------------------------------------------------
| Product prices in the database are stored in the BASE currency (PKR).
| `rate` = how many units of that currency 1 unit of the base currency buys
| (display price = stored price × rate). Rates are approximate and can be
| overridden from .env (e.g. CURRENCY_RATE_USD=0.0036).
|
| `default` is what a visitor sees before (or if) IP detection fails.
*/

return [

    'base'    => 'PKR',
    'default' => env('CURRENCY_DEFAULT', 'USD'),

    'currencies' => [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'rate' => (float) env('CURRENCY_RATE_USD', 0.003571), 'decimals' => 2],
        'PKR' => ['symbol' => 'Rs', 'name' => 'Pakistani Rupee', 'rate' => 1.0, 'decimals' => 0],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'rate' => (float) env('CURRENCY_RATE_GBP', 0.002817), 'decimals' => 2],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'rate' => (float) env('CURRENCY_RATE_EUR', 0.003333), 'decimals' => 2],
        'AED' => ['symbol' => 'AED', 'name' => 'UAE Dirham', 'rate' => (float) env('CURRENCY_RATE_AED', 0.013123), 'decimals' => 2],
        'SAR' => ['symbol' => 'SAR', 'name' => 'Saudi Riyal', 'rate' => (float) env('CURRENCY_RATE_SAR', 0.013405), 'decimals' => 2],
        'QAR' => ['symbol' => 'QAR', 'name' => 'Qatari Riyal', 'rate' => (float) env('CURRENCY_RATE_QAR', 0.013004), 'decimals' => 2],
        'KWD' => ['symbol' => 'KWD', 'name' => 'Kuwaiti Dinar', 'rate' => (float) env('CURRENCY_RATE_KWD', 0.001099), 'decimals' => 3],
        'BHD' => ['symbol' => 'BHD', 'name' => 'Bahraini Dinar', 'rate' => (float) env('CURRENCY_RATE_BHD', 0.001346), 'decimals' => 3],
        'OMR' => ['symbol' => 'OMR', 'name' => 'Omani Rial', 'rate' => (float) env('CURRENCY_RATE_OMR', 0.001376), 'decimals' => 3],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar', 'rate' => (float) env('CURRENCY_RATE_CAD', 0.004878), 'decimals' => 2],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar', 'rate' => (float) env('CURRENCY_RATE_AUD', 0.005405), 'decimals' => 2],
        'NZD' => ['symbol' => 'NZ$', 'name' => 'New Zealand Dollar', 'rate' => (float) env('CURRENCY_RATE_NZD', 0.005952), 'decimals' => 2],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'rate' => (float) env('CURRENCY_RATE_INR', 0.298507), 'decimals' => 0],
        'BDT' => ['symbol' => '৳', 'name' => 'Bangladeshi Taka', 'rate' => (float) env('CURRENCY_RATE_BDT', 0.425532), 'decimals' => 0],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen', 'rate' => (float) env('CURRENCY_RATE_JPY', 0.540541), 'decimals' => 0],
        'CNY' => ['symbol' => 'CN¥', 'name' => 'Chinese Yuan', 'rate' => (float) env('CURRENCY_RATE_CNY', 0.025974), 'decimals' => 2],
        'SGD' => ['symbol' => 'S$', 'name' => 'Singapore Dollar', 'rate' => (float) env('CURRENCY_RATE_SGD', 0.004762), 'decimals' => 2],
        'MYR' => ['symbol' => 'RM', 'name' => 'Malaysian Ringgit', 'rate' => (float) env('CURRENCY_RATE_MYR', 0.015873), 'decimals' => 2],
        'TRY' => ['symbol' => '₺', 'name' => 'Turkish Lira', 'rate' => (float) env('CURRENCY_RATE_TRY', 0.121951), 'decimals' => 2],
        'EGP' => ['symbol' => 'E£', 'name' => 'Egyptian Pound', 'rate' => (float) env('CURRENCY_RATE_EGP', 0.175439), 'decimals' => 2],
        'ZAR' => ['symbol' => 'R', 'name' => 'South African Rand', 'rate' => (float) env('CURRENCY_RATE_ZAR', 0.064516), 'decimals' => 2],
        'NGN' => ['symbol' => '₦', 'name' => 'Nigerian Naira', 'rate' => (float) env('CURRENCY_RATE_NGN', 5.555556), 'decimals' => 0],
        'CHF' => ['symbol' => 'CHF', 'name' => 'Swiss Franc', 'rate' => (float) env('CURRENCY_RATE_CHF', 0.003125), 'decimals' => 2],
        'SEK' => ['symbol' => 'kr', 'name' => 'Swedish Krona', 'rate' => (float) env('CURRENCY_RATE_SEK', 0.037037), 'decimals' => 2],
        'NOK' => ['symbol' => 'kr', 'name' => 'Norwegian Krone', 'rate' => (float) env('CURRENCY_RATE_NOK', 0.037736), 'decimals' => 2],
        'DKK' => ['symbol' => 'kr', 'name' => 'Danish Krone', 'rate' => (float) env('CURRENCY_RATE_DKK', 0.025), 'decimals' => 2],
        'PLN' => ['symbol' => 'zł', 'name' => 'Polish Zloty', 'rate' => (float) env('CURRENCY_RATE_PLN', 0.014286), 'decimals' => 2],
        'HKD' => ['symbol' => 'HK$', 'name' => 'Hong Kong Dollar', 'rate' => (float) env('CURRENCY_RATE_HKD', 0.027778), 'decimals' => 2],
        'IDR' => ['symbol' => 'Rp', 'name' => 'Indonesian Rupiah', 'rate' => (float) env('CURRENCY_RATE_IDR', 58.139535), 'decimals' => 0],
        'THB' => ['symbol' => '฿', 'name' => 'Thai Baht', 'rate' => (float) env('CURRENCY_RATE_THB', 0.123457), 'decimals' => 2],
        'PHP' => ['symbol' => '₱', 'name' => 'Philippine Peso', 'rate' => (float) env('CURRENCY_RATE_PHP', 0.204082), 'decimals' => 2],
    ],

    /*
    | Countries shown in the header Location dropdown (in this order) and the currency
    | each one uses. A visitor from any other country gets `default` (USD) and the
    | dropdown shows the first country that uses that currency.
    */
    'countries' => [
        'US' => ['name' => 'United States', 'flag' => '🇺🇸', 'currency' => 'USD'],
        'PK' => ['name' => 'Pakistan', 'flag' => '🇵🇰', 'currency' => 'PKR'],
        'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧', 'currency' => 'GBP'],
        'CA' => ['name' => 'Canada', 'flag' => '🇨🇦', 'currency' => 'CAD'],
        'AU' => ['name' => 'Australia', 'flag' => '🇦🇺', 'currency' => 'AUD'],
        'NZ' => ['name' => 'New Zealand', 'flag' => '🇳🇿', 'currency' => 'NZD'],
        'AE' => ['name' => 'United Arab Emirates', 'flag' => '🇦🇪', 'currency' => 'AED'],
        'SA' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦', 'currency' => 'SAR'],
        'QA' => ['name' => 'Qatar', 'flag' => '🇶🇦', 'currency' => 'QAR'],
        'KW' => ['name' => 'Kuwait', 'flag' => '🇰🇼', 'currency' => 'KWD'],
        'BH' => ['name' => 'Bahrain', 'flag' => '🇧🇭', 'currency' => 'BHD'],
        'OM' => ['name' => 'Oman', 'flag' => '🇴🇲', 'currency' => 'OMR'],
        'IN' => ['name' => 'India', 'flag' => '🇮🇳', 'currency' => 'INR'],
        'BD' => ['name' => 'Bangladesh', 'flag' => '🇧🇩', 'currency' => 'BDT'],
        'CN' => ['name' => 'China', 'flag' => '🇨🇳', 'currency' => 'CNY'],
        'JP' => ['name' => 'Japan', 'flag' => '🇯🇵', 'currency' => 'JPY'],
        'HK' => ['name' => 'Hong Kong', 'flag' => '🇭🇰', 'currency' => 'HKD'],
        'SG' => ['name' => 'Singapore', 'flag' => '🇸🇬', 'currency' => 'SGD'],
        'MY' => ['name' => 'Malaysia', 'flag' => '🇲🇾', 'currency' => 'MYR'],
        'ID' => ['name' => 'Indonesia', 'flag' => '🇮🇩', 'currency' => 'IDR'],
        'TH' => ['name' => 'Thailand', 'flag' => '🇹🇭', 'currency' => 'THB'],
        'PH' => ['name' => 'Philippines', 'flag' => '🇵🇭', 'currency' => 'PHP'],
        'TR' => ['name' => 'Türkiye', 'flag' => '🇹🇷', 'currency' => 'TRY'],
        'EG' => ['name' => 'Egypt', 'flag' => '🇪🇬', 'currency' => 'EGP'],
        'ZA' => ['name' => 'South Africa', 'flag' => '🇿🇦', 'currency' => 'ZAR'],
        'NG' => ['name' => 'Nigeria', 'flag' => '🇳🇬', 'currency' => 'NGN'],
        'DE' => ['name' => 'Germany', 'flag' => '🇩🇪', 'currency' => 'EUR'],
        'FR' => ['name' => 'France', 'flag' => '🇫🇷', 'currency' => 'EUR'],
        'IT' => ['name' => 'Italy', 'flag' => '🇮🇹', 'currency' => 'EUR'],
        'ES' => ['name' => 'Spain', 'flag' => '🇪🇸', 'currency' => 'EUR'],
        'NL' => ['name' => 'Netherlands', 'flag' => '🇳🇱', 'currency' => 'EUR'],
        'BE' => ['name' => 'Belgium', 'flag' => '🇧🇪', 'currency' => 'EUR'],
        'IE' => ['name' => 'Ireland', 'flag' => '🇮🇪', 'currency' => 'EUR'],
        'AT' => ['name' => 'Austria', 'flag' => '🇦🇹', 'currency' => 'EUR'],
        'PT' => ['name' => 'Portugal', 'flag' => '🇵🇹', 'currency' => 'EUR'],
        'FI' => ['name' => 'Finland', 'flag' => '🇫🇮', 'currency' => 'EUR'],
        'GR' => ['name' => 'Greece', 'flag' => '🇬🇷', 'currency' => 'EUR'],
        'CH' => ['name' => 'Switzerland', 'flag' => '🇨🇭', 'currency' => 'CHF'],
        'SE' => ['name' => 'Sweden', 'flag' => '🇸🇪', 'currency' => 'SEK'],
        'NO' => ['name' => 'Norway', 'flag' => '🇳🇴', 'currency' => 'NOK'],
        'DK' => ['name' => 'Denmark', 'flag' => '🇩🇰', 'currency' => 'DKK'],
        'PL' => ['name' => 'Poland', 'flag' => '🇵🇱', 'currency' => 'PLN'],
    ],

    /* Detected countries not in the list above but that use a listed currency. */
    'country_currency_extra' => [
        'LU' => 'EUR', 'SK' => 'EUR', 'SI' => 'EUR', 'EE' => 'EUR', 'LV' => 'EUR', 'LT' => 'EUR', 'MT' => 'EUR', 'CY' => 'EUR', 'HR' => 'EUR',
        'MO' => 'HKD', 'PR' => 'USD', 'EC' => 'USD', 'SV' => 'USD', 'PA' => 'USD',
    ],

    /* IP geolocation (free, no key). Set GEOIP_ENABLED=false to skip lookups. */
    'geoip' => [
        'enabled'  => filter_var(env('GEOIP_ENABLED', true), FILTER_VALIDATE_BOOL),
        'timeout'  => 3,
        /* Country code to assume for localhost / private IPs while developing. */
        'dev_country' => env('GEOIP_DEV_COUNTRY', 'US'),
    ],
];
