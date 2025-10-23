<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add share_account_id to link individual share certificates to their parent share account
     */
    public function up(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->foreignId('share_account_id')
                ->nullable()
                ->after('member_id')
                ->constrained('share_accounts')
                ->onDelete('cascade')
                ->comment('Links share certificate to parent share account');
            
            $table->index('share_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->dropForeign(['share_account_id']);
            $table->dropIndex(['share_account_id']);
            $table->dropColumn('share_account_id');
        });
    }
};
