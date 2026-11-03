<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // All permission strings from config (list/view/create/edit/delete per module)
        $all = [];
        foreach (config('permissions') as $mod => $def) {
            foreach ($def['actions'] as $act) $all[] = "$mod.$act";
        }

        // Admin role → everything
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            DB::table('role_permissions')->where('role_id', $adminId)->delete();
            DB::table('role_permissions')->insert(array_map(fn ($p) => [
                'role_id' => $adminId, 'permission' => $p, 'created_at' => $now, 'updated_at' => $now,
            ], $all));
        }

        // Vendor role → own dashboard + full product CRUD
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            DB::table('role_permissions')->where('role_id', $vendorId)->delete();
            $vperms = ['dashboard.list', 'dashboard.view',
                       'products.list', 'products.view', 'products.create', 'products.edit', 'products.delete'];
            DB::table('role_permissions')->insert(array_map(fn ($p) => [
                'role_id' => $vendorId, 'permission' => $p, 'created_at' => $now, 'updated_at' => $now,
            ], $vperms));
        }
    }

    public function down(): void {}
};
