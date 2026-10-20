<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->unsignedTinyInteger('rating');            // 1–5
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('verified_purchase')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('admin_note')->nullable();          // reason shown to staff only
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });

        // Cached aggregates so product listings need no extra joins
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('reviews_count')->default(0)->after('status');
            $table->decimal('reviews_avg', 3, 2)->nullable()->after('reviews_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reviews_count', 'reviews_avg']);
        });
        Schema::dropIfExists('reviews');
    }
};
