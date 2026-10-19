<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        // Backfill: link existing orders to the account that shares the same email.
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('UPDATE orders o JOIN users u ON LOWER(u.email) = LOWER(o.customer_email) SET o.user_id = u.id WHERE o.user_id IS NULL AND o.customer_email IS NOT NULL');
        } else {
            DB::statement('UPDATE orders SET user_id = (SELECT id FROM users WHERE LOWER(users.email) = LOWER(orders.customer_email) LIMIT 1) WHERE user_id IS NULL AND customer_email IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
