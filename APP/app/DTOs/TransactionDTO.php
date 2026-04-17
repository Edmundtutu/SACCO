<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class TransactionDTO
{
    public function __construct(
        public int $memberId,
        public string $type,
        public float $amount,
        public ?int $accountId = null,
        public ?float $feeAmount = null,
        public ?string $description = null,
        public ?int $relatedLoanId = null,
        public ?int $processedBy = null,
        public ?array $metadata = null,
        // Canonical fields (Phase 1) – promoted out of metadata for consistency.
        // Legacy callers that pass payment_method/payment_reference inside
        // metadata are still supported; the TransactionNormalizer will extract
        // and populate these fields automatically.
        public ?string $paymentMethod = null,
        public ?string $paymentReference = null,
    ) {}

    /**
     * Create DTO from request data
     */
    public static function fromRequest(Request $request): self
    {
        // Determine transaction type based on route or request data
        $type = $request->string('type');
        if (!$type) {
            // Infer type from route
            $routeName = $request->route()?->getName();
            $typeMap = [
                'transactions.deposit' => 'deposit',
                'transactions.withdrawal' => 'withdrawal',
                'transactions.share-purchase' => 'share_purchase',
                'transactions.loan-disbursement' => 'loan_disbursement',
                'transactions.loan-repayment' => 'loan_repayment',
            ];
            $type = $typeMap[$routeName] ?? 'deposit';
        }

        $metadata = $request->array('metadata') ?: null;

        // Accept payment_method / payment_reference from top-level request
        // fields OR from inside metadata (legacy callers).
        $paymentMethod    = $request->string('payment_method')    ?: ($metadata['payment_method']    ?? null) ?: null;
        $paymentReference = $request->string('payment_reference') ?: ($metadata['payment_reference'] ?? null) ?: null;

        return new self(
            memberId: $request->integer('member_id'),
            type: $type,
            amount: $request->float('amount'),
            accountId: $request->integer('account_id') ?: null,
            feeAmount: $request->float('fee_amount') ?: null,
            description: $request->string('description') ?: null,
            relatedLoanId: $request->integer('related_loan_id') ?: $request->integer('loan_id') ?: null,
            processedBy: $request->integer('processed_by') ?: auth()->id(),
            metadata: $metadata,
            paymentMethod: $paymentMethod,
            paymentReference: $paymentReference,
        );
    }

    /**
     * Create DTO from array data
     */
    public static function fromArray(array $data): self
    {
        $metadata = $data['metadata'] ?? null;

        // Accept payment_method / payment_reference from top-level array keys
        // OR from inside metadata (legacy callers).
        $paymentMethod    = $data['payment_method']    ?? ($metadata['payment_method']    ?? null) ?? null;
        $paymentReference = $data['payment_reference'] ?? ($metadata['payment_reference'] ?? null) ?? null;

        // Accept 'loan_id' as a legacy alias for 'related_loan_id' (mirrors
        // the same logic in fromRequest).
        $relatedLoanId = $data['related_loan_id'] ?? $data['loan_id'] ?? null;

        return new self(
            memberId: $data['member_id'],
            type: $data['type'],
            amount: $data['amount'],
            accountId: $data['account_id'] ?? null,
            feeAmount: $data['fee_amount'] ?? null,
            description: $data['description'] ?? null,
            relatedLoanId: $relatedLoanId,
            processedBy: $data['processed_by'] ?? null,
            metadata: $metadata,
            paymentMethod: $paymentMethod,
            paymentReference: $paymentReference,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'member_id'        => $this->memberId,
            'type'             => $this->type,
            'amount'           => $this->amount,
            'account_id'       => $this->accountId,
            'fee_amount'       => $this->feeAmount,
            'description'      => $this->description,
            'related_loan_id'  => $this->relatedLoanId,
            'processed_by'     => $this->processedBy,
            'metadata'         => $this->metadata,
            'payment_method'   => $this->paymentMethod,
            'payment_reference'=> $this->paymentReference,
        ];
    }

    /**
     * Validate DTO data
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->memberId <= 0) {
            $errors[] = 'Member ID is required and must be positive';
        }

        if (empty($this->type)) {
            $errors[] = 'Transaction type is required';
        }

        if ($this->amount <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }

        if ($this->feeAmount !== null && $this->feeAmount < 0) {
            $errors[] = 'Fee amount cannot be negative';
        }

        // Type-specific validations
        if (in_array($this->type, ['deposit', 'withdrawal']) && !$this->accountId) {
            $errors[] = 'Account ID is required for savings transactions';
        }

        if (in_array($this->type, ['loan_disbursement', 'loan_repayment']) && !$this->relatedLoanId) {
            $errors[] = 'Related loan ID is required for loan transactions';
        }

        return $errors;
    }

    /**
     * Check if DTO is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Resolve the effective payment method with fallback chain:
     * 1. Top-level $paymentMethod field (canonical)
     * 2. metadata['payment_method'] (legacy)
     * 3. default 'cash'
     */
    public function resolvePaymentMethod(): string
    {
        return $this->paymentMethod
            ?? $this->metadata['payment_method']
            ?? 'cash';
    }

    /**
     * Resolve the effective payment reference with fallback chain:
     * 1. Top-level $paymentReference field (canonical)
     * 2. metadata['payment_reference'] (legacy)
     * 3. null
     */
    public function resolvePaymentReference(): ?string
    {
        return $this->paymentReference
            ?? $this->metadata['payment_reference']
            ?? null;
    }
}
