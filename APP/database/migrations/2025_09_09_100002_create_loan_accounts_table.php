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
            
            // Account-level tracking fields (NOT individual loan details)
            $table->decimal('total_disbursed_amount', 15, 2)->default(0)->comment('Total of all loans disbursed');
            $table->decimal('total_repaid_amount', 15, 2)->default(0)->comment('Total of all repayments');
            $table->decimal('current_outstanding', 15, 2)->default(0)->comment('Current total outstanding across all loans');
            
            // Linked accounts
            $table->foreignId('linked_savings_account')->nullable()->constrained('savings_accounts')->comment('Default savings account for repayments');
            
            // Account limits and configuration
            $table->decimal('min_loan_limit', 15, 2)->default(0)->comment('Minimum loan amount allowed');
            $table->decimal('max_loan_limit', 15, 2)->nullable()->comment('Maximum loan amount allowed');
            $table->enum('repayment_frequency_type', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])->default('monthly');
            
            // Account status and tracking
            $table->text('status_notes')->nullable()->comment('Internal notes about account status');
            $table->timestamp('last_activity_date')->nullable()->comment('Last loan or repayment activity');
            
            // Flexible features and audit
            $table->json('account_features')->nullable()->comment('Account-specific features/settings');
            $table->json('audit_trail')->nullable()->comment('Track account-level changes');
            $table->text('remarks')->nullable()->comment('General account remarks');
            
            $table->timestamps();
            
            // Indexes
            $table->index('linked_savings_account');
            $table->index('last_activity_date');
            $table->index('current_outstanding');
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
