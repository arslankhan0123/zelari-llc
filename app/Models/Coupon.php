<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_spend', 'max_discount',
        'usage_limit', 'used_count', 'expires_at', 'active', 'source', 'email', 'user_id',
    ];

    protected $casts = [
        'value'        => 'decimal:2',
        'min_spend'    => 'decimal:2',
        'max_discount' => 'decimal:2',
        'active'       => 'boolean',
        'expires_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function entries()
    {
        return $this->hasMany(WheelEntry::class);
    }
}
