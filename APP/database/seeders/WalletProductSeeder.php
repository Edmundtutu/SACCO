<?php

namespace Database\Seeders;

use App\Models\SavingsProduct;
use Illuminate\Database\Seeder;

class WalletProductSeeder extends Seeder
{
    /**
     * Ensure wallet product exists with correct type
     *
     * @return void
     */
    public function run()
    {
        // Check if wallet product already exists
        $wallet = SavingsProduct::where('code', 'WL001')->first();

        if ($wallet) {
            // Update existing product to use 'wallet' type if it's 'special'
            if ($wallet->type === 'special') {
                $wallet->update(['type' => 'wallet']);
                $this->command->info('✅ Updated wallet product type from "special" to "wallet"');
            } else {
                $this->command->info('✅ Wallet product already exists with correct type');
            }
            return;
        }

        // Create wallet product if it doesn't exist
        SavingsProduct::create([
            'name' => 'Member Wallet',
            'code' => 'WL001',
            'description' => 'Digital wallet for member transactions',
            'type' => 'wallet',
            'minimum_balance' => 0,
            'maximum_balance' => null,
            'interest_rate' => 0.00,
            'interest_calculation' => 'simple',
            'interest_payment_frequency' => 'annually',
            'minimum_monthly_contribution' => null,
            'maturity_period_months' => null,
            'withdrawal_fee' => 0,
            'allow_partial_withdrawals' => true,
            'minimum_notice_days' => 0,
            'is_active' => true,
        ]);

        $this->command->info('✅ Wallet product (WL001) created successfully');
    }
}

