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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('sacco_code', 20)->unique()->comment('Unique SACCO identifier code');
            $table->string('sacco_name')->comment('Full SACCO name');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('country', 50)->default('Kenya');
            $table->string('currency', 3)->default('KES');
            
            // Subscription & Status
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])->default('basic');
            $table->enum('status', ['active', 'suspended', 'inactive', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            // Limits
            $table->integer('max_members')->default(100)->comment('Maximum allowed members');
            $table->integer('max_staff')->default(10)->comment('Maximum allowed staff');
            $table->integer('max_loans')->default(500)->comment('Maximum active loans');
            $table->decimal('max_loan_amount', 15, 2)->nullable()->comment('Maximum single loan amount');
            
            // Feature Flags
            $table->json('enabled_features')->nullable()->comment('JSON array of enabled features');
            $table->json('settings')->nullable()->comment('Tenant-specific settings');
            
            // Metadata
            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('owner_phone')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('subscription_plan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
};
