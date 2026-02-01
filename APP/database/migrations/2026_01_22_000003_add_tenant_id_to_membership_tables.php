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
        // Add tenant_id to memberships table
        Schema::table('memberships', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Add tenant_id to individual_profiles table
        Schema::table('individual_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Update national_id unique constraint to be composite with tenant_id
        Schema::table('individual_profiles', function (Blueprint $table) {
            $table->dropUnique(['national_id']);
            $table->unique(['tenant_id', 'national_id']);
        });

        // Add tenant_id to vsla_profiles table
        Schema::table('vsla_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        /** Following migration is commented out until a unique constrait 'registration 
         * number is attributed to the respective table
         * */
        // // Update registration_number unique constraint to be composite with tenant_id
        // Schema::table('vsla_profiles', function (Blueprint $table) {
        //     $table->dropUnique(['registration_number']);
        //     $table->unique(['tenant_id', 'registration_number']);
        // });

        // Add tenant_id to mfi_profiles table
        Schema::table('mfi_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        /**
         * See reason for commenting out Vsla registration_number uniquie index
         */
        // // Update license_number unique constraint to be composite with tenant_id
        // Schema::table('mfi_profiles', function (Blueprint $table) {
        //     $table->dropUnique(['license_number']);
        //     $table->unique(['tenant_id', 'license_number']);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse mfi_profiles changes
        // Schema::table('mfi_profiles', function (Blueprint $table) {
        //     $table->dropUnique(['tenant_id', 'license_number']);
        //     $table->unique(['license_number']);
        // });

        Schema::table('mfi_profiles', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // // Reverse vsla_profiles changes
        // Schema::table('vsla_profiles', function (Blueprint $table) {
        //     $table->dropUnique(['tenant_id', 'registration_number']);
        //     $table->unique(['registration_number']);
        // });

        Schema::table('vsla_profiles', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse individual_profiles changes
        Schema::table('individual_profiles', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'national_id']);
            $table->unique(['national_id']);
        });

        Schema::table('individual_profiles', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse memberships changes
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
