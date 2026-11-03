<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Master Admin', 'slug' => Role::MASTER_ADMIN, 'description' => 'Full access, including managing other admins'],
            ['name' => 'Admin',      'slug' => Role::ADMIN,       'description' => 'Manages catalog, orders, customers, payments and settings'],
            ['name' => 'User',       'slug' => Role::USER,        'description' => 'Storefront customer account — no admin access'],
        ] as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
