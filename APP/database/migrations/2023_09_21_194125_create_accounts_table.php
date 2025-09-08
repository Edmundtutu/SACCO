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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->enum('account_type', ['save_for_target', 'savings'])->default('savings');
            $table->foreignId('member_id')->constrained('users');
            $table->foreignId('savings_product_id')->constrained('savings_products');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0); // Balance minus holds
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->date('last_interest_calculation')->nullable();
            $table->date('maturity_date')->nullable(); // For fixed deposits
            $table->enum('status', ['active', 'inactive', 'dormant', 'closed'])->default('active');
            $table->timestamp('last_transaction_date')->nullable();
            $table->text('closure_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');

            $table->timestamps();
            // Indexes
            $table->index(['member_id', 'status']);
            $table->index(['savings_product_id', 'status']);
            $table->index('account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};

