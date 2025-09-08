<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  MFI = Mutual Fund Institution
     *  Generic composition of micro financial institutions
     *
     *  SACCO-specific requirements
     * @return void
     */
    public function up()
    {
        Schema::create('mfi_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('contact_person')->nullable();;
            $table->string('contact_number')->nullable();
            $table->integer('membership_count')->nullable();
            $table->json('board_members')->nullable();
            $table->string('registration_certificate')->nullable();
            $table->string('bylaws_copy')->nullable();
            $table->text('resolution_minutes')->nullable();
            $table->string('operating_license')->nullable();
            $table->string('address')->nullable();
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
        Schema::dropIfExists('mfi_profiles');
    }
};
