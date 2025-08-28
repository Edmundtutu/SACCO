<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add new enhanced columns
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'loan_disbursement', 'loan_repayment', 'fee', 'interest', 'dividend', 'share_purchase', 'share_redemption'])->after('transaction_number');
            $table->enum('category', ['savings', 'loan', 'share', 'fee', 'administrative'])->after('type');
            $table->decimal('amount', 15, 2)->after('category');
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 15, 2)->after('fee_amount'); // Amount after fees
            $table->decimal('balance_before', 15, 2)->nullable()->after('net_amount');
            $table->decimal('balance_after', 15, 2)->nullable()->after('balance_before');
            $table->text('description')->after('balance_after');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'check', 'internal_transfer'])->nullable()->after('description');
            $table->string('payment_reference')->nullable()->after('payment_method'); // External reference
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed')->after('payment_reference');
            $table->datetime('transaction_date')->after('status');
            $table->datetime('value_date')->nullable()->after('transaction_date'); // When transaction takes effect
            $table->foreignId('related_loan_id')->nullable()->constrained('loans')->after('value_date'); // If related to a loan
            $table->foreignId('related_account_id')->nullable()->constrained('accounts')->after('related_loan_id'); // Related account for transfers
            $table->text('reversal_reason')->nullable()->after('related_account_id');
            $table->foreignId('reversed_by')->nullable()->constrained('users')->after('reversal_reason');
            $table->timestamp('reversed_at')->nullable()->after('reversed_by');
            $table->foreignId('processed_by')->constrained('users')->after('reversed_at'); // Staff who processed
            $table->json('metadata')->nullable()->after('processed_by'); // Additional transaction data
            
            // Add indexes
            $table->index(['member_id', 'type', 'transaction_date']);
            $table->index(['account_id', 'transaction_date']);
            $table->index(['type', 'status', 'transaction_date']);
            $table->index('transaction_number');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['related_loan_id']);
            $table->dropForeign(['related_account_id']);
            $table->dropForeign(['reversed_by']);
            $table->dropForeign(['processed_by']);
            
            $table->dropColumn([
                'type', 'category', 'amount', 'fee_amount',
                'net_amount', 'balance_before', 'balance_after', 'description',
                'payment_method', 'payment_reference', 'status', 'transaction_date',
                'value_date', 'related_loan_id', 'related_account_id', 'reversal_reason',
                'reversed_by', 'reversed_at', 'processed_by', 'metadata'
            ]);
        });
    }
};