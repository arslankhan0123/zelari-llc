<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->enum('type', ['percent', 'fixed', 'freeship'])->default('percent');
            $table->decimal('value', 10, 2)->default(0);          // percent (e.g. 10) or fixed USD amount
            $table->decimal('min_spend', 10, 2)->default(0);       // minimum order subtotal in USD
            $table->decimal('max_discount', 10, 2)->nullable();    // cap for percent coupons (USD)
            $table->unsignedInteger('usage_limit')->nullable();    // total redemptions allowed (null = unlimited)
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->enum('source', ['manual', 'wheel'])->default('manual');
            $table->string('email')->nullable();                   // wheel coupons are tied to the winner's email
            $table->timestamps();
            $table->index(['active', 'source']);
        });

        Schema::create('wheel_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('prize_label');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_entries');
        Schema::dropIfExists('coupons');
    }
};
