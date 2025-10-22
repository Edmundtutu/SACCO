<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_product_id')->constrained('loan_products')->cascadeOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('processing_fee', 15, 2)->default(0);
            $table->decimal('insurance_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2); // Principal + interest + fees
            $table->integer('repayment_period_months');
            $table->decimal('monthly_payment', 15, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('principal_balance', 15, 2);
            $table->decimal('interest_balance', 15, 2);
            $table->decimal('penalty_balance', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            
            // Dates
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('first_payment_date')->nullable();
            $table->date('maturity_date')->nullable();
            
            // Additional info
            $table->text('purpose')->nullable();
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Approval/disbursement tracking
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('disbursed_by')->nullable()->constrained('users');
            $table->foreignId('disbursement_account_id')->nullable()->constrained('accounts');
            
            $table->timestamps();

            // Indexes
            $table->index('loan_product_id');
            $table->index(['disbursement_date', 'maturity_date']);
            $table->index('outstanding_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_accounts');
    }
};
