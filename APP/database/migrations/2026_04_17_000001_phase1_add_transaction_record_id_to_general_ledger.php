<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 PR 2 — Strengthen GL ↔ Transaction linkage.
 *
 * Adds a nullable FK column `transaction_record_id` to `general_ledger` that
 * points directly to `transactions.id`.  This is ADDITIVE / non-destructive:
 *
 * - Existing rows remain untouched (the column is nullable).
 * - Existing string `transaction_id` (e.g. "GL-42-1") behaviour is preserved.
 * - New postings written by the updated LedgerService will dual-write:
 *   the legacy string `transaction_id` AND the new integer FK.
 * - An index is added for efficient reconciliation queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_ledger', function (Blueprint $table) {
            // Nullable FK to transactions – allows pre-existing rows with no
            // corresponding Transaction record (e.g. manual journal entries
            // or rows created before this migration).
            $table->unsignedBigInteger('transaction_record_id')
                ->nullable()
                ->after('transaction_id')
                ->comment('FK to transactions.id (Phase 1 dual-write linkage)');

            $table->foreign('transaction_record_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();

            $table->index('transaction_record_id', 'gl_transaction_record_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->dropForeign(['transaction_record_id']);
            $table->dropIndex('gl_transaction_record_id_idx');
            $table->dropColumn('transaction_record_id');
        });
    }
};
