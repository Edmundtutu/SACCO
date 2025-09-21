<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Share>
 */
class ShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $sharesCount = $this->faker->numberBetween(1, 100);
        $shareValue = $this->faker->randomFloat(2, 100, 1000);
        $totalValue = $sharesCount * $shareValue;

        $status = $this->faker->randomElement(['active', 'transferred', 'redeemed']);
        
        return [
            'member_id' => User::factory(),
            'certificate_number' => 'SHR' . now()->format('Y') . str_pad(
                $this->faker->unique()->numberBetween(1, 999999),
                6,
                '0',
                STR_PAD_LEFT
            ),
            'shares_count' => $sharesCount,
            'share_value' => $shareValue,
            'total_value' => $totalValue,
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'status' => $status,
            'transfer_details' => $status === 'transferred' ? $this->faker->sentence() : null,
            'transfer_date' => $status === 'transferred' ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'transferred_to' => $status === 'transferred' ? User::factory() : null,
            'redemption_date' => $status === 'redeemed' ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'redemption_value' => $status === 'redeemed' ? $this->faker->randomFloat(2, 100, 10000) : null,
            'redemption_reason' => $status === 'redeemed' ? $this->faker->sentence() : null,
            'processed_by' => User::factory(),
        ];
    }

    /**
     * Create active shares
     */
    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create transferred shares
     */
    public function transferred()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'transferred',
            'transfer_details' => $this->faker->sentence(),
            'transfer_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'transferred_to' => User::factory(),
            'redemption_date' => null,
            'redemption_value' => null,
            'redemption_reason' => null,
        ]);
    }

    /**
     * Create redeemed shares
     */
    public function redeemed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'redeemed',
            'redemption_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'redemption_value' => $this->faker->randomFloat(2, 100, 10000),
            'redemption_reason' => $this->faker->sentence(),
            'transfer_details' => null,
            'transfer_date' => null,
            'transferred_to' => null,
        ]);
    }
}
