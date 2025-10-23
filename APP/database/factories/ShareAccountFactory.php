<?php

namespace Database\Factories;

use App\Models\ShareAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ShareAccount Factory - Creates account-level tracking
 * (NOT individual share certificates - those are in Share model)
 */
class ShareAccountFactory extends Factory
{
    protected $model = ShareAccount::class;

    public function definition(): array
    {
        // Account-level aggregate data
        $shareUnits = $this->faker->numberBetween(10, 500);
        $sharePrice = 1000; // Current share price (UGX 1,000 per unit)
        $totalValue = $shareUnits * $sharePrice;
        
        // Dividends tracking
        $dividendsEarned = $this->faker->randomFloat(2, 0, $shareUnits * 100); // Up to 100 per share
        $dividendsPaid = $dividendsEarned * $this->faker->randomFloat(1, 0.5, 0.9); // 50-90% paid
        $dividendsPending = $dividendsEarned - $dividendsPaid;
        
        // Bonus shares (0-10% of total)
        $bonusShares = $this->faker->numberBetween(0, (int)($shareUnits * 0.1));
        
        return [
            // Share ownership tracking (totals from all certificates)
            'share_units' => $shareUnits,
            'share_price' => $sharePrice,
            'total_share_value' => $totalValue,
            
            // Dividends
            'dividends_earned' => $dividendsEarned,
            'dividends_pending' => $dividendsPending,
            'dividends_paid' => $dividendsPaid,
            
            // Account classification
            'account_class' => $this->faker->randomElement(['ordinary', 'preferred', 'premium']),
            'locked_shares' => $this->faker->numberBetween(0, (int)($shareUnits * 0.2)), // Up to 20% locked
            'membership_fee_paid' => $this->faker->boolean(80),
            'bonus_shares_earned' => $bonusShares,
            
            // Balance limits
            'min_balance_required' => $this->faker->randomElement([1, 5, 10]),
            'max_balance_limit' => $this->faker->randomElement([1000, 5000, 10000, null]),
            
            // Features and audit
            'account_features' => [
                'auto_reinvest_dividends' => $this->faker->boolean(60),
                'voting_rights' => $this->faker->boolean(90),
                'transfer_allowed' => $this->faker->boolean(70),
            ],
            'audit_trail' => [
                [
                    'action' => 'account_opened',
                    'date' => $this->faker->dateTimeBetween('-3 years', '-1 year')->format('Y-m-d H:i:s'),
                    'by' => 'System',
                ],
            ],
            'remarks' => $this->faker->optional(0.3)->sentence(),
            'last_activity_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * State: New share account with no shares yet
     */
    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'share_units' => 0,
            'total_share_value' => 0,
            'dividends_earned' => 0,
            'dividends_pending' => 0,
            'dividends_paid' => 0,
            'locked_shares' => 0,
            'bonus_shares_earned' => 0,
            'membership_fee_paid' => false,
            'last_activity_date' => null,
        ]);
    }

    /**
     * State: Premium account with high shares
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'share_units' => $this->faker->numberBetween(500, 2000),
            'account_class' => 'premium',
            'min_balance_required' => 50,
            'max_balance_limit' => null, // No limit
            'membership_fee_paid' => true,
        ]);
    }

    /**
     * State: Account with pending dividends
     */
    public function withPendingDividends(): static
    {
        return $this->state(function (array $attributes) {
            $earned = $this->faker->randomFloat(2, 10000, 100000);
            $paid = $earned * 0.3; // Only 30% paid
            
            return [
                'dividends_earned' => $earned,
                'dividends_paid' => $paid,
                'dividends_pending' => $earned - $paid,
            ];
        });
    }

    /**
     * State: Account with locked shares (e.g., loan collateral)
     */
    public function withLockedShares(int $lockedCount = null): static
    {
        return $this->state(function (array $attributes) use ($lockedCount) {
            $totalShares = $attributes['share_units'] ?? 100;
            $locked = $lockedCount ?? (int)($totalShares * 0.5); // 50% locked by default
            
            return [
                'locked_shares' => min($locked, $totalShares),
            ];
        });
    }
}
