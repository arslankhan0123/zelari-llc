<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelEntry extends Model
{
    protected $fillable = ['email', 'prize_label', 'coupon_id', 'ip'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
