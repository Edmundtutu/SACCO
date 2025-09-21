<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Dividend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DividendPayment>
 */
class DividendPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $sharesCount = $this->faker->numberBetween(10, 1000);
        $dividendAmount = $this->faker->randomFloat(2, 100, 10000);

        return [
            'dividend_id' => Dividend::factory(),
            'member_id' => User::factory(),
            'shares_count' => $sharesCount,
            'dividend_amount' => $dividendAmount,
            'payment_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mobile_money', 'check']),
            'payment_reference' => $this->faker->optional()->uuid(),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'processed_by' => User::factory(),
        ];
    }

    /**
     * Create paid dividend payment
     */
    public function paid()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Create pending dividend payment
     */
    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
