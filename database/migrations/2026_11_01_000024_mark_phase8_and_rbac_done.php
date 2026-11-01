<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        DB::table('build_tasks')->where('phase', 8)->update(['status' => 'done', 'updated_at' => now()]);
        // Phase 2.5 final task: enforcement across menu, routes & actions
        DB::table('build_tasks')->where('phase', 2.5)
            ->where('title', 'like', '%nforce%')
            ->update(['status' => 'done', 'updated_at' => now()]);
    }
    public function down(): void {}
};
