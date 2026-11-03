<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Vendor;

class Commission
{
    /** Platform-wide default commission rate (percent). */
    public static function defaultRate(): float
    {
        return (float) Setting::get('commission_default_rate', '10');
    }

    /**
     * Resolve the commission rate (percent) for a sale.
     * Precedence: category override → vendor rate → global default.
     */
    public static function rateFor(?Vendor $vendor, ?Category $category = null): float
    {
        if ($category && $category->commission_rate !== null) {
            return (float) $category->commission_rate;
        }
        if ($vendor && $vendor->commission_rate !== null) {
            return (float) $vendor->commission_rate;
        }
        return static::defaultRate();
    }
}
