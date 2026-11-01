<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrmRole extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_system'];
    protected $casts = ['is_system' => 'boolean'];

    private ?array $permCache = null;

    public function permissionStrings(): array
    {
        if ($this->permCache === null) {
            $this->permCache = \Illuminate\Support\Facades\DB::table('hrm_role_permissions')
                ->where('hrm_role_id', $this->id)->pluck('permission')->all();
        }
        return $this->permCache;
    }

    public function hasPermission(string $perm): bool
    {
        return in_array($perm, $this->permissionStrings(), true);
    }

    public function syncPermissions(array $perms): void
    {
        $db = \Illuminate\Support\Facades\DB::table('hrm_role_permissions');
        $db->where('hrm_role_id', $this->id)->delete();
        $rows = collect($perms)->filter()->unique()->map(fn ($p) => [
            'hrm_role_id' => $this->id, 'permission' => $p, 'created_at' => now(), 'updated_at' => now(),
        ])->values()->all();
        if ($rows) \Illuminate\Support\Facades\DB::table('hrm_role_permissions')->insert($rows);
        $this->permCache = null;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'hrm_role_id');
    }

    public static function idFor(string $slug): ?int
    {
        return static::where('slug', $slug)->value('id');
    }
}
