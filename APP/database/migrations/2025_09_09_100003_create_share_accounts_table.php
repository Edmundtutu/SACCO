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
        Schema::create('share_accounts', function (Blueprint $table) {
            $table->id();
            
            // Share ownership tracking (account-level, NOT individual certificates)
            $table->integer('share_units')->default(0)->comment('Total share units owned');
            $table->decimal('share_price', 10, 2)->comment('Current share price');
            $table->decimal('total_share_value', 15, 2)->default(0)->comment('Total value of all shares');
            
            // Dividends tracking
            $table->decimal('dividends_earned', 15, 2)->default(0)->comment('Total dividends earned');
            $table->decimal('dividends_pending', 15, 2)->default(0)->comment('Dividends pending distribution');
            $table->decimal('dividends_paid', 15, 2)->default(0)->comment('Dividends already paid out');
            
            // Account classification and limits
            $table->enum('account_class', ['ordinary', 'preferred', 'premium'])->default('ordinary')->comment('Share account classification');
            $table->integer('locked_shares')->default(0)->comment('Shares that cannot be transferred/redeemed');
            $table->boolean('membership_fee_paid')->default(false)->comment('Whether membership fee has been paid');
            $table->integer('bonus_shares_earned')->default(0)->comment('Bonus shares awarded');
            
            // Balance limits
            $table->integer('min_balance_required')->default(1)->comment('Minimum share units required');
            $table->integer('max_balance_limit')->nullable()->comment('Maximum share units allowed');
            
            // Flexible features and audit
            $table->json('account_features')->nullable()->comment('Account-specific features/settings');
            $table->json('audit_trail')->nullable()->comment('Track account-level changes');
            $table->text('remarks')->nullable()->comment('General account remarks');
            $table->timestamp('last_activity_date')->nullable()->comment('Last share transaction activity');
            
            $table->timestamps();
            
            // Indexes
            $table->index('share_units');
            $table->index('account_class');
            $table->index('last_activity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_accounts');
    }
};
