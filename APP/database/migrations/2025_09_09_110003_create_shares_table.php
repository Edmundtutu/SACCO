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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users'); // Reference to users table
            $table->foreignId('share_account_id')->nullable()->constrained('share_accounts')->onDelete('cascade')->comment('Links share certificate to parent share account');
            $table->string('certificate_number')->unique();
            $table->integer('shares_count');
            $table->decimal('share_value', 10, 2); // Value per share at time of purchase
            $table->decimal('total_value', 15, 2); // Total value of shares
            $table->date('purchase_date');
            $table->enum('status', ['active', 'transferred', 'redeemed'])->default('active');
            $table->text('transfer_details')->nullable(); // Details if transferred
            $table->date('transfer_date')->nullable();
            $table->foreignId('transferred_to')->nullable()->constrained('users');
            $table->date('redemption_date')->nullable();
            $table->decimal('redemption_value', 15, 2)->nullable();
            $table->text('redemption_reason')->nullable();
            $table->foreignId('processed_by')->constrained('users'); // Staff who processed the transaction
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['share_account_id']);
            $table->index('purchase_date');
            $table->index('certificate_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shares');
    }
};
