<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: tenant_id is nullable during initial migration to allow for data backfilling.
     * After running TenantDataMigrationSeeder, all records will have tenant_id populated.
     * In production, you may want to make this NOT NULL after seeding.
     *
     * @return void
     */
    public function up()
    {
        // Add tenant_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Update email unique constraint to be composite with tenant_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
