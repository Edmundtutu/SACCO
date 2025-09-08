<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * VSLA = Village Savings and Loan Association
     * Generic composition of small financial organisations
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vsla_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('village');
            $table->string('sub_county');
            $table->string('district');
            $table->integer('membership_count');
            // Vsla-specific requirements
            $table->string('registration_certificate'); // Certified copy of certificate
            $table->string('constitution_copy');        // Certified constitution/bylaws
            $table->text('resolution_minutes');         // Resolution to join SACCO
            $table->json('executive_contacts');         // Contacts + National ID copies
            $table->string('recommendation_lc1');       // Recommendation letter from LC1
            $table->string('recommendation_cdo');       // Recommendation letter from CDO

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
        Schema::dropIfExists('vsla_profiles');
    }
};
