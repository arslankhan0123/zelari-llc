<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Auto-generates a 7-day social content plan from real catalog data:
 * ready-to-post captions + hashtags + share links. No AI/API — template driven.
 */
class MarketingPlan
{
    const FILE = 'marketing/plan.json';

    public static function generate(): array
    {
        $store = zstore('name', 'Zelari');
        $home  = url('/');
        $shop  = url('/shop');

        $active = fn () => Product::where('status', 'active')->whereNotNull('slug');
        $deals  = $active()->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'purchase_price')->latest()->take(10)->get();
        $fresh  = $active()->latest()->take(10)->get();
        $pool   = $active()->inRandomOrder()->take(15)->get();

        $pick = function ($coll, &$idx) {
            if ($coll->isEmpty()) return null;
            $p = $coll[$idx % $coll->count()];
            $idx++;
            return $p;
        };

        $tags = function ($p = null) use ($store) {
            $base = ['#'.Str::of($store)->replace(' ', '')->lower(), '#onlineshopping', '#shopnow', '#ecommerce'];
            if ($p && $p->category) $base[] = '#'.Str::of($p->category->name)->replace([' ', '&', '/'], '')->lower();
            $base[] = '#deals';
            return array_values(array_unique(array_slice($base, 0, 6)));
        };

        $urlOf = fn ($p) => url('/product/'.$p->slug);
        $desc  = fn ($p) => Str::limit(trim(strip_tags((string) ($p->short_description ?: $p->description ?: ''))), 110) ?: 'a customer favourite';
        $priceOf = fn ($p) => zprice((float) ($p->sale_price ?: $p->purchase_price));
        $discOf = fn ($p) => ($p->purchase_price > 0 && $p->sale_price) ? (int) round((1 - $p->sale_price / $p->purchase_price) * 100) : 0;

        // 7-day rotation of post types
        $types = ['spotlight', 'deal', 'newarrival', 'category', 'tip', 'socialproof', 'spotlight'];
        $di = 0; $fi = 0; $pi = 0;
        $plan = [];

        foreach ($types as $i => $type) {
            $date = now()->addDays($i)->toDateString();
            $item = ['day' => now()->addDays($i)->format('l'), 'date' => $date, 'type' => $type];

            if ($type === 'deal' && $deals->isNotEmpty()) {
                $p = $pick($deals, $di);
                $d = $discOf($p);
                $item += ['label' => 'Flash Deal', 'product' => $p->name, 'url' => $urlOf($p), 'image' => $p->display_image, 'price' => $priceOf($p)];
                $item['caption'] = "🔥 DEAL ALERT! The {$p->name} is now just {$priceOf($p)}".($d > 0 ? " — that's {$d}% OFF" : '').". Limited stock, grab it before it's gone! 👉 {$urlOf($p)}";
                $item['hashtags'] = $tags($p);
            } elseif ($type === 'newarrival' && $fresh->isNotEmpty()) {
                $p = $pick($fresh, $fi);
                $item += ['label' => 'New Arrival', 'product' => $p->name, 'url' => $urlOf($p), 'image' => $p->display_image, 'price' => $priceOf($p)];
                $item['caption'] = "🆕 Just landed: the {$p->name}! Fresh in store and ready to ship worldwide. Be the first to get it 👉 {$urlOf($p)}";
                $item['hashtags'] = $tags($p);
            } elseif ($type === 'category') {
                $p = $pick($pool, $pi);
                $cat = $p && $p->category ? $p->category->name : 'great products';
                $item += ['label' => 'Collection', 'url' => $shop, 'image' => $p->display_image ?? null, 'product' => $cat];
                $item['caption'] = "Looking for {$cat}? {$store} has you covered — honest prices and fast worldwide delivery. Browse the collection 👉 {$shop}";
                $item['hashtags'] = $tags($p);
            } elseif ($type === 'tip') {
                $item += ['label' => 'Engagement', 'url' => $home, 'product' => null, 'image' => null];
                $item['caption'] = "💡 Shopping tip: at {$store} you get cash on delivery + fast worldwide shipping, so you can shop with zero stress. Start here 👉 {$home}";
                $item['hashtags'] = $tags();
            } elseif ($type === 'socialproof') {
                $item += ['label' => 'Social Proof', 'url' => $home, 'product' => null, 'image' => null];
                $item['caption'] = "⭐⭐⭐⭐⭐ Thousands love shopping with {$store} — quality products, honest prices, delivered to your door. Join them today 👉 {$home}";
                $item['hashtags'] = $tags();
            } else { // spotlight
                $p = $pick($pool, $pi);
                if (!$p) { $p = $fresh->first(); }
                if ($p) {
                    $item += ['label' => 'Product Spotlight', 'product' => $p->name, 'url' => $urlOf($p), 'image' => $p->display_image, 'price' => $priceOf($p)];
                    $item['caption'] = "✨ Meet the {$p->name} — {$desc($p)}. Yours for {$priceOf($p)}. Shop now 👉 {$urlOf($p)}";
                    $item['hashtags'] = $tags($p);
                } else {
                    $item += ['label' => 'Spotlight', 'url' => $shop, 'product' => null, 'image' => null];
                    $item['caption'] = "✨ Discover something you'll love at {$store}. Shop the latest 👉 {$shop}";
                    $item['hashtags'] = $tags();
                }
            }
            // share links
            $su = urlencode($item['url']); $st = urlencode($item['caption']);
            $item['share'] = [
                'facebook'  => "https://www.facebook.com/sharer/sharer.php?u={$su}",
                'whatsapp'  => "https://wa.me/?text={$st}",
                'x'         => "https://twitter.com/intent/tweet?text={$st}",
            ];
            $plan[] = $item;
        }

        $report = ['generated_at' => now()->toIso8601String(), 'week_of' => now()->toDateString(), 'items' => $plan];
        try { Storage::disk('local')->put(self::FILE, json_encode($report)); } catch (\Throwable $e) {}
        return $report;
    }

    public static function latest(): ?array
    {
        try {
            if (Storage::disk('local')->exists(self::FILE)) {
                return json_decode(Storage::disk('local')->get(self::FILE), true);
            }
        } catch (\Throwable $e) {}
        return null;
    }
}
