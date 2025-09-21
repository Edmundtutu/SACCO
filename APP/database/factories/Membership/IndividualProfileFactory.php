<?php

namespace Database\Factories\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\IndividualProfile>
 */
class IndividualProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'phone' => $this->faker->phoneNumber(),
            'national_id' => $this->faker->unique()->numerify('##########'),
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'occupation' => $this->faker->jobTitle(),
            'monthly_income' => $this->faker->randomFloat(2, 50000, 500000),
            'referee' => User::factory(),
            'next_of_kin_name' => $this->faker->name(),
            'next_of_kin_relationship' => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Child', 'Friend']),
            'next_of_kin_phone' => $this->faker->phoneNumber(),
            'next_of_kin_address' => $this->faker->address(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'employer_name' => $this->faker->company(),
            'bank_name' => $this->faker->randomElement(['Equity Bank', 'KCB Bank', 'Cooperative Bank', 'Absa Bank', 'Stanbic Bank']),
            'bank_account_number' => $this->faker->numerify('##########'),
            'profile_photo_path' => null,
            'id_copy_path' => null,
            'signature_path' => null,
            'additional_notes' => $this->faker->optional()->sentence(),
        ];
    }
}
