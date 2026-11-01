<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-category commission override (null = fall back to the vendor's rate / global default)
        if (!Schema::hasColumn('categories', 'commission_rate')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->nullable()->after('status');
            });
        }

        // Vendor payout requests
        if (!Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('status')->default('pending'); // pending|approved|paid|rejected
                $table->string('method')->nullable();          // bank, jazzcash, etc.
                $table->string('reference')->nullable();        // payout txn reference
                $table->text('note')->nullable();               // vendor note
                $table->text('admin_note')->nullable();         // admin note
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'status']);
            });
        }

        // Global default commission rate (used when neither category nor vendor overrides)
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $exists = DB::table('settings')->where('key', 'commission_default_rate')->exists();
            if (!$exists) {
                DB::table('settings')->insert([
                    'key'        => 'commission_default_rate',
                    'value'      => '10',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $now = now();

        // Admin role carries explicit perms → add the new modules so it keeps full access.
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            $adminPerms = [];
            foreach (['commissions', 'earnings', 'payouts'] as $mod) {
                foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                    $adminPerms[] = "$mod.$act";
                }
            }
            foreach ($adminPerms as $perm) {
                if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }

        // Vendor role → read their earnings, request payouts.
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            $vendorPerms = ['earnings.list', 'earnings.view', 'payouts.list', 'payouts.view', 'payouts.create'];
            foreach ($vendorPerms as $perm) {
                if (!DB::table('role_permissions')->where('role_id', $vendorId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $vendorId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
        if (Schema::hasColumn('categories', 'commission_rate')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('commission_rate');
            });
        }
    }
};
