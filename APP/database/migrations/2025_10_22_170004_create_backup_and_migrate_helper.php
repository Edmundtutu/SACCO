<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Helper migration to backup existing accounts before refactoring
     */
    public function up(): void
    {
        // Create backup table if it doesn't exist
        if (!Schema::hasTable('accounts_backup')) {
            DB::statement('CREATE TABLE accounts_backup AS SELECT * FROM accounts');

            if (isset($this->command)) {
                $this->command->info('✅ Backup table created: accounts_backup');
                $this->command->info('📊 Backed up ' . DB::table('accounts_backup')->count() . ' accounts');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_backup');
    }
};
