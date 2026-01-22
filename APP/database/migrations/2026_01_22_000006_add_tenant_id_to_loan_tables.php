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
        // Add tenant_id to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Update loan_number unique constraint to be composite with tenant_id
        Schema::table('loans', function (Blueprint $table) {
            $table->dropUnique(['loan_number']);
            $table->unique(['tenant_id', 'loan_number']);
        });

        // Add tenant_id to loan_guarantors table
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Add tenant_id to loan_repayments table
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse loan_repayments changes
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse loan_guarantors changes
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse loans changes
        Schema::table('loans', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'loan_number']);
            $table->unique(['loan_number']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
