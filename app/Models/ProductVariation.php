<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $fillable = ['product_id', 'sku', 'options', 'price', 'sale_price', 'quantity', 'image', 'enabled', 'sort'];

    protected $casts = [
        'options'    => 'array',
        'price'      => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity'   => 'integer',
        'enabled'    => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective selling price. */
    public function effectivePrice(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    /** "Size: M, Color: Red" */
    public function label(): string
    {
        return collect($this->options ?? [])->map(fn ($v, $k) => "$k: $v")->implode(', ');
    }
}
