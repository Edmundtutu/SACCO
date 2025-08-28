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
        Schema::table('accounts', function (Blueprint $table) {
            // Add new enhanced columns
            $table->foreignId('savings_product_id')->constrained('savings_products')->after('account_number');
            $table->decimal('balance', 15, 2)->default(0)->after('savings_product_id');
            $table->decimal('available_balance', 15, 2)->default(0)->after('balance'); // Balance minus holds
            $table->decimal('minimum_balance', 15, 2)->default(0)->after('available_balance');
            $table->decimal('interest_earned', 15, 2)->default(0)->after('minimum_balance');
            $table->date('last_interest_calculation')->nullable()->after('interest_earned');
            $table->date('maturity_date')->nullable()->after('last_interest_calculation'); // For fixed deposits
            $table->enum('status', ['active', 'inactive', 'dormant', 'closed'])->default('active')->after('maturity_date');
            $table->timestamp('last_transaction_date')->nullable()->after('status');
            $table->text('closure_reason')->nullable()->after('last_transaction_date');
            $table->timestamp('closed_at')->nullable()->after('closure_reason');
            $table->foreignId('closed_by')->nullable()->constrained('users')->after('closed_at');
            
            // Add indexes
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
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['savings_product_id']);
            $table->dropForeign(['closed_by']);
            $table->dropColumn([
                'savings_product_id', 'balance', 'available_balance',
                'minimum_balance', 'interest_earned', 'last_interest_calculation',
                'maturity_date', 'status', 'last_transaction_date', 'closure_reason',
                'closed_at', 'closed_by'
            ]);
        });
    }
};