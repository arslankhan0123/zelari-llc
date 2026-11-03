<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipping_zones')) {
            Schema::create('shipping_zones', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('countries')->nullable();   // ISO-2 codes; null/empty with is_rest = catch-all
                $table->boolean('is_rest')->default(false); // "rest of the world" fallback zone
                $table->boolean('enabled')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipping_methods')) {
            Schema::create('shipping_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('zone_id')->constrained('shipping_zones')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('rate', 10, 2)->default(0);        // base currency
                $table->decimal('free_over', 10, 2)->nullable();   // free when subtotal >= this
                $table->boolean('enabled')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('vendors', 'shipping_flat')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->decimal('shipping_flat', 10, 2)->nullable()->after('commission_rate');
                $table->decimal('shipping_free_over', 10, 2)->nullable()->after('shipping_flat');
            });
        }

        if (!Schema::hasColumn('orders', 'tax')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('tax', 12, 2)->default(0)->after('shipping_fee');
            });
        }

        if (!Schema::hasColumn('categories', 'tax_exempt')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('tax_exempt')->default(false)->after('commission_rate');
            });
        }

        // Tax defaults (off until configured → current behaviour preserved)
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            foreach (['tax_enabled' => '0', 'tax_rate' => '0', 'tax_inclusive' => '0', 'tax_label' => 'Tax'] as $k => $v) {
                if (!DB::table('settings')->where('key', $k)->exists()) {
                    DB::table('settings')->insert(['key' => $k, 'value' => $v, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        // Admin role → full access to the new modules
        $now = now();
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['shipping', 'tax'] as $mod) {
                foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                    $perm = "$mod.$act";
                    if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
        foreach ([['vendors', 'shipping_flat'], ['vendors', 'shipping_free_over'], ['orders', 'tax'], ['categories', 'tax_exempt']] as [$t, $c]) {
            if (Schema::hasColumn($t, $c)) {
                Schema::table($t, fn (Blueprint $table) => $table->dropColumn($c));
            }
        }
    }
};
