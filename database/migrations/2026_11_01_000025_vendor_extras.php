<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $t) {
            foreach ([
                'return_policy' => 'text', 'shipping_policy' => 'text',
                'payout_method' => 'string', 'bank_name' => 'string',
                'account_name' => 'string', 'account_number' => 'string',
            ] as $col => $type) {
                if (!Schema::hasColumn('vendors', $col)) {
                    $type === 'text' ? $t->text($col)->nullable() : $t->string($col)->nullable();
                }
            }
        });

        if (!Schema::hasColumn('coupons', 'vendor_id')) {
            Schema::table('coupons', function (Blueprint $t) {
                $t->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('vendor_reviews')) {
            Schema::create('vendor_reviews', function (Blueprint $t) {
                $t->id();
                $t->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $t->string('author_name');
                $t->string('author_email')->nullable();
                $t->unsignedTinyInteger('rating')->default(5);
                $t->text('body')->nullable();
                $t->string('status')->default('approved'); // approved|pending
                $t->timestamps();
                $t->index(['vendor_id', 'status']);
            });
        }

        $now = now();
        // Vendor role → own store settings + own coupons (not toggle/wheel = no edit)
        $vendorId = DB::table('roles')->where('slug', 'vendor')->value('id');
        if ($vendorId) {
            foreach (['store.list', 'store.view', 'store.edit', 'coupons.list', 'coupons.create', 'coupons.delete'] as $perm) {
                if (!DB::table('role_permissions')->where('role_id', $vendorId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert(['role_id' => $vendorId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
        // Admin role → store.*
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                $perm = "store.$act";
                if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
        if (Schema::hasColumn('coupons', 'vendor_id')) {
            Schema::table('coupons', fn (Blueprint $t) => $t->dropConstrainedForeignId('vendor_id'));
        }
        foreach (['return_policy', 'shipping_policy', 'payout_method', 'bank_name', 'account_name', 'account_number'] as $c) {
            if (Schema::hasColumn('vendors', $c)) Schema::table('vendors', fn (Blueprint $t) => $t->dropColumn($c));
        }
    }
};
