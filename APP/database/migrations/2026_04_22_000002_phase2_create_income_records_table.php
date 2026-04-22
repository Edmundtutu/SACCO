<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — Add income_records detail table.
 *
 * Additive migration.  Every income record links to a transactions row
 * and is created exclusively via TransactionService + IncomeHandler.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_records', function (Blueprint $table) {
            $table->id();

            // Link to the canonical transaction row
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();

            // Income classification
            $table->string('category', 50);           // e.g. 'membership_fee', 'service_fee'
            $table->string('gl_account_code', 20);    // resolved at creation time
            $table->string('gl_account_name', 120);

            // Financial detail
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('payment_reference', 120)->nullable();

            // Narrative
            $table->text('description')->nullable();
            $table->string('receipt_number', 80)->nullable()->unique();

            // Payer reference (member or external party)
            $table->unsignedBigInteger('payer_member_id')->nullable();
            $table->foreign('payer_member_id')->references('id')->on('users')->nullOnDelete();

            // Soft audit
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

            // Multi-tenancy
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->timestamps();

            $table->index(['category', 'created_at']);
            $table->index(['transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_records');
    }
};
