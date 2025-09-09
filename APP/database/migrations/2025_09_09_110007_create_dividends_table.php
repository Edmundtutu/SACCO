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
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->year('financial_year'); // Year for which dividends are declared
            $table->decimal('total_dividend_pool', 15, 2); // Total amount available for dividends
            $table->decimal('dividend_rate', 5, 2); // Dividend rate percentage
            $table->date('declaration_date'); // Date dividends were declared
            $table->date('record_date'); // Date to determine eligible shareholders
            $table->date('payment_date'); // Date dividends will be/were paid
            $table->enum('status', ['declared', 'approved', 'paid', 'cancelled'])->default('declared');
            $table->text('notes')->nullable();
            $table->foreignId('declared_by')->constrained('users'); // Who declared the dividends
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Who approved for payment
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique('financial_year'); // One dividend declaration per year
            $table->index(['financial_year', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dividends');
    }
};