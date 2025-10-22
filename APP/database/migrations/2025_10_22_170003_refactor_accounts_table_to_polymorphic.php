<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Transform accounts table from savings-specific to polymorphic hub
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Drop old savings-specific fields (we'll move these to savings_accounts)
            $table->dropColumn([
                'savings_product_id',
                'balance',
                'available_balance',
                'minimum_balance',
                'interest_earned',
                'last_interest_calculation',
                'maturity_date',
                'last_transaction_date',
            ]);
            
            // Drop old account_type enum (we'll use accountable_type instead)
            $table->dropColumn('account_type');
            
            // Add polymorphic relationship columns
            $table->string('accountable_type')->after('member_id'); // Model class name
            $table->unsignedBigInteger('accountable_id')->after('accountable_type'); // Model ID
            
            // Add index for polymorphic relationship
            $table->index(['accountable_type', 'accountable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropIndex(['accountable_type', 'accountable_id']);
            $table->dropColumn(['accountable_type', 'accountable_id']);
            
            // Restore old savings-specific columns
            $table->enum('account_type', ['save_for_target', 'savings'])->default('savings')->after('account_number');
            $table->foreignId('savings_product_id')->after('member_id')->constrained('savings_products');
            $table->decimal('balance', 15, 2)->default(0)->after('savings_product_id');
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->date('last_interest_calculation')->nullable();
            $table->date('maturity_date')->nullable();
            $table->timestamp('last_transaction_date')->nullable();
        });
    }
};
