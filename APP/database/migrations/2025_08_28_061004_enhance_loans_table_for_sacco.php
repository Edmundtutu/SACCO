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
        Schema::table('loans', function (Blueprint $table) {
            // Add new enhanced columns  
            $table->foreignId('loan_product_id')->constrained('loan_products')->after('loan_number');
            $table->decimal('principal_amount', 15, 2)->after('loan_product_id');
            $table->decimal('interest_rate', 5, 2)->after('principal_amount'); // Store actual rate applied
            $table->decimal('processing_fee', 10, 2)->default(0)->after('interest_rate');
            $table->decimal('insurance_fee', 10, 2)->default(0)->after('processing_fee');
            $table->decimal('total_amount', 15, 2)->after('insurance_fee'); // Principal + interest + fees
            $table->integer('repayment_period_months')->after('total_amount');
            $table->decimal('monthly_payment', 10, 2)->after('repayment_period_months');
            $table->date('application_date')->after('monthly_payment');
            $table->date('approval_date')->nullable()->after('application_date');
            $table->date('disbursement_date')->nullable()->after('approval_date');
            $table->date('first_payment_date')->nullable()->after('disbursement_date');
            $table->date('maturity_date')->nullable()->after('first_payment_date');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'completed', 'defaulted', 'written_off'])->default('pending')->after('maturity_date');
            $table->decimal('outstanding_balance', 15, 2)->default(0)->after('status');
            $table->decimal('principal_balance', 15, 2)->default(0)->after('outstanding_balance');
            $table->decimal('interest_balance', 15, 2)->default(0)->after('principal_balance');
            $table->decimal('penalty_balance', 15, 2)->default(0)->after('interest_balance');
            $table->decimal('total_paid', 15, 2)->default(0)->after('penalty_balance');
            $table->text('purpose')->nullable()->after('total_paid');
            $table->text('collateral_description')->nullable()->after('purpose');
            $table->decimal('collateral_value', 15, 2)->nullable()->after('collateral_description');
            $table->text('rejection_reason')->nullable()->after('collateral_value');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('rejection_reason');
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->after('approved_by');
            $table->foreignId('disbursement_account_id')->nullable()->constrained('accounts')->after('disbursed_by'); // Account where loan was disbursed
            
            // Add indexes
            $table->index(['member_id', 'status']);
            $table->index(['loan_product_id', 'status']);
            $table->index('loan_number');
            $table->index('application_date');
            $table->index('maturity_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['loan_product_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['disbursed_by']);
            $table->dropForeign(['disbursement_account_id']);
            
            $table->dropColumn([
                'loan_product_id', 'principal_amount', 'interest_rate',
                'processing_fee', 'insurance_fee', 'total_amount', 'repayment_period_months',
                'monthly_payment', 'application_date', 'approval_date', 'disbursement_date',
                'first_payment_date', 'maturity_date', 'status', 'outstanding_balance', 'principal_balance',
                'interest_balance', 'penalty_balance', 'total_paid', 'purpose',
                'collateral_description', 'collateral_value', 'rejection_reason',
                'approved_by', 'disbursed_by', 'disbursement_account_id'
            ]);
        });
    }
};