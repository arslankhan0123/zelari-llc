<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();      // SuperAdmin, Admin, User
            $table->string('slug', 50)->unique();      // super_admin, admin, user
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->nullOnDelete();
            // Seeded / admin-created account passwords, stored ENCRYPTED (Laravel Crypt), so the owner
            // can look them up in the admin panel. Never filled from customer self-registration.
            $table->text('real_password')->nullable()->after('role_id');
        });

        // Existing accounts were all admins of this panel until now
        $now = now();
        DB::table('roles')->insert([
            ['name' => 'SuperAdmin', 'slug' => 'super_admin', 'description' => 'Full access, including managing other admins', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Admin',      'slug' => 'admin',       'description' => 'Manages catalog, orders, customers, payments and settings', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'User',       'slug' => 'user',        'description' => 'Storefront customer account — no admin access', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        DB::table('users')->whereNull('role_id')->update(['role_id' => $adminId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('real_password');
        });
        Schema::dropIfExists('roles');
    }
};
