<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN       = 'admin';
    public const USER        = 'user';

    protected $fillable = ['name', 'slug', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idFor(string $slug): ?int
    {
        return static::where('slug', $slug)->value('id');
    }
}
