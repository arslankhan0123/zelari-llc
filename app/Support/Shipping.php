<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\Vendor;

class Shipping
{
    /**
     * Quote shipping for a set of line items.
     * @param array $items  [['product_id'=>int|null, 'subtotal'=>float], ...]
     * @return array ['total'=>float, 'lines'=>[['label'=>string,'fee'=>float], ...]]
     *
     * Precedence per vendor: vendor flat rate → matching shipping zone method → global flat (legacy).
     * When nothing is configured it returns exactly the legacy global quote, so behaviour is
     * unchanged until an admin sets up zones or a vendor sets a rate.
     */
    public static function quote(array $items, ?string $country = null): array
    {
        // Only physical goods ship; digital/downloadable items are excluded.
        $ids  = array_filter(array_map(fn ($i) => $i['product_id'] ?? null, $items));
        $meta = Product::whereIn('id', $ids)->get(['id', 'vendor_id', 'type'])->keyBy('id');
        $ships = fn ($it) => !isset($meta[$it['product_id'] ?? 0])
            || !in_array($meta[$it['product_id']]->type, ['digital', 'downloadable'], true);

        $shipItems = array_filter($items, $ships);

        $grandSub = 0.0;
        foreach ($shipItems as $it) {
            $grandSub += (float) ($it['subtotal'] ?? 0);
        }

        if (empty($shipItems)) {
            return ['total' => 0.0, 'lines' => []];   // all-digital order
        }

        $configured = ShippingZone::where('enabled', true)->exists()
            || Vendor::whereNotNull('shipping_flat')->exists();

        if (!$configured) {
            $fee = zshipping($grandSub);
            return ['total' => $fee, 'lines' => [['label' => 'Shipping', 'fee' => $fee]]];
        }

        $byVendor = []; // vendor_id (0 = none) => subtotal
        foreach ($shipItems as $it) {
            $vid = (int) (optional($meta[$it['product_id'] ?? 0])->vendor_id ?? 0);
            $byVendor[$vid] = ($byVendor[$vid] ?? 0) + (float) ($it['subtotal'] ?? 0);
        }

        $vendors = Vendor::whereIn('id', array_filter(array_keys($byVendor)))->get()->keyBy('id');

        $total = 0.0;
        $lines = [];
        foreach ($byVendor as $vid => $sub) {
            $vendor = $vid ? $vendors->get($vid) : null;
            $fee    = static::vendorFee($vendor, (float) $sub, $country);
            $total += $fee;
            $lines[] = ['label' => $vendor ? ('Shipping — ' . $vendor->name) : 'Shipping', 'fee' => $fee];
        }

        return ['total' => round($total, 2), 'lines' => $lines];
    }

    /** Convenience: just the total. */
    public static function total(array $items, ?string $country = null): float
    {
        return static::quote($items, $country)['total'];
    }

    private static function vendorFee(?Vendor $vendor, float $sub, ?string $country): float
    {
        if ($vendor && $vendor->shipping_flat !== null) {
            if ($vendor->shipping_free_over !== null && $sub >= (float) $vendor->shipping_free_over) {
                return 0.0;
            }
            return round((float) $vendor->shipping_flat, 2);
        }

        $zoneFee = static::zoneFee($country, $sub);
        return $zoneFee !== null ? $zoneFee : zshipping($sub);
    }

    /** Cheapest enabled method in the zone that covers the country; null if no zone matches. */
    private static function zoneFee(?string $country, float $sub): ?float
    {
        $zones = ShippingZone::where('enabled', true)->with('methods')
            ->orderBy('is_rest')->orderBy('sort')->get();

        $zone = $zones->first(fn ($z) => !$z->is_rest && $z->coversCountry($country))
             ?? $zones->first(fn ($z) => $z->is_rest);

        if (!$zone) {
            return null;
        }

        $fees = $zone->methods->where('enabled', true)->map(fn ($m) => $m->feeFor($sub));
        return $fees->isEmpty() ? null : (float) $fees->min();
    }
}
