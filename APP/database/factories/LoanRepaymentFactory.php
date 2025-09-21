<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRepayment>
 */
class LoanRepaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $scheduledAmount = $this->faker->randomFloat(2, 1000, 10000);
        $principalAmount = $this->faker->randomFloat(2, 500, $scheduledAmount * 0.8);
        $interestAmount = $this->faker->randomFloat(2, 100, $scheduledAmount * 0.3);
        $penaltyAmount = $this->faker->randomFloat(2, 0, $scheduledAmount * 0.1);
        $totalAmount = $principalAmount + $interestAmount + $penaltyAmount;

        $dueDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $paymentDate = $this->faker->optional(0.8)->dateTimeBetween($dueDate, 'now');
        $daysLate = $paymentDate ? max(0, $dueDate->diff($paymentDate)->days) : 0;

        return [
            'loan_id' => Loan::factory(),
            'receipt_number' => 'RCP' . now()->format('Ymd') . str_pad(
                $this->faker->unique()->numberBetween(1, 9999),
                4,
                '0',
                STR_PAD_LEFT
            ),
            'installment_number' => $this->faker->numberBetween(1, 36),
            'due_date' => $dueDate,
            'payment_date' => $paymentDate,
            'scheduled_amount' => $scheduledAmount,
            'principal_amount' => $principalAmount,
            'interest_amount' => $interestAmount,
            'penalty_amount' => $penaltyAmount,
            'total_amount' => $totalAmount,
            'balance_after_payment' => $this->faker->randomFloat(2, 0, 50000),
            'days_late' => $daysLate,
            'status' => $this->faker->randomElement(['pending', 'paid', 'partial', 'overdue', 'waived']),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mobile_money', 'check', 'deduction']),
            'payment_reference' => $this->faker->optional()->uuid(),
            'notes' => $this->faker->optional()->sentence(),
            'collected_by' => User::factory(),
            'approved_by' => $this->faker->optional()->randomElement([User::factory()]),
        ];
    }

    /**
     * Create paid repayment
     */
    public function paid()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Create pending repayment
     */
    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create overdue repayment
     */
    public function overdue()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'payment_date' => null,
            'days_late' => $this->faker->numberBetween(1, 90),
        ]);
    }
}
