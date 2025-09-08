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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('member_id')->constrained('users');
            $table->foreignId('account_id')->constrained();

            // Enhanced columns in logical order
            $table->enum('type', [
                'deposit', 'withdrawal', 'transfer',
                'loan_disbursement', 'loan_repayment',
                'fee', 'interest', 'dividend',
                'share_purchase', 'share_redemption'
            ]);
            $table->enum('category', ['savings', 'loan', 'share', 'fee', 'administrative']);
            $table->decimal('amount', 15, 2);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // Amount after fees
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->text('description');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'check', 'internal_transfer'])->nullable();
            $table->string('payment_reference')->nullable(); // External reference
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->dateTime('transaction_date');
            $table->dateTime('value_date')->nullable(); // When transaction takes effect
            $table->foreignId('related_loan_id')->nullable()->constrained('loans'); // If related to a loan
            $table->foreignId('related_account_id')->nullable()->constrained('accounts'); // Related account for transfers
            $table->text('reversal_reason')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('processed_by')->constrained('users'); // Staff who processed
            $table->json('metadata')->nullable(); // Additional transaction data

            $table->timestamps();

            // Indexes
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
        Schema::dropIfExists('transactions');
    }
};

