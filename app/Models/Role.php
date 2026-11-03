<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const MASTER_ADMIN = 'master_admin';
    public const ADMIN       = 'admin';
    public const USER        = 'user';
    public const VENDOR       = 'vendor';
    public const VENDOR_STAFF = 'vendor_staff';

    protected $fillable = ['name', 'slug', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    private ?array $permCache = null;

    /** All permission strings granted to this role. */
    public function permissionStrings(): array
    {
        if ($this->permCache === null) {
            $this->permCache = \Illuminate\Support\Facades\DB::table('role_permissions')
                ->where('role_id', $this->id)->pluck('permission')->all();
        }
        return $this->permCache;
    }

    public function hasPermission(string $perm): bool
    {
        return in_array($perm, $this->permissionStrings(), true);
    }

    public function syncPermissions(array $perms): void
    {
        $db = \Illuminate\Support\Facades\DB::table('role_permissions');
        $db->where('role_id', $this->id)->delete();
        $rows = collect($perms)->filter()->unique()->map(fn ($p) => [
            'role_id' => $this->id, 'permission' => $p, 'created_at' => now(), 'updated_at' => now(),
        ])->values()->all();
        if ($rows) \Illuminate\Support\Facades\DB::table('role_permissions')->insert($rows);
        $this->permCache = null;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idFor(string $slug): ?int
    {
        return static::where('slug', $slug)->value('id');
    }
}
