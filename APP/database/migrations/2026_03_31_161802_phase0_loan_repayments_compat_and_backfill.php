<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 0 – Corrective migration (additive / non-destructive only).
 *
 * Addresses two schema-level issues identified in the problem statement:
 *
 * 1. Status enum mismatch
 *    LoanRepayment::markAsCompleted() and ::scopeCompleted() used the value
 *    'completed', which is NOT present in the loan_repayments.status enum
 *    (valid values: pending|paid|partial|overdue|waived).  Any rows that
 *    somehow carry 'completed' are back-filled to 'paid'.
 *
 * 2. Field name compatibility (amount / total_amount, reference / payment_reference)
 *    The legacy Api\LoansController::repay() called
 *      $loan->repayments()->create(['amount' => ..., 'reference' => ...])
 *    but the actual columns are total_amount and payment_reference.
 *    The mismatch is resolved via model-level accessors/mutators in
 *    LoanRepayment – no additional DB columns are required.
 *    This migration documents the intent and is safe to run on an empty table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Back-fill any rows whose status was set to the invalid value
        // 'completed'.  Under MySQL strict mode these rows should never exist,
        // but we run the update unconditionally so the migration is idempotent
        // on any environment.
        if (Schema::hasTable('loan_repayments')) {
            DB::table('loan_repayments')
                ->where('status', 'completed')
                ->update(['status' => 'paid']);
        }
    }

    public function down(): void
    {
        // No structural changes to reverse; the backfill is intentional.
    }
};
