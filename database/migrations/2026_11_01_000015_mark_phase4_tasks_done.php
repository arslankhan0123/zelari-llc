<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 4 storefront/UX done, except the per-vendor shipping rules that
        // live in Phase 5 (Shipping & Tax Engines) — leave the cart/shipping item there.
        DB::table('build_tasks')->where('phase', 4)
            ->where('title', 'not like', '%per-vendor shipping%')
            ->where('title', 'not like', '%Multi-vendor cart%')
            ->update(['status' => 'done', 'updated_at' => now()]);
    }

    public function down(): void {}
};
