<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['member', 'staff_level_1', 'staff_level_2', 'staff_level_3']),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended', 'pending_approval']),
            'membership_date' => fake()->optional()->date(),
            'account_verified_at' => fake()->optional()->dateTime(),
        ];
    }

    /**
     * Indicate that the user's account is not yet verified.
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'account_verified_at' => null,
        ]);
    }
}
