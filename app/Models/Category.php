<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'description', 'status', 'image'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        // Remove the stored image file when the category is deleted
        static::deleted(function ($category) {
            $category->deleteImageFile();
        });
    }

    /** Delete the image file from storage (safe to call when there is none). */
    public function deleteImageFile(): void
    {
        $path = $this->getRawOriginal('image') ?: $this->image;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** True when an uploaded image exists on disk. */
    public function hasImage(): bool
    {
        return $this->image && Storage::disk('public')->exists($this->image);
    }

    /** Browser URL for the category image; falls back to a themed stock image. */
    public function getImageUrlAttribute(): string
    {
        if ($this->hasImage()) {
            return asset('storage/' . $this->image) . '?v=' . Storage::disk('public')->lastModified($this->image);
        }
        return zcat_image($this->name, (int) $this->id, 600);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /** Active children, recursively eager-loaded to any depth. */
    public function childrenRecursive(): HasMany
    {
        return $this->children()
            ->where('status', 'active')
            ->orderBy('name')
            ->with('childrenRecursive');
    }

    /** IDs of this category and every descendant (uses loaded relations when present). */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $children = $this->relationLoaded('childrenRecursive')
            ? $this->childrenRecursive
            : $this->childrenRecursive()->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }
        return $ids;
    }

    /** Breadcrumb trail from the root down to this category. */
    public function ancestors(): array
    {
        $trail = [];
        $node = $this->parent;
        while ($node) {
            array_unshift($trail, $node);
            $node = $node->parent;
        }
        return $trail;
    }

    /**
     * Assign $this->total_count (products in this category + all descendants)
     * from a [category_id => count] map, recursively. Returns the total.
     */
    public function assignCounts(array $counts): int
    {
        $total = (int) ($counts[$this->id] ?? 0);
        if ($this->relationLoaded('childrenRecursive')) {
            foreach ($this->childrenRecursive as $child) {
                $total += $child->assignCounts($counts);
            }
        }
        $this->setAttribute('total_count', $total);
        return $total;
    }

    /** Map of active product counts keyed by category_id. */
    public static function productCountMap(): array
    {
        return Product::where('status', 'active')
            ->selectRaw('category_id, COUNT(*) as c')
            ->groupBy('category_id')
            ->pluck('c', 'category_id')
            ->all();
    }

    public function hasChildren(): bool
    {
        return $this->relationLoaded('childrenRecursive')
            ? $this->childrenRecursive->isNotEmpty()
            : $this->children()->where('status', 'active')->exists();
    }
}
