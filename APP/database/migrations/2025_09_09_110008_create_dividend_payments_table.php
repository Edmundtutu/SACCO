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
        Schema::create('dividend_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained('users');
            $table->integer('eligible_shares'); // Number of shares owned on record date
            $table->decimal('dividend_amount', 10, 2); // Amount this member receives
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'account_credit'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users'); // Staff who processed payment
            $table->timestamps();

            $table->unique(['dividend_id', 'member_id']);
            $table->index(['member_id', 'status']);
            $table->index(['dividend_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dividend_payments');
    }
};