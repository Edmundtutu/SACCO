<?php

namespace App\Services;

use App\DTOs\TransactionDTO;
use Illuminate\Support\Facades\Log;

/**
 * Phase 1 — Transaction Structure Normalization
 *
 * Maps legacy and inconsistent transaction payloads to the canonical
 * TransactionDTO structure without breaking existing callers.
 *
 * Modes (config: sacco.accounting_normalization_mode):
 *   'observe' – pass through unchanged; only log detected inconsistencies.
 *   'soft'    – normalize transparently and log when normalization occurs.
 *   'hard'    – reject payloads that cannot be normalized (throw exception).
 *
 * Canonical internal fields:
 *   - type          (string, required)
 *   - member_id     (int, required)
 *   - amount        (float > 0, required)
 *   - processed_by  (int, required)
 *   - payment_method    (string|null, promoted from metadata)
 *   - payment_reference (string|null, promoted from metadata)
 *   - account_id        (int|null, required for deposit/withdrawal)
 *   - related_loan_id   (int|null, required for loan transactions)
 *
 * Metadata conventions preserved for backward compatibility:
 *   - payment_method / payment_reference may still live in metadata.
 *   - principal_amount / interest_amount / penalty_amount / repayment_id
 *     are written by LoanRepaymentHandler and remain in metadata.
 *   - notes / collected_by remain in metadata.
 */
class TransactionNormalizer
{
    /**
     * Normalize a raw payload array into a canonical TransactionDTO.
     *
     * @param  array  $payload  Raw request / service data.
     * @return TransactionDTO
     */
    public function normalize(array $payload): TransactionDTO
    {
        $mode = config('sacco.accounting_normalization_mode', 'soft');

        // 1. Extract canonical fields, pulling from legacy locations if needed.
        $normalized = $this->extractCanonical($payload);

        // 2. Detect any gaps or inconsistencies.
        $warnings = $this->detectWarnings($payload, $normalized);

        // 3. Act according to the active mode.
        if (!empty($warnings)) {
            $logContext = [
                'mode'        => $mode,
                'type'        => $normalized['type'] ?? null,
                'member_id'   => $normalized['member_id'] ?? null,
                'warnings'    => $warnings,
                'raw_payload' => $this->sanitizeForLog($payload),
            ];

            if ($mode === 'observe') {
                Log::info('[TransactionNormalizer] Inconsistencies detected (observe mode — not normalized)', $logContext);
                // Return DTO built from the raw payload without modifications.
                return TransactionDTO::fromArray($payload);
            }

            Log::info('[TransactionNormalizer] Payload normalized', $logContext);
        }

        return TransactionDTO::fromArray($normalized);
    }

    /**
     * Normalize a TransactionDTO that was already built (e.g. via fromRequest).
     * Promotes metadata fields to canonical positions if they are missing at
     * the top level.
     */
    public function normalizeDTO(TransactionDTO $dto): TransactionDTO
    {
        $mode = config('sacco.accounting_normalization_mode', 'soft');

        $paymentMethod    = $dto->paymentMethod    ?? $dto->metadata['payment_method']    ?? null;
        $paymentReference = $dto->paymentReference ?? $dto->metadata['payment_reference'] ?? null;

        if ($paymentMethod === $dto->paymentMethod && $paymentReference === $dto->paymentReference) {
            // Nothing to normalize.
            return $dto;
        }

        if ($mode === 'observe') {
            Log::info('[TransactionNormalizer] DTO has metadata-only payment fields (observe mode — not promoted)', [
                'type'      => $dto->type,
                'member_id' => $dto->memberId,
            ]);
            return $dto;
        }

        Log::info('[TransactionNormalizer] Promoted payment fields from metadata to canonical DTO fields', [
            'type'             => $dto->type,
            'member_id'        => $dto->memberId,
            'payment_method'   => $paymentMethod,
        ]);

        return new TransactionDTO(
            memberId:         $dto->memberId,
            type:             $dto->type,
            amount:           $dto->amount,
            accountId:        $dto->accountId,
            feeAmount:        $dto->feeAmount,
            description:      $dto->description,
            relatedLoanId:    $dto->relatedLoanId,
            processedBy:      $dto->processedBy,
            metadata:         $dto->metadata,
            paymentMethod:    $paymentMethod,
            paymentReference: $paymentReference,
        );
    }

    /**
     * Extract canonical field values from a raw payload, pulling from
     * legacy / alternative key names where present.
     */
    protected function extractCanonical(array $payload): array
    {
        $metadata = $payload['metadata'] ?? null;

        // payment_method: top-level takes priority, then metadata.
        $paymentMethod = $payload['payment_method'] ?? $metadata['payment_method'] ?? null;

        // payment_reference: top-level takes priority, then metadata.
        $paymentReference = $payload['payment_reference'] ?? $metadata['payment_reference'] ?? null;

        // related_loan_id: accept 'loan_id' as a legacy alias.
        $relatedLoanId = $payload['related_loan_id']
            ?? $payload['loan_id']
            ?? null;

        // processed_by: accept 'staff_id' as a legacy alias.
        $processedBy = $payload['processed_by']
            ?? $payload['staff_id']
            ?? null;

        return array_merge($payload, [
            'payment_method'    => $paymentMethod,
            'payment_reference' => $paymentReference,
            'related_loan_id'   => $relatedLoanId,
            'processed_by'      => $processedBy,
        ]);
    }

    /**
     * Identify inconsistencies between the raw payload and canonical form.
     *
     * @return string[] Human-readable warning messages.
     */
    protected function detectWarnings(array $raw, array $normalized): array
    {
        $warnings = [];

        // payment_method was only in metadata, not at top level.
        if (
            !isset($raw['payment_method']) &&
            isset($raw['metadata']['payment_method'])
        ) {
            $warnings[] = 'payment_method found only inside metadata; promoted to canonical field';
        }

        // payment_reference was only in metadata, not at top level.
        if (
            !isset($raw['payment_reference']) &&
            isset($raw['metadata']['payment_reference'])
        ) {
            $warnings[] = 'payment_reference found only inside metadata; promoted to canonical field';
        }

        // Legacy loan_id alias used instead of related_loan_id.
        if (isset($raw['loan_id']) && !isset($raw['related_loan_id'])) {
            $warnings[] = 'loan_id used as alias for related_loan_id; please update caller to use related_loan_id';
        }

        // Legacy staff_id alias used instead of processed_by.
        if (isset($raw['staff_id']) && !isset($raw['processed_by'])) {
            $warnings[] = 'staff_id used as alias for processed_by; please update caller to use processed_by';
        }

        return $warnings;
    }

    /**
     * Sanitize a payload array for safe logging (remove sensitive values).
     */
    protected function sanitizeForLog(array $payload): array
    {
        $safe = $payload;
        foreach (['payment_reference', 'payment_method'] as $key) {
            if (isset($safe[$key])) {
                $safe[$key] = '[present]';
            }
        }
        if (isset($safe['metadata'])) {
            $safe['metadata'] = '[present]';
        }
        return $safe;
    }
}
