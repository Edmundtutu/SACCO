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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_type'); // can be Persoanl, team or bussiness
            $table ->dateTime('DOA'); // date of loan approval
            $table ->dateTime('DOR')->nullable(); // date of repayment
            $table->integer('loan_amount');
            $table->float('intrest_rate'); // categorised according to the loan figures
            $table->string('loan_status'); // paid, active, defaulted
            $table -> longText('repayment_terms');
            $table->foreignId('member_id')->constrained();
            $table->foreignId('account_id')->constrained(); // for disbursment
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
        Schema::dropIfExists('loans');
    }
};
