<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if (!$vendorId) {
            return;
        }

        // Vendors manage their own sub-orders: see the list, view details, update fulfilment status.
        $perms = ['orders.list', 'orders.view', 'orders.edit'];

        foreach ($perms as $perm) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $vendorId)->where('permission', $perm)->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id'    => $vendorId,
                    'permission' => $perm,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            DB::table('role_permissions')->where('role_id', $vendorId)
                ->whereIn('permission', ['orders.list', 'orders.view', 'orders.edit'])->delete();
        }
    }
};
