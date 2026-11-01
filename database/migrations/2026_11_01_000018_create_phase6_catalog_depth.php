<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('physical')->after('status'); // physical|digital|downloadable|bundled
            }
            if (!Schema::hasColumn('products', 'variant_attributes')) {
                $table->json('variant_attributes')->nullable()->after('type');   // {"Size":["S","M"],"Color":["Red"]}
            }
            if (!Schema::hasColumn('products', 'download_file')) {
                $table->string('download_file')->nullable()->after('variant_attributes'); // digital/downloadable delivery
            }
        });

        if (!Schema::hasTable('product_variations')) {
            Schema::create('product_variations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('sku')->nullable();
                $table->json('options');                       // {"Size":"M","Color":"Red"}
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->unsignedInteger('quantity')->default(0);
                $table->string('image')->nullable();
                $table->boolean('enabled')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bundle_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_variations');
        foreach (['type', 'variant_attributes', 'download_file'] as $c) {
            if (Schema::hasColumn('products', $c)) {
                Schema::table('products', fn (Blueprint $t) => $t->dropColumn($c));
            }
        }
    }
};
