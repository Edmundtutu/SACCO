<?php

namespace Database\Seeders;

use App\Models\Membership\MfiProfile;
use App\Models\Membership\VslaProfile;
use App\Models\User;
use App\Models\Membership\Membership;
use App\Models\Membership\IndividualProfile;
use Illuminate\Database\Seeder;

/**
 * MemberOnlySeeder - Creates users, profiles, and memberships only
 * Does NOT create accounts, loans, shares, or transactions
 * Part of ordered seeding: foundation → members → accounts → transactions/loans
 */
class MemberOnlySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🔄 Creating staff users...');

        // Create staff users first (for processing transactions later)
        $staffUsers = User::factory()
            ->count(2)
            ->state(fn () =>[
                'role' => 'staff_level_3',
                'status' => 'active',
                'membership_date' => now()->subDays(rand(730, 1095)),
                'account_verified_at' => now()->subDays(rand(730, 1095)),
            ])
            ->create();

        $this->command->info("✅ Created {$staffUsers->count()} staff users");

        // Create member users with profiles and memberships
        $this->command->info('🔄 Creating members with profiles...');

        // Batch 1: 10 members with complete Individual profiles
        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) {
                $profile = IndividualProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => IndividualProfile::class,
                    'profile_id' => $profile->id,
                ]);
            });

        $this->command->info('✅ Created 10 members with full profiles');

        // Batch 2: 10 additional members who are VLSAs
        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) {
                $profile = VslaProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => VslaProfile::class,
                    'profile_id' => $profile->id,
                ]);
            });

        $this->command->info('✅ Created 10 additional members');

        // Batch 3: 5 members who are MFIs
        User::factory()
            ->count(5)
            ->create()
            ->each(function (User $user) {
                $profile = MfiProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => MfiProfile::class,
                    'profile_id' => $profile->id,
                ]);
            });

        $this->command->info('✅ Created 5 minimal members');

        $totalMembers = User::where('role', 'member')->count();
        $totalStaff = User::whereIn('role', ['staff_level_1', 'staff_level_2', 'staff_level_3'])->count();

        $this->command->info("✅ MemberOnlySeeder complete: {$totalMembers} members, {$totalStaff} staff");
    }
}

