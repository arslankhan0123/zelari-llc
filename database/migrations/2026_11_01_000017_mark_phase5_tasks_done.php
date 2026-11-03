<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('build_tasks')->where('phase', 5)->update(['status' => 'done', 'updated_at' => now()]);
        // Phase 4 leftover: multi-vendor cart & checkout (per-vendor shipping) now complete.
        DB::table('build_tasks')->where('phase', 4)->update(['status' => 'done', 'updated_at' => now()]);
    }

    public function down(): void {}
};
