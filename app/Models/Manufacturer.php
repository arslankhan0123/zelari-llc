<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** A brand / manufacturer products can belong to (e.g. Engine, Gul Ahmed). */
class Manufacturer extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo', 'description', 'website', 'country',
        'email', 'phone', 'established_year', 'featured', 'sort_order', 'status',
    ];

    protected $casts = [
        'featured'         => 'boolean',
        'sort_order'       => 'integer',
        'established_year' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        $slugify = function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        };
        static::creating($slugify);
        static::updating($slugify);

        // Remove the logo file when the brand is deleted
        static::deleted(fn ($m) => $m->deleteLogoFile());
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Brands in the order they should be shown: featured first, then sort order, then name. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('featured')->orderBy('sort_order')->orderBy('name');
    }

    /** True when a logo file exists on disk. */
    public function hasLogo(): bool
    {
        return $this->logo && Storage::disk('public')->exists($this->logo);
    }

    /** Browser URL for the logo, or null when there is none. */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->hasLogo()) {
            return null;
        }
        return asset('storage/' . $this->logo) . '?v=' . Storage::disk('public')->lastModified($this->logo);
    }

    /** Up to two letters used when a brand has no logo. */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [''];
        $first = mb_substr($words[0] ?? '', 0, 1);
        $second = count($words) > 1 ? mb_substr($words[1], 0, 1) : mb_substr($words[0] ?? '', 1, 1);
        return mb_strtoupper($first . $second);
    }

    /** Delete the logo file from storage (safe to call when there is none). */
    public function deleteLogoFile(): void
    {
        $path = $this->getRawOriginal('logo') ?: $this->logo;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
