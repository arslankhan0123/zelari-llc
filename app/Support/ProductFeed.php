<?php

namespace App\Support;

use App\Models\Product;
use App\Support\Currency;
use Illuminate\Support\Str;

/**
 * Product catalog feeds for Google Merchant Center (RSS 2.0) and
 * Meta (Facebook/Instagram) Commerce (CSV). Prices output in USD.
 * Cached 1h; busted by the Product model on any catalog change.
 */
class ProductFeed
{
    private static function usdRate(): float
    {
        try { return (float) (Currency::all()['USD']['rate'] ?? 1) ?: 1; }
        catch (\Throwable $e) { return 1; }
    }

    private static function price($base): string
    {
        return number_format((float) $base * self::usdRate(), 2, '.', '').' USD';
    }

    private static function img(Product $p): string
    {
        $u = $p->display_image ?: asset('images/logo.png');
        return Str::startsWith($u, ['http://', 'https://']) ? $u : url($u);
    }

    private static function items()
    {
        return Product::where('status', 'active')->whereNotNull('slug')
            ->with(['category', 'manufacturer'])->get();
    }

    public static function google(): string
    {
        return cache()->remember('feed.google', 3600, function () {
            $x  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $x .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>'."\n";
            $x .= '<title>'.htmlspecialchars(zstore('name', 'Zelari')).'</title>'."\n";
            $x .= '<link>'.url('/').'</link>'."\n";
            $x .= '<description>'.htmlspecialchars(zstore('name', 'Zelari').' product feed').'</description>'."\n";
            foreach (self::items() as $p) {
                $price = (float) ($p->sale_price ?: $p->purchase_price);
                $desc  = Str::limit(trim(strip_tags((string) ($p->short_description ?: $p->description ?: $p->name))), 480);
                $brand = optional($p->manufacturer)->name ?: zstore('name', 'Zelari');
                $x .= "<item>\n";
                $x .= '<g:id>'.htmlspecialchars($p->sku ?: (string) $p->id).'</g:id>'."\n";
                $x .= '<g:title>'.htmlspecialchars(Str::limit($p->name, 150)).'</g:title>'."\n";
                $x .= '<g:description>'.htmlspecialchars($desc ?: $p->name).'</g:description>'."\n";
                $x .= '<g:link>'.htmlspecialchars(url('/product/'.$p->slug)).'</g:link>'."\n";
                $x .= '<g:image_link>'.htmlspecialchars(self::img($p)).'</g:image_link>'."\n";
                $x .= '<g:availability>'.(($p->quantity ?? 0) > 0 ? 'in_stock' : 'out_of_stock').'</g:availability>'."\n";
                $x .= '<g:price>'.self::price($price).'</g:price>'."\n";
                if ($p->sale_price && $p->sale_price < $p->purchase_price) {
                    $x .= '<g:sale_price>'.self::price($p->sale_price).'</g:sale_price>'."\n";
                }
                $x .= '<g:condition>new</g:condition>'."\n";
                $x .= '<g:brand>'.htmlspecialchars($brand).'</g:brand>'."\n";
                $x .= '<g:identifier_exists>no</g:identifier_exists>'."\n";
                if ($p->category) $x .= '<g:product_type>'.htmlspecialchars($p->category->name).'</g:product_type>'."\n";
                $x .= "</item>\n";
            }
            $x .= "</channel></rss>";
            return $x;
        });
    }

    public static function meta(): string
    {
        return cache()->remember('feed.meta', 3600, function () {
            $esc = fn ($v) => '"'.str_replace('"', '""', (string) $v).'"';
            $rows = [['id','title','description','availability','condition','price','link','image_link','brand','google_product_category','sale_price']];
            foreach (self::items() as $p) {
                $price = (float) ($p->sale_price ?: $p->purchase_price);
                $desc  = Str::limit(trim(strip_tags((string) ($p->short_description ?: $p->description ?: $p->name))), 480);
                $rows[] = [
                    $p->sku ?: (string) $p->id,
                    Str::limit($p->name, 150),
                    $desc ?: $p->name,
                    ($p->quantity ?? 0) > 0 ? 'in stock' : 'out of stock',
                    'new',
                    self::price($p->purchase_price),
                    url('/product/'.$p->slug),
                    self::img($p),
                    optional($p->manufacturer)->name ?: zstore('name', 'Zelari'),
                    optional($p->category)->name ?: '',
                    ($p->sale_price && $p->sale_price < $p->purchase_price) ? self::price($p->sale_price) : '',
                ];
            }
            return implode("\n", array_map(fn ($r) => implode(',', array_map($esc, $r)), $rows));
        });
    }
}
