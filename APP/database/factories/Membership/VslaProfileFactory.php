<?php

namespace Database\Factories\Membership;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\VslaProfile>
 */
class VslaProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'village' => $this->faker->city(),
            'sub_county' => $this->faker->citySuffix() . ' Sub County',
            'district' => $this->faker->state(),
            'membership_count' => $this->faker->numberBetween(10, 50),
            'registration_certificate' => $this->faker->url(),
            'constitution_copy' => $this->faker->url(),
            'resolution_minutes' => $this->faker->url(),
            'executive_contacts' => [
                'chairperson' => [
                    'name' => $this->faker->name(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
                'secretary' => [
                    'name' => $this->faker->name(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
                'treasurer' => [
                    'name' => $this->faker->name(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
            ],
            'recommendation_lc1' => $this->faker->url(),
            'recommendation_cdo' => $this->faker->url(),
        ];
    }
}
