<?php

if (!function_exists('zcat_icon')) {
    /**
     * Pick a Font Awesome icon for a category based on its name.
     */
    function zcat_icon(?string $name): string
    {
        $n = strtolower((string) $name);
        $map = [
            'phone|mobile|smartphone'           => 'fa-mobile-screen-button',
            'laptop|computer|pc|notebook'       => 'fa-laptop',
            'electronic|gadget|tech|device'     => 'fa-microchip',
            'headphone|audio|speaker|earbud'    => 'fa-headphones',
            'camera|photo'                      => 'fa-camera',
            'watch|smartwatch'                  => 'fa-clock',
            'game|gaming|console'               => 'fa-gamepad',
            'tv|television|monitor|screen'      => 'fa-tv',
            'fashion|cloth|apparel|wear|dress'  => 'fa-shirt',
            'men'                               => 'fa-person',
            'women|ladies|girl'                 => 'fa-person-dress',
            'kid|child|baby|toy'                => 'fa-baby-carriage',
            'shoe|footwear|sneaker|sandal'      => 'fa-shoe-prints',
            'bag|handbag|backpack|luggage'      => 'fa-bag-shopping',
            'jewel|ring|necklace|accessor'      => 'fa-gem',
            'beauty|cosmetic|makeup|skin|care'  => 'fa-spa',
            'perfume|fragrance'                 => 'fa-spray-can-sparkles',
            'health|medic|pharma|vitamin'       => 'fa-heart-pulse',
            'sport|fitness|gym|outdoor'         => 'fa-dumbbell',
            'home|decor|furniture|living'       => 'fa-couch',
            'kitchen|cook|appliance'            => 'fa-blender',
            'grocer|food|snack|fruit|vegetable' => 'fa-apple-whole',
            'beverage|drink|coffee|tea|juice'   => 'fa-mug-hot',
            'meat|seafood|fish'                 => 'fa-drumstick-bite',
            'bakery|bread|breakfast|dairy'      => 'fa-bread-slice',
            'frozen|ice'                        => 'fa-snowflake',
            'pet|dog|cat'                       => 'fa-paw',
            'book|stationery|office'            => 'fa-book-open',
            'auto|car|bike|motor'               => 'fa-car',
            'garden|plant|tool'                 => 'fa-seedling',
            'gift'                              => 'fa-gift',
        ];
        foreach ($map as $keys => $icon) {
            foreach (explode('|', $keys) as $k) {
                if ($k !== '' && str_contains($n, $k)) {
                    return $icon;
                }
            }
        }
        return 'fa-tag';
    }
}

if (!function_exists('zcat_image')) {
    /**
     * A stock photo URL (Unsplash CDN) matching a category name. $seed varies the pick.
     */
    function zcat_image(?string $name, int $seed = 0, int $w = 800): string
    {
        $n = strtolower((string) $name);
        $sets = [
            'phone|mobile|smartphone'           => ['1511707171634-5f897ff02aa9','1592899677977-9c10ca588bbd','1601784551446-20c9e07cdbdb'],
            'laptop|computer|pc|notebook'       => ['1496181133206-80ce9b88a853','1498049794561-7780e7231661','1517336714731-489689fd1ca8'],
            'electronic|gadget|tech|device'     => ['1498049794561-7780e7231661','1550009158-9ebf69173e03','1519389950473-47ba0277781c'],
            'headphone|audio|speaker|earbud'    => ['1505740420928-5e560c06d30e','1583394838336-acd977736f90','1484704849700-f032a568e944'],
            'camera|photo'                      => ['1526170375885-4d8ecf77b99f','1516035069371-29a1b244cc32','1502920917128-1aa500764cbd'],
            'watch|smartwatch'                  => ['1523275335684-37898b6baf30','1546868871-7041f2a55e12','1524592094714-0f0654e20314'],
            'game|gaming|console'               => ['1606144042614-b2417e99c4e3','1493711662062-fa541adb3fc8','1511512578047-dfb367046420'],
            'tv|television|monitor|screen'      => ['1593359677879-a4bb92f829d1','1527443224154-c4a3942d3acf'],
            'fashion|cloth|apparel|wear|dress'  => ['1441986300917-64674bd600d8','1445205170230-053b83016050','1523381210434-271e8be1f52b','1434389677669-e08b4cac3105'],
            'men'                               => ['1617137968427-85924c800a22','1490578474895-699cd4e2cf59'],
            'women|ladies|girl'                 => ['1515886657613-9f3515b0c78f','1483985988355-763728e1935b','1490481651871-ab68de25d43d'],
            'kid|child|baby|toy'                => ['1596461404969-9ae70f2830c1','1515488042361-ee00e0ddd4e4','1522771930-78848d9293e8'],
            'shoe|footwear|sneaker|sandal'      => ['1542291026-7eec264c27ff','1491553895911-0055eca6402d','1560343090-f0409e92791a','1549298916-b41d501d3772'],
            'bag|handbag|backpack|luggage'      => ['1548036328-c9fa89d128fa','1553062407-98eeb64c6a62','1590874103328-eac38a683ce7'],
            'jewel|ring|necklace|accessor'      => ['1515562141207-7a88fb7ce338','1599643478518-a784e5dc4c8f','1535632066927-ab7c9ab60908'],
            'beauty|cosmetic|makeup|skin|care'  => ['1556228453-efd6c1ff04f6','1596462502278-27bfdc403348','1512496015851-a90fb38ba796'],
            'perfume|fragrance'                 => ['1585386959984-a4155224a1ad','1541643600914-78b084683601','1594035910387-fea47794261f'],
            'health|medic|pharma|vitamin'       => ['1584308666744-24d5c474f2ae','1471864190281-a93a3070b6de'],
            'sport|fitness|gym|outdoor'         => ['1517836357463-d25dfeac3438','1571019613454-1cb2f99b2d8b','1534438327276-14e5300c3a48'],
            'home|decor|furniture|living'       => ['1555041469-a586c61ea9bc','1592078615290-033ee584e267','1586023492125-27b2c045efd7'],
            'kitchen|cook|appliance'            => ['1556909114-f6e7ad7d3136','1556911220-bff31c812dba'],
            'grocer|food|snack|fruit|vegetable' => ['1542838132-92c53300491e','1512621776951-a57141f2eefd','1610832958506-aa56368176cf','1488459716781-31db52582fe9'],
            'beverage|drink|coffee|tea|juice'   => ['1497515114629-f71d768fd07c','1544787219-7f47ccb76574','1600271886742-f049cd451bba'],
            'meat|seafood|fish'                 => ['1607623814075-e51df1bdc82f','1535473895227-bdecb20fb157'],
            'bakery|bread|breakfast|dairy'      => ['1509440159596-0249088772ff','1555507036-ab1f4038808a','1550583724-b2692b85b150'],
            'frozen|ice'                        => ['1497034825429-c343d7c6a68f','1501443762994-82bd5dace89a'],
            'pet|dog|cat'                       => ['1548199973-03cce0bbc87b','1543852786-1cf6624b9987','1425082661705-1834bfd09dca'],
            'book|stationery|office'            => ['1495446815901-a7297e633e8d','1512820790803-83ca734da794','1456735190827-d1262f71b8a3'],
            'auto|car|bike|motor'               => ['1511919884226-fd3cad34687c','1503376780353-7e6692767b70'],
            'garden|plant|tool'                 => ['1466692476868-aef1dfb1e735','1416879595882-3373a0480b5b'],
            'gift'                              => ['1513885535751-8b9238bd345a','1549465220-1a8b9238cd48'],
        ];
        $generic = ['1472851294608-062f824d29cc','1607082348824-0a96f2a4b9da','1441986300917-64674bd600d8','1523275335684-37898b6baf30','1505740420928-5e560c06d30e','1542291026-7eec264c27ff','1556228453-efd6c1ff04f6','1526170375885-4d8ecf77b99f','1496181133206-80ce9b88a853','1572635196237-14b3f281503f'];
        $ids = $generic;
        foreach ($sets as $keys => $list) {
            foreach (explode('|', $keys) as $k) {
                if ($k !== '' && str_contains($n, $k)) { $ids = $list; break 2; }
            }
        }
        $id = $ids[abs($seed) % count($ids)];
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";
    }
}

if (!function_exists('zbg')) {
    /** Named editorial background photos used in banners & heroes. */
    function zbg(string $key, int $w = 1800): string
    {
        $ids = [
            'hero1'   => '1607082348824-0a96f2a4b9da', // shopping bags, pastel
            'hero2'   => '1441986300917-64674bd600d8', // clothing rack, dark
            'hero3'   => '1483985988355-763728e1935b', // shopper with bags
            'side'    => '1523275335684-37898b6baf30', // classic watch
            'sidecard'=> '1548036328-c9fa89d128fa', // leather bag
            'cash'    => '1542838132-92c53300491e',    // groceries
            'deal'    => '1472851294608-062f824d29cc', // store front
            'shop'    => '1555529669-e69e7aa0ba9a',    // product shelf
            'cart'    => '1556740758-90de374c12ad',    // cart / checkout
            'about'   => '1521737604893-d14cc237f11d', // team
            'contact' => '1423666639041-f56000c27a9a', // desk / phone
            'news'    => '1557804506-669a67965ba0',    // envelope / notes
            'promo1'  => '1505740420928-5e560c06d30e',
            'promo2'  => '1542291026-7eec264c27ff',
            'promo3'  => '1526170375885-4d8ecf77b99f',
            'promo4'  => '1556228453-efd6c1ff04f6',
        ];
        $id = $ids[$key] ?? $ids['hero1'];
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";
    }
}

if (!function_exists('zimg')) {
    /**
     * Convert a stored image path (or absolute URL) into a browser URL.
     */
    function zimg(?string $path): ?string
    {
        if (!$path) return null;
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//']) ? $path : asset('storage/' . $path);
    }
}

if (!function_exists('zcur')) {
    /** Active currency: ['code','symbol','name','rate','decimals']. */
    function zcur(): array
    {
        return \App\Support\Currency::current();
    }
}
if (!function_exists('zprice')) {
    /** Format a base-currency (DB) amount in the visitor's currency, e.g. "$12.50". */
    function zprice($amount): string
    {
        return \App\Support\Currency::format($amount, zcur());
    }
}
if (!function_exists('zorder_price')) {
    /** Format a base-currency amount in the currency an order was placed in (falls back to the active one). */
    function zorder_price($order, $amount): string
    {
        if ($order && !empty($order->currency_code)) {
            $cur = \App\Support\Currency::all()[$order->currency_code] ?? [];
            return \App\Support\Currency::format($amount, [
                'symbol'   => $order->currency_symbol ?: ($cur['symbol'] ?? $order->currency_code),
                'rate'     => (float) ($order->exchange_rate ?: ($cur['rate'] ?? 1)),
                'decimals' => $cur['decimals'] ?? 2,
            ]);
        }
        return zprice($amount);
    }
}
if (!function_exists('zcountries')) {
    /** Countries for the header Location dropdown. */
    function zcountries(): array
    {
        return \App\Support\Currency::countries();
    }
}
if (!function_exists('zcountry')) {
    /** Currently selected/detected country code (e.g. "US"). */
    function zcountry(): string
    {
        return \App\Support\Currency::currentCountry() ?: 'US';
    }
}
if (!function_exists('zpkr')) {
    /** @deprecated use zprice() */
    function zpkr($amount): string
    {
        return zprice($amount);
    }
}

if (!function_exists('zlogo')) {
    /** Inline an SVG from public/images (so its animations run). $suffix makes ids unique per instance. */
    function zlogo(string $file, string $suffix = ''): string
    {
        static $cache = [];
        $key = $file . '|' . $suffix;
        if (!isset($cache[$key])) {
            $path = public_path('images/' . $file);
            $svg  = is_file($path) ? file_get_contents($path) : '';
            if ($svg === '') return '<img src="' . asset('images/' . $file) . '" alt="Zelari">';
            if ($suffix !== '') {
                $svg = preg_replace('/id="([a-z]+-)([a-z]+)"/i', 'id="$1$2' . $suffix . '"', $svg);
                $svg = preg_replace('/url\(#([a-z]+-)([a-z]+)\)/i', 'url(#$1$2' . $suffix . ')', $svg);
            }
            $svg = preg_replace('/\s(width|height)="[^"]*"/', '', $svg, 2);
            $cache[$key] = $svg;
        }
        return $cache[$key];
    }
}

if (!function_exists('ztheme')) {
    /** Brand colour from config/theme.php, e.g. ztheme('brand') => '#1e1b4b'. For inline styles (email/PDF). */
    function ztheme(string $key, ?string $default = null): string
    {
        return (string) config('theme.' . $key, $default ?? '#1e1b4b');
    }
}
if (!function_exists('zbrand')) {
    /**
     * Branding asset URL from Settings, falling back to the bundled default.
     * $key: logo | icon | print   (store settings brand_logo / brand_icon / brand_print)
     */
    function zbrand(string $key): string
    {
        $defaults = ['logo' => 'images/logo.png', 'icon' => 'images/favicon-192.png', 'print' => 'images/logo-print.png'];
        $rel = \App\Models\Setting::get('brand_' . $key);
        if ($rel && is_file(public_path($rel))) {
            return asset($rel) . '?v=' . filemtime(public_path($rel));
        }
        return asset($defaults[$key] ?? $defaults['logo']);
    }
}
if (!function_exists('zbrand_path')) {
    /** Absolute filesystem path of a branding asset (for dompdf). */
    function zbrand_path(string $key): string
    {
        $defaults = ['logo' => 'images/logo.png', 'icon' => 'images/favicon-192.png', 'print' => 'images/logo-print.png'];
        $rel = \App\Models\Setting::get('brand_' . $key);
        if ($rel && is_file(public_path($rel))) {
            return public_path($rel);
        }
        return public_path($defaults[$key] ?? $defaults['logo']);
    }
}
if (!function_exists('zbrand_custom')) {
    /** True when the admin uploaded their own file for this asset. */
    function zbrand_custom(string $key): bool
    {
        $rel = \App\Models\Setting::get('brand_' . $key);
        return $rel && is_file(public_path($rel));
    }
}
if (!function_exists('zstore')) {
    /** Store setting shortcut: zstore('phone') => Setting store_phone. */
    function zstore(string $key, $default = null)
    {
        return \App\Models\Setting::get('store_' . $key, $default);
    }
}
if (!function_exists('zship')) {
    /** Shipping/delivery setting: zship('delivery_days') => "2–5". */
    function zship(string $key, $default = null)
    {
        return \App\Models\Setting::get('ship_' . $key, $default);
    }
}
if (!function_exists('zusd')) {
    /** Convert a USD amount (as entered in admin) to the base currency used for stored prices. */
    function zusd($usd): float
    {
        $rate = (float) (\App\Support\Currency::all()['USD']['rate'] ?? 1); // base → USD
        return $rate > 0 ? (float) $usd / $rate : (float) $usd;
    }
}
if (!function_exists('zfree_over')) {
    /** Free-delivery threshold in the base currency (0 = always free). */
    function zfree_over(): float
    {
        return zusd((float) zship('free_over_usd', 0));
    }
}
if (!function_exists('zshipping')) {
    /** Delivery fee (base currency) for a given base-currency subtotal. */
    function zshipping(float $subtotalBase): float
    {
        $fee  = (float) zship('fee_usd', 0);
        $free = zfree_over();
        if ($fee <= 0) return 0.0;
        if ($free > 0 && $subtotalBase >= $free) return 0.0;
        return round(zusd($fee), 2);
    }
}
if (!function_exists('zcontent')) {
    /**
     * A text setting with placeholders filled in:
     * {store} {returns} {days} {hours} {free} {coverage}
     */
    function zcontent(string $key, $default = ''): string
    {
        $v = (string) \App\Models\Setting::get($key, $default);
        if ($v === '' || !str_contains($v, '{')) return $v;
        return strtr($v, [
            '{store}'    => zstore('name', 'Zelari'),
            '{returns}'  => zship('return_days', '7'),
            '{days}'     => zship('delivery_days', '2–5'),
            '{hours}'    => zship('pack_hours', '24'),
            '{free}'     => zfree_over() > 0 ? zprice(zfree_over()) : 'every order',
            '{coverage}' => zship('coverage', 'nationwide'),
        ]);
    }
}
if (!function_exists('zfaqs')) {
    /** Contact-page FAQs: admin-defined ("Question? | Answer" per line) or sensible defaults built from the shipping settings. */
    function zfaqs(): array
    {
        $raw = trim((string) \App\Models\Setting::get('store_faqs', ''));
        $out = [];
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (!str_contains($line, '|')) continue;
            [$q, $a] = array_map('trim', explode('|', $line, 2));
            if ($q !== '' && $a !== '') $out[] = [$q, $a];
        }
        if ($out) return $out;
        $days = zship('delivery_days', '2–5'); $hrs = zship('pack_hours', '24'); $ret = zship('return_days', '7'); $cov = zship('coverage', 'nationwide');
        $free = zfree_over();
        return [
            ['How long does delivery take?', "Orders are packed within {$hrs} hours and delivered in {$days} business days {$cov}. You'll get a call from the rider before arrival."],
            ['How much is delivery?', $free > 0 ? 'Delivery is free on orders over ' . zprice($free) . '. Below that, the fee is shown at checkout before you pay.' : 'Delivery is free on every order.'],
            ['Which payment methods do you accept?', implode(', ', array_column(\App\Support\Payments::enabledMethods(), 'label')) . '. All online payments are processed securely — we never see your card number.'],
            ['Can I pay cash on delivery?', \App\Support\Payments::isEnabled('cash_on_delivery') ? 'Yes — pay the rider in cash when your order arrives.' : 'Cash on delivery is not available right now; please pay online at checkout.'],
            ['What is your return policy?', "You can return any unused item within {$ret} days of delivery. Contact us with your order number and we'll arrange the pickup."],
            ['How do I track my order?', "Reply to your confirmation email or message us on WhatsApp with your order number and we'll send the latest status."],
            ['Can I change or cancel my order?', "Yes, as long as it hasn't been packed yet — usually within {$hrs} hours of ordering. Contact us as soon as possible with your order number."],
            ['Will prices change if I switch currency?', 'No. Prices are converted for display only; the amount you see at checkout is what you pay.'],
        ];
    }
}
if (!function_exists('ztel')) {
    /** Digits-only phone for tel:/wa.me links. */
    function ztel(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }
}
