<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // orders.payment_method is a MySQL ENUM — add 'paypal' to it
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash_on_delivery','bank_transfer','card','paypal','other') NOT NULL DEFAULT 'cash_on_delivery'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('orders')->where('payment_method', 'paypal')->update(['payment_method' => 'other']);
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash_on_delivery','bank_transfer','card','other') NOT NULL DEFAULT 'cash_on_delivery'");
        }
    }
};
