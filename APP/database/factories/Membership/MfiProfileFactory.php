<?php

namespace Database\Factories\Membership;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\MfiProfile>
 */
class MfiProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'contact_person' => $this->faker->name(),
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'membership_count' => $this->faker->numberBetween(50, 200),
            'registration_certificate' => $this->faker->optional()->url(),
            'board_members' => [
                'chairperson' => [
                    'name' => $this->faker->name(),
                    'position' => 'Chairperson',
                    'phone' => $this->faker->phoneNumber(),
                ],
                'vice_chairperson' => [
                    'name' => $this->faker->name(),
                    'position' => 'Vice Chairperson',
                    'phone' => $this->faker->phoneNumber(),
                ],
                'secretary' => [
                    'name' => $this->faker->name(),
                    'position' => 'Secretary',
                    'phone' => $this->faker->phoneNumber(),
                ],
                'treasurer' => [
                    'name' => $this->faker->name(),
                    'position' => 'Treasurer',
                    'phone' => $this->faker->phoneNumber(),
                ],
            ],
            'bylaws_copy' => $this->faker->optional()->url(),
            'resolution_minutes' => $this->faker->optional()->url(),
            'operating_license' => $this->faker->optional()->url(),
        ];
    }
}
