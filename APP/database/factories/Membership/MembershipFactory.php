<?php

namespace Database\Factories\Membership;

use App\Models\User;
use App\Models\Membership\IndividualProfile;
use App\Models\Membership\VslaProfile;
use App\Models\Membership\MfiProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $profileType = $this->faker->randomElement([
            IndividualProfile::class,
            VslaProfile::class,
            MfiProfile::class,
        ]);

        $profile = $profileType::factory()->create();

        return [
            'id' => $this->generateMembershipId($profileType),
            'user_id' => User::factory(),
            'profile_type' => $profileType,
            'profile_id' => $profile->id,
            'approval_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by_level_1' => null,
            'approved_at_level_1' => null,
            'approved_by_level_2' => null,
            'approved_at_level_2' => null,
            'approved_by_level_3' => null,
            'approved_at_level_3' => null,
        ];
    }

    /**
     * Generate membership ID based on profile type
     */
    private function generateMembershipId(string $profileType): string
    {
        $year = date('Y');
        
        $prefixMap = [
            IndividualProfile::class => 'M',
            VslaProfile::class => 'G',
            MfiProfile::class => 'S',
        ];

        $prefix = $prefixMap[$profileType] ?? 'M';
        $number = $this->faker->unique()->numberBetween(1, 9999);

        return sprintf('%s%s-%04d', $prefix, $year, $number);
    }

    /**
     * Create approved membership
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            $approver1 = User::factory()->create(['role' => 'staff_level_1']);
            $approver2 = User::factory()->create(['role' => 'staff_level_2']);
            
            return [
                'approval_status' => 'approved',
                'approved_by_level_1' => $approver1->id,
                'approved_at_level_1' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
                'approved_by_level_2' => $approver2->id,
                'approved_at_level_2' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            ];
        });
    }

    /**
     * Create pending membership
     */
    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_by_level_1' => null,
            'approved_at_level_1' => null,
            'approved_by_level_2' => null,
            'approved_at_level_2' => null,
            'approved_by_level_3' => null,
            'approved_at_level_3' => null,
        ]);
    }

    /**
     * Create rejected membership
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            $approver1 = User::factory()->create(['role' => 'staff_level_1']);
            
            return [
                'approval_status' => 'rejected',
                'approved_by_level_1' => $approver1->id,
                'approved_at_level_1' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
                'approved_by_level_2' => null,
                'approved_at_level_2' => null,
                'approved_by_level_3' => null,
                'approved_at_level_3' => null,
            ];
        });
    }
}
