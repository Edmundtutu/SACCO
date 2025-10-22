<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
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
        $status = $this->faker->randomElement(['active', 'inactive', 'dormant', 'closed']);

        // By default, create a savings account (most common type)
        $savingsAccount = SavingsAccount::factory()->create();

        return [
            'account_number' => 'ACC' . str_pad((string)$this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'member_id' => User::factory(),
            'accountable_type' => SavingsAccount::class,
            'accountable_id' => $savingsAccount->id,
            'status' => $status,
            'closure_reason' => $status === 'closed' ? $this->faker->sentence() : null,
            'closed_at' => $status === 'closed' ? now() : null,
            'closed_by' => $status === 'closed' ? User::factory() : null,
        ];
    }

    /**
     * Create account with a savings account
     */
    public function withSavingsAccount(?SavingsAccount $savingsAccount = null): static
    {
        return $this->state(function (array $attributes) use ($savingsAccount) {
            $savings = $savingsAccount ?? SavingsAccount::factory()->create();
            
            return [
                'accountable_type' => SavingsAccount::class,
                'accountable_id' => $savings->id,
            ];
        });
    }

    /**
     * Create account with a loan account
     */
    public function withLoanAccount(?LoanAccount $loanAccount = null): static
    {
        return $this->state(function (array $attributes) use ($loanAccount) {
            $loan = $loanAccount ?? LoanAccount::factory()->create();
            
            return [
                'accountable_type' => LoanAccount::class,
                'accountable_id' => $loan->id,
            ];
        });
    }

    /**
     * Create account with a share account
     */
    public function withShareAccount(?ShareAccount $shareAccount = null): static
    {
        return $this->state(function (array $attributes) use ($shareAccount) {
            $share = $shareAccount ?? ShareAccount::factory()->create();
            
            return [
                'accountable_type' => ShareAccount::class,
                'accountable_id' => $share->id,
            ];
        });
    }
}
