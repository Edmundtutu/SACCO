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
            $table->foreignId('member_id')->constrained('users');
            $table->string('accountable_type');
            $table->unsignedBigInteger('accountable_id');
            $table->enum('status', ['active', 'inactive', 'dormant', 'closed'])->default('active');
            $table->timestamp('last_transaction_date')->nullable();
            $table->text('closure_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');

            $table->timestamps();
            // Indexes
            $table->index(['member_id', 'status']);
            $table->index(['accountable_type', 'accountable_id']);
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

