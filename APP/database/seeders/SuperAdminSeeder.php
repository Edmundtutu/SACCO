<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed a platform-level super admin account for tenant management flows.
     */
    public function run(): void
    {
        $email = 'super.admin@sacco.test';
        $password = 'SuperAdmin@123';

        $superAdmin = User::withoutGlobalScopes()
            ->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Platform Super Admin',
                    'role' => 'super_admin',
                    'is_super_admin' => true,
                    'status' => 'active',
                    'password' => Hash::make($password),
                    'membership_date' => now(),
                    'account_verified_at' => now(),
                    'remember_token' => Str::random(40),
                ]
            );

        if ($this->command) {
            $this->command->warn('--------------------------------------------------');
            $this->command->info('Super admin account ready for use.');
            $this->command->line("Email: {$email}");
            $this->command->line("Password: {$password}");
            $this->command->warn('Remember to change this password in production environments.');
            $this->command->warn('--------------------------------------------------');
        }
    }
}
