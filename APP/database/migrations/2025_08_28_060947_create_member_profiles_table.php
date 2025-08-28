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
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->text('next_of_kin_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('employer_name')->nullable();
            $table->text('employer_address')->nullable();
            $table->string('employer_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('id_copy_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_profiles');
    }
};