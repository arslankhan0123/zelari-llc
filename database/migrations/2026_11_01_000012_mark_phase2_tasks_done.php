<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 2 — Vendor Catalog & Orders is now fully implemented.
        DB::table('build_tasks')->where('phase', 2)->update(['status' => 'done', 'updated_at' => now()]);

        // Phase 2.5 enforcement task (per-action RBAC via middleware) is done too, if present.
        DB::table('build_tasks')->where('phase', 2.5)
            ->where('title', 'like', '%enforce%')
            ->update(['status' => 'done', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // no-op: status is user-managed from the dashboard afterwards
    }
};
