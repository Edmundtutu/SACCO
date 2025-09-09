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
        Schema::create('savings_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['compulsory', 'voluntary', 'fixed_deposit', 'special']);
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->decimal('maximum_balance', 15, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->default(0); // Annual interest rate percentage
            $table->enum('interest_calculation', ['simple', 'compound'])->default('simple');
            $table->enum('interest_payment_frequency', ['monthly', 'quarterly', 'annually'])->default('annually');
            $table->decimal('minimum_monthly_contribution', 15, 2)->nullable();
            $table->integer('maturity_period_months')->nullable(); // For fixed deposits
            $table->decimal('withdrawal_fee', 10, 2)->default(0);
            $table->boolean('allow_partial_withdrawals')->default(true);
            $table->integer('minimum_notice_days')->default(0); // Days notice required for withdrawal
            $table->boolean('is_active')->default(true);
            $table->json('additional_rules')->nullable(); // Store complex rules as JSON
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
        Schema::dropIfExists('savings_products');
    }
};