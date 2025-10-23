<?php

namespace Database\Factories;

use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsAccountFactory extends Factory
{
    protected $model = SavingsAccount::class;

    public function definition(): array
    {
        $balance = $this->faker->randomFloat(2, 1000, 500000);
        
        return [
            'savings_product_id' => SavingsProduct::factory(),
            'balance' => $balance,
            'available_balance' => $balance,
            'minimum_balance' => $this->faker->randomFloat(2, 0, 5000),
            'interest_earned' => $this->faker->randomFloat(2, 0, 50000),
            'interest_rate' => $this->faker->randomFloat(2, 5, 15),
            'last_interest_calculation' => $this->faker->optional()->dateTimeBetween('-1 year'),
            'maturity_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+2 years'),
        ];
    }

    public function wallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'savings_product_id' => SavingsProduct::factory()->wallet(),
        ]);
    }
}
