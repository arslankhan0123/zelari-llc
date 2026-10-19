<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Currency the customer saw at checkout (amounts stay stored in the base currency)
            $table->string('currency_code', 3)->default('PKR')->after('total');
            $table->string('currency_symbol', 8)->default('Rs')->after('currency_code');
            $table->decimal('exchange_rate', 14, 6)->default(1)->after('currency_symbol');

            // IP-derived approximate location, kept next to the typed shipping address
            $table->string('ip_address', 45)->nullable()->after('notes');
            $table->string('ip_country_code', 2)->nullable()->after('ip_address');
            $table->string('ip_country', 100)->nullable()->after('ip_country_code');
            $table->string('ip_region', 100)->nullable()->after('ip_country');
            $table->string('ip_city', 100)->nullable()->after('ip_region');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code', 'currency_symbol', 'exchange_rate',
                'ip_address', 'ip_country_code', 'ip_country', 'ip_region', 'ip_city',
            ]);
        });
    }
};
