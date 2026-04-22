<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — Add expense_records detail table.
 *
 * This is an additive migration: no existing tables are modified.
 * Every expense is created via TransactionService and links to a
 * transactions row (transaction_id FK).  The GL posting is done by
 * ExpenseHandler; this table stores the expense-domain detail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_records', function (Blueprint $table) {
            $table->id();

            // Link to the canonical transaction row
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();

            // Expense classification
            $table->string('category', 50);           // e.g. 'stationery', 'transport'
            $table->string('gl_account_code', 20);    // resolved at creation time
            $table->string('gl_account_name', 120);

            // Financial detail
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('payment_reference', 120)->nullable();

            // Narrative
            $table->text('description')->nullable();
            $table->string('receipt_number', 80)->nullable()->unique();

            // Soft audit
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

            // Multi-tenancy (mirrors the convention used by other tables)
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->timestamps();

            $table->index(['category', 'created_at']);
            $table->index(['transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_records');
    }
};
