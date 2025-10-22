<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add wallet transaction types to the enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit', 'withdrawal', 'transfer',
            'loan_disbursement', 'loan_repayment',
            'fee', 'interest', 'dividend',
            'share_purchase', 'share_redemption',
            'wallet_topup', 'wallet_withdrawal', 'wallet_to_savings', 'wallet_to_loan'
        )");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove wallet transaction types from the enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit', 'withdrawal', 'transfer',
            'loan_disbursement', 'loan_repayment',
            'fee', 'interest', 'dividend',
            'share_purchase', 'share_redemption'
        )");
    }
};
