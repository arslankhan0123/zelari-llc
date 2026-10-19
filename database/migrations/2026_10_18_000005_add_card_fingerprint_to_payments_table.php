<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Stripe's stable per-card identifier: same physical card => same fingerprint,
            // even when two different cards share the same last 4 digits.
            $table->string('card_fingerprint', 64)->nullable()->index()->after('card_last4');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('card_fingerprint');
        });
    }
};
