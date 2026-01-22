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
        // Add tenant_id to savings_products table
        Schema::table('savings_products', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Update code unique constraint to be composite with tenant_id
        Schema::table('savings_products', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['tenant_id', 'code']);
        });

        // Add tenant_id to loan_products table
        Schema::table('loan_products', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Update code unique constraint to be composite with tenant_id
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse loan_products changes
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique(['code']);
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse savings_products changes
        Schema::table('savings_products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique(['code']);
        });

        Schema::table('savings_products', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
