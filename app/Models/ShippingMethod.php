<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = ['zone_id', 'name', 'rate', 'free_over', 'enabled', 'sort'];

    protected $casts = [
        'rate'      => 'decimal:2',
        'free_over' => 'decimal:2',
        'enabled'   => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /** Fee for a given base-currency subtotal (free when the threshold is met). */
    public function feeFor(float $subtotal): float
    {
        if ($this->free_over !== null && $subtotal >= (float) $this->free_over) {
            return 0.0;
        }
        return round((float) $this->rate, 2);
    }
}
