<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        DB::table('build_tasks')->where('phase', 6)->update(['status' => 'done', 'updated_at' => now()]);
    }
    public function down(): void {}
};
