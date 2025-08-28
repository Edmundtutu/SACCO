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
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Internal transaction reference
            $table->date('transaction_date');
            $table->string('account_code'); // Chart of accounts code (e.g., 1001, 2001, etc.)
            $table->string('account_name'); // Human readable account name
            $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense']); // Account classification
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->text('description'); // Transaction description
            $table->string('reference_type')->nullable(); // Type of source document (loan, savings, etc.)
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of source document
            $table->foreignId('member_id')->nullable()->constrained('users'); // If transaction relates to a member
            $table->string('batch_id')->nullable(); // For grouping related entries
            $table->enum('status', ['posted', 'pending', 'reversed'])->default('posted');
            $table->foreignId('posted_by')->constrained('users'); // User who posted the entry
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'account_code']);
            $table->index(['account_type', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('batch_id');
            $table->index('transaction_id');
            $table->index(['member_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_ledger');
    }
};