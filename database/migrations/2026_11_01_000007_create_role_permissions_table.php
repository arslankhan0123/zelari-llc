<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('permission', 60);
            $table->timestamps();
            $table->unique(['role_id', 'permission']);
        });

        // Mark built-in roles so they can't be deleted from the UI
        if (!Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('description');
            });
            DB::table('roles')->whereIn('slug', ['master_admin', 'admin', 'user', 'vendor', 'vendor_staff'])
                ->update(['is_system' => true]);
        }

        // Give the 'admin' role a broad default permission set (everything except users/settings management)
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            $perms = [];
            foreach (config('permissions') as $mod => $def) {
                if (in_array($mod, ['users'])) continue; // admin can't manage users/roles by default
                foreach ($def['actions'] as $act) {
                    $perms[] = ['role_id' => $adminId, 'permission' => "$mod.$act", 'created_at' => now(), 'updated_at' => now()];
                }
            }
            DB::table('role_permissions')->insertOrIgnore($perms);
        }

        // Vendor role: scoped to own products + dashboard
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            foreach (['dashboard.view', 'products.view', 'products.create', 'products.edit', 'products.delete'] as $p) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $vendorId, 'permission' => $p, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        if (Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', fn (Blueprint $t) => $t->dropColumn('is_system'));
        }
    }
};
