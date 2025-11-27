<?php

namespace Database\Factories;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsGoal>
 */
class SavingsGoalFactory extends Factory
{
    protected $model = SavingsGoal::class;

    public function definition(): array
    {
        $targetAmount = $this->faker->numberBetween(200_000, 5_000_000);

        return [
            'member_id' => User::factory(),
            'savings_account_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'target_amount' => $targetAmount,
            'current_amount' => $this->faker->numberBetween(0, (int) ($targetAmount * 0.7)),
            'target_date' => $this->faker->optional()->dateTimeBetween('+2 months', '+1 year'),
            'status' => $this->faker->randomElement(SavingsGoal::STATUSES),
            'auto_nudge' => true,
            'nudge_frequency' => SavingsGoal::NUDGE_WEEKLY,
            'metadata' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => SavingsGoal::STATUS_ACTIVE,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => SavingsGoal::STATUS_COMPLETED,
            'achieved_at' => now(),
            'current_amount' => fn (array $attributes) => $attributes['target_amount'],
        ]);
    }
}
