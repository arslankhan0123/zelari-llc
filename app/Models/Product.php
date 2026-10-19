<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'sku',
        'feature_image', 'purchase_price', 'sale_price',
        'quantity', 'short_description', 'description', 'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'quantity'       => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($p) {
            if (empty($p->slug)) {
                $p->slug = Str::slug($p->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->sale_price && $this->purchase_price > 0 && $this->sale_price < $this->purchase_price) {
            return (int) round((($this->purchase_price - $this->sale_price) / $this->purchase_price) * 100);
        }
        return null;
    }

    public function getEffectivePriceAttribute(): string
    {
        return number_format($this->sale_price ?? $this->purchase_price, 2);
    }

    /** Numeric price the customer pays. */
    public function getPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->purchase_price);
    }

    /** Strike-through price when on sale, otherwise null. */
    public function getComparePriceAttribute(): ?float
    {
        return ($this->sale_price && $this->sale_price < $this->purchase_price)
            ? (float) $this->purchase_price
            : null;
    }

    /** Full URL of the primary image (feature image, else first gallery image). */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->feature_image;
        if (!$path && $this->relationLoaded('images')) {
            $path = optional($this->images->first())->image_path;
        } elseif (!$path) {
            $path = optional($this->images()->first())->image_path;
        }
        if (!$path) {
            return null;
        }
        return Str::startsWith($path, ['http://', 'https://', '//']) ? $path : asset('storage/' . $path);
    }

    /** Image to display: the real image, else a category-matched stock photo. */
    public function getDisplayImageAttribute(): string
    {
        return $this->has_real_image ? $this->image_url : zcat_image(optional($this->category)->name, (int) $this->id, 800);
    }

    /**
     * True only for images uploaded to this store. Remote URLs (e.g. seeded
     * placeholder images) count as "no image" unless STORE_TRUST_REMOTE_IMAGES=true.
     */
    public function getHasRealImageAttribute(): bool
    {
        $url = $this->image_url;
        if ($url === null) return false;
        if (Str::startsWith($url, ['http://', 'https://', '//']) && !Str::startsWith($url, url('/'))) {
            return filter_var(env('STORE_TRUST_REMOTE_IMAGES', false), FILTER_VALIDATE_BOOL);
        }
        return true;
    }

    public function getInStockAttribute(): bool
    {
        return $this->quantity > 0;
    }
}
