<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanGuarantor>
 */
class LoanGuarantorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $status = $this->faker->randomElement(['pending', 'accepted', 'rejected']);
        
        return [
            'loan_id' => Loan::factory(),
            'guarantor_id' => User::factory(),
            'guaranteed_amount' => $this->faker->randomFloat(2, 5000, 50000),
            'guarantor_savings_at_time' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $status,
            'guarantee_date' => $status === 'accepted' ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'guarantee_terms' => $this->faker->optional()->sentence(),
            'claimed_amount' => 0,
            'claim_date' => null,
            'rejection_reason' => $status === 'rejected' ? $this->faker->sentence() : null,
            'release_reason' => null,
            'release_date' => null,
            'processed_by' => User::factory(),
        ];
    }

    /**
     * Create accepted guarantor
     */
    public function accepted()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'guarantee_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Create pending guarantor
     */
    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'guarantee_date' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Create rejected guarantor
     */
    public function rejected()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'guarantee_date' => null,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
