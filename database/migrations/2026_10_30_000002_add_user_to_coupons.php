<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
        });
        Schema::table('wheel_entries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wheel_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
