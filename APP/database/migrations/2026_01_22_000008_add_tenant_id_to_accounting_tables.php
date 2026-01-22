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
        // Add tenant_id to general_ledger table
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // NOTE: chart_of_accounts remains GLOBAL (no tenant_id)
        // All SACCOs use the same standardized Chart of Accounts structure
        // This avoids breaking transaction handlers that hardcode account codes
        // SECURITY: Chart of Accounts data will be visible across all tenants,
        // but this is acceptable as it only contains standardized account structure,
        // not actual financial data. Financial data (general_ledger) remains tenant-scoped.

        // Add tenant_id to dividends table
        Schema::table('dividends', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Add tenant_id to dividend_payments table
        Schema::table('dividend_payments', function (Blueprint $table) {
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
        // Reverse dividend_payments changes
        Schema::table('dividend_payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Reverse dividends changes
        Schema::table('dividends', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // NOTE: chart_of_accounts has no tenant_id to remove (remains global)

        // Reverse general_ledger changes
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
