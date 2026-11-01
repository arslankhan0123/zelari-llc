<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vendor extends Model
{
    public const PENDING   = 'pending';
    public const APPROVED  = 'approved';
    public const SUSPENDED = 'suspended';
    public const REJECTED  = 'rejected';

    protected $fillable = [
        'user_id', 'name', 'slug', 'email', 'phone', 'logo', 'banner',
        'description', 'status', 'commission_rate', 'shipping_flat', 'shipping_free_over', 'address', 'city',
        'country', 'meta', 'admin_notes', 'approved_at', 'plan_id',
        'return_policy', 'shipping_policy', 'payout_method', 'bank_name', 'account_name', 'account_number',
    ];

    protected $casts = [
        'meta'            => 'array',
        'approved_at'     => 'datetime',
        'commission_rate' => 'decimal:2',
        'shipping_flat'    => 'decimal:2',
        'shipping_free_over' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($v) {
            if (empty($v->slug)) {
                $base = Str::slug($v->name) ?: 'vendor';
                $slug = $base; $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . (++$i);
                }
                $v->slug = $slug;
            }
        });
    }

    // ── Relations ──────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }

    public function reviews(): HasMany { return $this->hasMany(VendorReview::class); }
    public function getRatingAvgAttribute(): float { return round((float) $this->reviews()->where('status','approved')->avg('rating'), 1); }
    public function getRatingCountAttribute(): int { return (int) $this->reviews()->where('status','approved')->count(); }

    public function vendorOrders(): HasMany
    {
        return $this->hasMany(VendorOrder::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    /**
     * Earnings snapshot (all amounts in base currency).
     * earned    = payout on delivered sub-orders (realised)
     * pending   = payout on active, not-yet-delivered sub-orders
     * paid      = total already paid out
     * reserved  = payout tied up in pending/approved payout requests
     * available = what the vendor can still request
     */
    public function earnings(): array
    {
        $subs   = $this->vendorOrders()->get(['status', 'subtotal', 'commission_amount', 'payout_amount']);
        $active = $subs->where('status', '!=', VendorOrder::CANCELLED);

        $gross      = (float) $active->sum('subtotal');
        $commission = (float) $active->sum('commission_amount');
        $earned     = (float) $active->where('status', VendorOrder::DELIVERED)->sum('payout_amount');
        $pending    = (float) $active->whereIn('status', [VendorOrder::PENDING, VendorOrder::PROCESSING, VendorOrder::SHIPPED])->sum('payout_amount');

        $paid     = (float) $this->payoutRequests()->where('status', PayoutRequest::PAID)->sum('amount');
        $reserved = (float) $this->payoutRequests()->whereIn('status', [PayoutRequest::PENDING, PayoutRequest::APPROVED])->sum('amount');

        $available = max(0, round($earned - $paid - $reserved, 2));

        return compact('gross', 'commission', 'earned', 'pending', 'paid', 'reserved', 'available');
    }

    // ── Scopes & helpers ───────────────────────────────────────
    public function scopeApproved($q) { return $q->where('status', self::APPROVED); }
    public function scopePending($q)  { return $q->where('status', self::PENDING); }

    public function isApproved(): bool  { return $this->status === self::APPROVED; }
    public function isPending(): bool   { return $this->status === self::PENDING; }
    public function isSuspended(): bool { return $this->status === self::SUSPENDED; }

    public function getLogoUrlAttribute(): string
    {
        return $this->logo ? asset('storage/' . ltrim($this->logo, '/')) : asset('images/logo.png');
    }

    public function getStatusColorAttribute(): string
    {
        return [
            self::APPROVED  => 'green',
            self::PENDING   => 'yellow',
            self::SUSPENDED => 'red',
            self::REJECTED  => 'draft',
        ][$this->status] ?? 'draft';
    }
}
