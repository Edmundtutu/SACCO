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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['personal', 'emergency', 'development', 'school_fees', 'business', 'asset_financing']);
            $table->decimal('minimum_amount', 15, 2);
            $table->decimal('maximum_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Annual interest rate percentage
            $table->enum('interest_calculation', ['flat_rate', 'reducing_balance'])->default('reducing_balance');
            $table->integer('minimum_period_months')->default(1);
            $table->integer('maximum_period_months')->default(60);
            $table->decimal('processing_fee_rate', 5, 2)->default(0); // Percentage of loan amount
            $table->decimal('insurance_fee_rate', 5, 2)->default(0); // Percentage of loan amount
            $table->integer('required_guarantors')->default(2);
            $table->decimal('guarantor_savings_multiplier', 3, 1)->default(3.0); // Guarantor savings must be X times loan amount
            $table->integer('grace_period_days')->default(0); // Days before penalty applies
            $table->decimal('penalty_rate', 5, 2)->default(0); // Penalty rate for late payments
            $table->decimal('minimum_savings_months', 3, 1)->default(6.0); // Member must have saved for X months
            $table->decimal('savings_to_loan_ratio', 3, 1)->default(3.0); // Member savings must be 1:X of loan amount
            $table->boolean('require_collateral')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('eligibility_criteria')->nullable(); // Store complex eligibility rules as JSON
            $table->json('required_documents')->nullable(); // List of required documents
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_products');
    }
};