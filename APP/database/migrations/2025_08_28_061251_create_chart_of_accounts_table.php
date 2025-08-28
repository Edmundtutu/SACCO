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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique(); // e.g., 1001, 2001, 3001
            $table->string('account_name'); // e.g., Cash in Hand, Member Savings
            $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->enum('account_subtype', [
                'current_asset', 'fixed_asset', 'current_liability', 'long_term_liability',
                'owner_equity', 'retained_earnings', 'operating_income', 'non_operating_income',
                'operating_expense', 'non_operating_expense'
            ])->nullable();
            $table->string('parent_code')->nullable(); // For sub-accounts
            $table->text('description')->nullable();
            $table->enum('normal_balance', ['debit', 'credit']); // Normal balance side
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_manual_entry')->default(true); // Some accounts are system-controlled
            $table->integer('level')->default(1); // Account hierarchy level
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_date')->nullable();
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
            $table->index('parent_code');
            $table->index('account_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};