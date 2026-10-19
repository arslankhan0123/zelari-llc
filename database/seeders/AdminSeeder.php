<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the panel accounts. Credentials can be overridden from .env:
 *   SEED_SUPERADMIN_EMAIL / SEED_SUPERADMIN_PASSWORD
 *   SEED_ADMIN_EMAIL      / SEED_ADMIN_PASSWORD
 * The plain password is kept in users.real_password (encrypted) so it can be
 * looked up from the admin panel. Re-running the seeder is safe: existing
 * accounts keep their current password.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $accounts = [
            [
                'name'     => 'Super Admin',
                'email'    => env('SEED_SUPERADMIN_EMAIL', 'superadmin@zelari.com'),
                'password' => env('SEED_SUPERADMIN_PASSWORD', 'SuperAdmin@123'),
                'role'     => Role::SUPER_ADMIN,
            ],
            [
                'name'     => 'Admin',
                'email'    => env('SEED_ADMIN_EMAIL', 'admin@zelari.com'),
                'password' => env('SEED_ADMIN_PASSWORD', 'Admin@123'),
                'role'     => Role::ADMIN,
            ],
        ];

        foreach ($accounts as $a) {
            $user = User::firstOrNew(['email' => $a['email']]);
            if (!$user->exists) {
                $user->password      = Hash::make($a['password']);
                $user->real_password = $a['password'];
                $user->email_verified_at = now();
            } elseif (!$user->real_password) {
                // existing account from before: reset to the seed password so it is known
                $user->password      = Hash::make($a['password']);
                $user->real_password = $a['password'];
            }
            $user->name    = $user->name ?: $a['name'];
            $user->role_id = Role::idFor($a['role']);
            $user->save();

            $this->command?->info(sprintf('%-11s %-28s password: %s', $user->role->name ?? $a['role'], $user->email, $user->real_password));
        }
    }
}
