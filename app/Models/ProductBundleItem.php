<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleItem extends Model
{
    protected $fillable = ['bundle_id', 'product_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
