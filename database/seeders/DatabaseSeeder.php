<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *   php artisan db:seed                      → roles + SuperAdmin + Admin
     *   php artisan db:seed --class=AdminSeeder  → same, on its own
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
