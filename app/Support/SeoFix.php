<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Safe, deterministic SEO auto-fixes at the data level.
 * (Template-level fixes — canonical, Open Graph/Twitter, image alt — are applied
 * globally in the store layout and need no per-run work.)
 * What this does NOT touch: product images and written content — those need a human.
 */
class SeoFix
{
    public static function run(): array
    {
        $fixed = ['slugs' => 0];

        try {
            $used = Product::whereNotNull('slug')->pluck('slug')->flip();
            Product::where(function ($q) {
                $q->whereNull('slug')->orWhere('slug', '');
            })->get(['id', 'name'])->each(function ($p) use (&$fixed, &$used) {
                $base = Str::slug($p->name) ?: 'product-'.$p->id;
                $slug = $base;
                $n = 2;
                while (isset($used[$slug])) {
                    $slug = $base.'-'.$n++;
                }
                $p->slug = $slug;
                $p->saveQuietly();
                $used[$slug] = true;
                $fixed['slugs']++;
            });
        } catch (\Throwable $e) {
        }

        return $fixed;
    }
}
