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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('member_id')->constrained('users');

            // Enhanced columns in logical order
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Store actual rate applied
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('insurance_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 15, 2); // Principal + interest + fees
            $table->integer('repayment_period_months');
            $table->decimal('monthly_payment', 10, 2);
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('first_payment_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->enum('status', [
                'pending', 'under_review', 'approved', 'rejected',
                'disbursed', 'active', 'completed', 'defaulted', 'written_off'
            ])->default('pending');
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->decimal('principal_balance', 15, 2)->default(0);
            $table->decimal('interest_balance', 15, 2)->default(0);
            $table->decimal('penalty_balance', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->text('purpose')->nullable();
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('disbursed_by')->nullable()->constrained('users');
            $table->foreignId('disbursement_account_id')->nullable()->constrained('accounts'); // Account where loan was disbursed

            $table->timestamps();

            // Indexes
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
        Schema::dropIfExists('loans');
    }
};
