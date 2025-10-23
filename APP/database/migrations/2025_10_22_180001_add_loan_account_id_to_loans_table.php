<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add loan_account_id to link individual loans to their parent loan account
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('loan_account_id')
                ->nullable()
                ->after('member_id')
                ->constrained('loan_accounts')
                ->onDelete('cascade')
                ->comment('Links loan to parent loan account');
            
            $table->index('loan_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['loan_account_id']);
            $table->dropIndex(['loan_account_id']);
            $table->dropColumn('loan_account_id');
        });
    }
};
