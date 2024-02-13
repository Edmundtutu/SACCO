<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $status = $this->faker->randomElement(['Active', 'Inactive']);
        return [
            'accountno' => $this->faker->bankAccountNumber(),
            'type' => $this->faker->randomElement(['Savings', 'checkings']),
            'status' => $status,
            'amount' => $this->faker->numberBetween($min=400, $max = 50000),
            'netamount' => $status == 'Active' ? $this->faker->numberBetween(400,50000) : NULL,
            'member_id' => Member::factory()
        ];
    }
}
