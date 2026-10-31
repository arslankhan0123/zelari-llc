<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('build_tasks')) return;

        $maxSort = (int) DB::table('build_tasks')->max('sort');
        $phaseTitle = 'Phase 2.5 — Roles & Permissions (RBAC)';
        $tasks = [
            ['Dynamic roles + permissions data model', 'done'],
            ['Roles management UI (create / edit / delete)', 'done'],
            ['Per-module, per-action permission matrix', 'done'],
            ['Create user & assign a role', 'done'],
            ['Seeder creates only Master Admin', 'done'],
            ['Enforce permissions across menu, routes & actions', 'todo'],
        ];
        $rows = [];
        foreach ($tasks as $i => [$title, $status]) {
            $rows[] = [
                'phase' => 25, 'phase_title' => $phaseTitle, 'title' => $title,
                'status' => $status, 'sort' => $maxSort + 1 + $i,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        DB::table('build_tasks')->insert($rows);
    }

    public function down(): void
    {
        DB::table('build_tasks')->where('phase', 25)->delete();
    }
};
