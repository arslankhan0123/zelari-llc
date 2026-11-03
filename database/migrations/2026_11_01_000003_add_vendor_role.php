<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            ['name' => 'Vendor',       'slug' => 'vendor',       'description' => 'Marketplace seller — manages own products, orders and earnings'],
            ['name' => 'Vendor Staff', 'slug' => 'vendor_staff', 'description' => 'Staff member of a vendor with limited vendor-panel access'],
        ] as $r) {
            $exists = DB::table('roles')->where('slug', $r['slug'])->exists();
            if (!$exists) {
                DB::table('roles')->insert($r + ['created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('slug', ['vendor', 'vendor_staff'])->delete();
    }
};
