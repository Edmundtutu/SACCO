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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->integer('installment_number'); // 1, 2, 3, etc.
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->decimal('scheduled_amount', 10, 2); // Originally scheduled payment amount
            $table->decimal('principal_amount', 10, 2)->default(0); // Principal portion
            $table->decimal('interest_amount', 10, 2)->default(0); // Interest portion
            $table->decimal('penalty_amount', 10, 2)->default(0); // Penalty for late payment
            $table->decimal('total_amount', 10, 2); // Total amount paid in this installment
            $table->decimal('balance_after_payment', 15, 2)->nullable(); // Outstanding balance after this payment
            $table->integer('days_late')->default(0); // Days payment was late
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived'])->default('pending');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'check', 'deduction'])->nullable();
            $table->string('payment_reference')->nullable(); // Bank ref, mobile money ref, etc.
            $table->text('notes')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users'); // Staff who collected payment
            $table->foreignId('approved_by')->nullable()->constrained('users'); // For waivers or adjustments
            $table->timestamps();

            $table->index(['loan_id', 'installment_number']);
            $table->index(['loan_id', 'status']);
            $table->index('due_date');
            $table->index('payment_date');
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayments');
    }
};