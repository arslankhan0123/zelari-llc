<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A customer review of a product. Hidden from the storefront until an admin approves it. */
class Review extends Model
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'product_id', 'user_id', 'order_id',
        'author_name', 'author_email', 'rating', 'title', 'body',
        'verified_purchase', 'status', 'admin_note', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'rating'            => 'integer',
        'verified_purchase' => 'boolean',
        'approved_at'       => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        // Keep products.reviews_count / reviews_avg in step with approvals
        static::saved(fn (Review $r) => $r->syncProductRating());
        static::deleted(fn (Review $r) => $r->syncProductRating());
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', self::APPROVED);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /** First letter(s) of the author's name, for the avatar circle. */
    public function getInitialAttribute(): string
    {
        return mb_strtoupper(mb_substr(trim((string) $this->author_name), 0, 1)) ?: '?';
    }

    /** Recalculate the cached rating on the product this review belongs to. */
    public function syncProductRating(): void
    {
        $product = $this->product()->first();
        if (!$product) {
            return;
        }
        $approved = static::where('product_id', $product->id)->approved();
        $count    = (clone $approved)->count();

        $product->forceFill([
            'reviews_count' => $count,
            'reviews_avg'   => $count ? round((clone $approved)->avg('rating'), 2) : null,
        ])->saveQuietly();
    }
}
