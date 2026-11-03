<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'super_admin')->update([
            'slug'        => 'master_admin',
            'name'        => 'Master Admin',
            'description' => 'Full platform access, including managing vendors and other admins',
            'updated_at'  => now(),
        ]);

        // Freshen the seeded account's display name for consistency
        DB::table('users')->where('name', 'Super Admin')->update(['name' => 'Master Admin']);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'master_admin')->update([
            'slug' => 'super_admin',
            'name' => 'SuperAdmin',
        ]);
        DB::table('users')->where('name', 'Master Admin')->update(['name' => 'Super Admin']);
    }
};
