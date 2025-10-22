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
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_product_id')->constrained('savings_products')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0); // Balance minus holds
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0); // Annual interest rate
            $table->date('last_interest_calculation')->nullable();
            $table->date('maturity_date')->nullable(); // For fixed deposits
            $table->timestamp('last_transaction_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('savings_product_id');
            $table->index('last_transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_accounts');
    }
};
