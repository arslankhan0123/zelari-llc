<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('id')
                      ->constrained('vendors')->nullOnDelete();
            });
        }

        // Default "house" vendor so existing products stay valid
        $ownerId = DB::table('users')->orderBy('id')->value('id');
        $houseId = DB::table('vendors')->where('slug', 'zelari')->value('id');
        if (!$houseId) {
            $houseId = DB::table('vendors')->insertGetId([
                'user_id'         => $ownerId,
                'name'            => 'Zelari',
                'slug'            => 'zelari',
                'email'           => DB::table('users')->orderBy('id')->value('email'),
                'description'     => 'Official Zelari store.',
                'status'          => 'approved',
                'commission_rate' => 0.00,
                'approved_at'     => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        DB::table('products')->whereNull('vendor_id')->update(['vendor_id' => $houseId]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vendor_id');
            });
        }
    }
};
