<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'vendor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('role_id')->constrained('vendors')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('suppliers', 'vendor_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
            });
        }

        // Backfill: each vendor owner's users.vendor_id = their vendor
        foreach (DB::table('vendors')->whereNotNull('user_id')->get(['id', 'user_id']) as $v) {
            DB::table('users')->where('id', $v->user_id)->update(['vendor_id' => $v->id]);
        }

        $now = now();
        // Vendor role → manage their own staff
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            foreach (['staff.list', 'staff.view', 'staff.create', 'staff.delete'] as $perm) {
                if (!DB::table('role_permissions')->where('role_id', $vendorId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert(['role_id' => $vendorId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
        // Admin role → full staff access
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['staff.list', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete'] as $perm) {
                if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'vendor_id')) {
            Schema::table('suppliers', fn (Blueprint $t) => $t->dropConstrainedForeignId('vendor_id'));
        }
        if (Schema::hasColumn('users', 'vendor_id')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('vendor_id'));
        }
    }
};
