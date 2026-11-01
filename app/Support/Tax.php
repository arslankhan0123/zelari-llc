<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Setting;

class Tax
{
    public static function enabled(): bool
    {
        return (string) Setting::get('tax_enabled', '0') === '1';
    }

    public static function rate(): float
    {
        return (float) Setting::get('tax_rate', '0');
    }

    public static function inclusive(): bool
    {
        return (string) Setting::get('tax_inclusive', '0') === '1';
    }

    public static function label(): string
    {
        return (string) Setting::get('tax_label', 'Tax') ?: 'Tax';
    }

    /**
     * Compute tax for a set of line items (base currency).
     * @param array $items [['product_id'=>int|null, 'subtotal'=>float], ...]
     * @return array ['amount'=>float, 'inclusive'=>bool, 'rate'=>float, 'label'=>string, 'taxable'=>float]
     * Items whose product's category is tax-exempt are left out of the taxable base.
     */
    public static function compute(array $items): array
    {
        $rate     = static::rate();
        $incl     = static::inclusive();
        $label    = static::label();

        if (!static::enabled() || $rate <= 0) {
            return ['amount' => 0.0, 'inclusive' => $incl, 'rate' => $rate, 'label' => $label, 'taxable' => 0.0];
        }

        $ids    = array_filter(array_map(fn ($i) => $i['product_id'] ?? null, $items));
        $exempt = Product::whereIn('id', $ids)->with('category:id,tax_exempt')
            ->get()->mapWithKeys(fn ($p) => [$p->id => (bool) optional($p->category)->tax_exempt]);

        $taxable = 0.0;
        foreach ($items as $it) {
            $pid = $it['product_id'] ?? null;
            if ($pid && ($exempt[$pid] ?? false)) {
                continue; // exempt category
            }
            $taxable += (float) ($it['subtotal'] ?? 0);
        }

        if ($taxable <= 0) {
            return ['amount' => 0.0, 'inclusive' => $incl, 'rate' => $rate, 'label' => $label, 'taxable' => 0.0];
        }

        $amount = $incl
            ? $taxable - ($taxable / (1 + $rate / 100))   // tax already inside the price
            : $taxable * $rate / 100;                       // added on top

        return [
            'amount'    => round($amount, 2),
            'inclusive' => $incl,
            'rate'      => $rate,
            'label'     => $label,
            'taxable'   => round($taxable, 2),
        ];
    }
}
