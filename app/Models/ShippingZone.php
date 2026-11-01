<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'countries', 'is_rest', 'enabled', 'sort'];

    protected $casts = [
        'countries' => 'array',
        'is_rest'   => 'boolean',
        'enabled'   => 'boolean',
    ];

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')->orderBy('sort');
    }

    public function coversCountry(?string $cc): bool
    {
        if ($this->is_rest) {
            return true;
        }
        if (!$cc) {
            return false;
        }
        return in_array(strtoupper($cc), array_map('strtoupper', (array) ($this->countries ?? [])), true);
    }
}
