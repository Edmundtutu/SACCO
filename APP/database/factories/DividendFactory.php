<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dividend>
 */
class DividendFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $year = $this->faker->numberBetween(2020, 2024);
        $dividendRate = $this->faker->randomFloat(4, 0.05, 0.15); // 5% to 15%
        $totalAmount = $this->faker->randomFloat(2, 100000, 1000000);

        return [
            'dividend_period' => $year,
            'dividend_rate' => $dividendRate,
            'total_dividend_amount' => $totalAmount,
            'declaration_date' => $this->faker->dateTimeBetween("$year-01-01", "$year-12-31"),
            'payment_date' => $this->faker->dateTimeBetween("$year-01-01", "$year-12-31"),
            'status' => $this->faker->randomElement(['declared', 'paid']),
            'notes' => $this->faker->optional()->sentence(),
            'declared_by' => User::factory(),
        ];
    }

    /**
     * Create declared dividend
     */
    public function declared()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'declared',
        ]);
    }

    /**
     * Create paid dividend
     */
    public function paid()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}
