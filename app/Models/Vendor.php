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
        'description', 'status', 'commission_rate', 'address', 'city',
        'country', 'meta', 'admin_notes', 'approved_at',
    ];

    protected $casts = [
        'meta'            => 'array',
        'approved_at'     => 'datetime',
        'commission_rate' => 'decimal:2',
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
