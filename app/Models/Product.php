<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'manufacturer_id', 'name', 'slug', 'sku',
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

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Only the reviews customers are allowed to see. */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', Review::APPROVED)->latest();
    }

    /** Average rating rounded to the nearest whole star (0 when there are none). */
    public function getStarsAttribute(): int
    {
        return (int) round((float) $this->reviews_avg);
    }

    /** How many approved reviews gave each star, e.g. [5 => 3, 4 => 1, ...]. */
    public function ratingBreakdown(): array
    {
        $rows = $this->reviews()->where('status', Review::APPROVED)
            ->selectRaw('rating, COUNT(*) as c')->groupBy('rating')->pluck('c', 'rating')->all();

        return collect(range(5, 1))->mapWithKeys(fn ($s) => [$s => (int) ($rows[$s] ?? 0)])->all();
    }

    /**
     * The order that entitles this user to review the product — they must have
     * bought it on an order that was not cancelled. Null when they may not review.
     */
    public function purchaseOrderFor(?User $user): ?Order
    {
        if (!$user) {
            return null;
        }

        return Order::where('status', '!=', 'cancelled')
            ->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereRaw('LOWER(customer_email) = ?', [mb_strtolower($user->email)]))
            ->whereHas('items', fn ($q) => $q->where('product_id', $this->id))
            ->latest()
            ->first();
    }

    /** The order with this number that contains the product and belongs to that email. */
    public function purchaseOrderForEmail(?string $orderNumber, ?string $email): ?Order
    {
        $orderNumber = trim((string) $orderNumber);
        $email       = mb_strtolower(trim((string) $email));
        if ($orderNumber === '' || $email === '') {
            return null;
        }

        return Order::where('status', '!=', 'cancelled')
            ->whereRaw('LOWER(order_number) = ?', [mb_strtolower($orderNumber)])
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->whereHas('items', fn ($q) => $q->where('product_id', $this->id))
            ->first();
    }

    /** The review left for this product from that email address, if any. */
    public function reviewByEmail(?string $email): ?Review
    {
        $email = mb_strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }
        return $this->reviews()->whereRaw('LOWER(author_email) = ?', [$email])->first();
    }

    /** The review this user already left for the product, if any. */
    public function reviewBy(?User $user): ?Review
    {
        if (!$user) {
            return null;
        }
        return $this->reviews()
            ->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereRaw('LOWER(author_email) = ?', [mb_strtolower($user->email)]))
            ->first();
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
