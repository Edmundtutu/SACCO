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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table-> string('transaction_type'); // deposit/savings, withdraw, loan payment
            $table-> integer('amount');
            $table->dateTime('Date_of_transaction');
            $table->foreignId('member_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('loan_id')->constrained()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
