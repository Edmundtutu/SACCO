<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use App\Services\Transactions\TransactionHandlerInterface;
use App\Services\Transactions\DepositHandler;
use App\Services\Transactions\WithdrawalHandler;
use App\Services\Transactions\SharePurchaseHandler;
use App\Services\Transactions\LoanDisbursementHandler;
use App\Services\Transactions\LoanRepaymentHandler;
use App\Services\Transactions\WalletTransactionHandler;
use App\DTOs\TransactionDTO;
use App\Events\TransactionProcessed;
use App\Events\TransactionFailed;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\TransactionProcessingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    protected BalanceService $balanceService;
    protected LedgerService $ledgerService;
    protected ValidationService $validationService;
    protected NumberGenerationService $numberService;
    protected array $handlers = [];

    public function __construct(
        BalanceService $balanceService,
        LedgerService $ledgerService,
        ValidationService $validationService,
        NumberGenerationService $numberService
    ) {
        $this->balanceService = $balanceService;
        $this->ledgerService = $ledgerService;
        $this->validationService = $validationService;
        $this->numberService = $numberService;

        $this->registerHandlers();
    }

    /**
     * Register transaction type handlers
     */
    protected function registerHandlers(): void
    {
        $this->handlers = [
            'deposit' => app(DepositHandler::class),
            'withdrawal' => app(WithdrawalHandler::class),
            'share_purchase' => app(SharePurchaseHandler::class),
            'loan_disbursement' => app(LoanDisbursementHandler::class),
            'loan_repayment' => app(LoanRepaymentHandler::class),
            
            // Wallet transaction handlers
            'wallet_topup' => app(WalletTransactionHandler::class),
            'wallet_withdrawal' => app(WalletTransactionHandler::class),
            'wallet_to_savings' => app(WalletTransactionHandler::class),
            'wallet_to_loan' => app(WalletTransactionHandler::class),
        ];
    }

    /**
     * Process a transaction
     */
    public function processTransaction(TransactionDTO $transactionData): Transaction
    {
        $startTime = microtime(true);

        try {
            // Get appropriate handler
            $handler = $this->getHandler($transactionData->type);

            // Start database transaction
            DB::beginTransaction();

            // Validate transaction
            $this->validateTransaction($transactionData, $handler);

            // Process the transaction
            $transaction = $this->executeTransaction($transactionData, $handler);

            // Verify double-entry balance
            $this->verifyDoubleEntryBalance($transaction);

            // Commit transaction
            DB::commit();

            // Fire success event
            event(new TransactionProcessed($transaction));

            // Log transaction
            $this->logTransaction($transaction, $startTime);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();

            // Fire failure event
            event(new TransactionFailed($transactionData, $e->getMessage()));

            // Log error
            Log::error('Transaction processing failed', [
                'transaction_data' => $transactionData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new TransactionProcessingException(
                'Transaction processing failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get handler for transaction type
     */
    protected function getHandler(string $transactionType): TransactionHandlerInterface
    {
        if (!isset($this->handlers[$transactionType])) {
            throw new InvalidTransactionException("No handler found for transaction type: {$transactionType}");
        }

        return $this->handlers[$transactionType];
    }

    /**
     * Validate transaction before processing
     */
    protected function validateTransaction(TransactionDTO $transactionData, TransactionHandlerInterface $handler): void
    {
        // Business rule validation
        $this->validationService->validateBusinessRules($transactionData);

        // Handler-specific validation
        $handler->validate($transactionData);

        // Additional security validations
        $this->validateSecurityConstraints($transactionData);
    }

    /**
     * Execute the actual transaction
     */
    protected function executeTransaction(TransactionDTO $transactionData, TransactionHandlerInterface $handler): Transaction
    {
        // Generate transaction number
        $transactionNumber = $this->numberService->generateTransactionNumber($transactionData->type);

        // Create transaction record
        $transaction = Transaction::create([
            'transaction_number' => $transactionNumber,
            'member_id' => $transactionData->memberId,
            'account_id' => $transactionData->accountId,
            'type' => $transactionData->type,
            'category' => $this->getCategoryForType($transactionData->type),
            'amount' => $transactionData->amount,
            'fee_amount' => $transactionData->feeAmount ?? 0,
            'net_amount' => $transactionData->amount - ($transactionData->feeAmount ?? 0),
            'description' => $transactionData->description,
            'payment_method' => 'cash',
            'status' => 'pending',
            'transaction_date' => now(),
            'value_date' => now(),
            'related_loan_id' => $transactionData->relatedLoanId ?? null,
            'processed_by' => $transactionData->processedBy,
            'metadata' => $transactionData->metadata ?? null,
        ]);

        // Execute handler-specific logic
        $handler->execute($transaction, $transactionData);

        // Update balances
        if ($transactionData->accountId) {
            $this->balanceService->updateAccountBalance($transaction);
        }

        // Create general ledger entries
        $ledgerEntries = $handler->getAccountingEntries($transaction, $transactionData);
        $this->ledgerService->createLedgerEntries($transaction, $ledgerEntries);

        // Mark transaction as completed
        $transaction->update(['status' => 'completed']);

        return $transaction->fresh();
    }

    /**
     * Verify double-entry bookkeeping balance
     */
    protected function verifyDoubleEntryBalance(Transaction $transaction): void
    {
        $totalDebits = $transaction->generalLedgerEntries()->sum('debit_amount');
        $totalCredits = $transaction->generalLedgerEntries()->sum('credit_amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            throw new TransactionProcessingException(
                "Double-entry bookkeeping out of balance. Debits: {$totalDebits}, Credits: {$totalCredits}"
            );
        }
    }

    /**
     * Validate security constraints
     */
    protected function validateSecurityConstraints(TransactionDTO $transactionData): void
    {
        // Check daily limits
        $this->validationService->validateDailyLimits($transactionData);

        // Check member status
        $this->validationService->validateMemberStatus($transactionData->memberId);

        // Check account status
        if ($transactionData->accountId) {
            $this->validationService->validateAccountStatus($transactionData->accountId);
        }
    }

    /**
     * Get transaction category based on type
     */
    protected function getCategoryForType(string $type): string
    {
        $categoryMap = [
            'deposit' => 'savings',
            'withdrawal' => 'savings',
            'share_purchase' => 'share',
            'loan_disbursement' => 'loan',
            'loan_repayment' => 'loan',
            'wallet_topup' => 'savings',
            'wallet_withdrawal' => 'savings',
            'wallet_to_savings' => 'savings',
            'wallet_to_loan' => 'loan',
        ];

        return $categoryMap[$type] ?? 'general';
    }

    /**
     * Log transaction details
     */
    protected function logTransaction(Transaction $transaction, float $startTime): void
    {
        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('Transaction processed successfully', [
            'transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'member_id' => $transaction->member_id,
            'processing_time_ms' => $processingTime,
        ]);
    }

    /**
     * Reverse a transaction
     */
    public function reverseTransaction(int $transactionId, string $reason, int $reversedBy): Transaction
    {
        DB::beginTransaction();

        try {
            $originalTransaction = Transaction::findOrFail($transactionId);

            if ($originalTransaction->status === 'reversed') {
                throw new InvalidTransactionException('Transaction is already reversed');
            }

            // Create reversal transaction
            $reversalTransaction = $this->createReversalTransaction($originalTransaction, $reason, $reversedBy);

            // Mark original as reversed
            $originalTransaction->update([
                'status' => 'reversed',
                'reversal_reason' => $reason,
                'reversed_by' => $reversedBy,
                'reversed_at' => now(),
            ]);

            // Create reversal ledger entries
            $this->ledgerService->createReversalEntries($originalTransaction, $reversalTransaction);

            // Update balances
            if ($originalTransaction->account_id) {
                $this->balanceService->reverseAccountBalance($originalTransaction);
            }

            DB::commit();

            return $reversalTransaction;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create reversal transaction record
     */
    protected function createReversalTransaction(Transaction $original, string $reason, int $reversedBy): Transaction
    {
        return Transaction::create([
            'transaction_number' => $this->numberService->generateTransactionNumber('REV'),
            'member_id' => $original->member_id,
            'account_id' => $original->account_id,
            'type' => 'reversal',
            'category' => $original->category,
            'amount' => -$original->amount, // Negative amount for reversal
            'fee_amount' => -$original->fee_amount,
            'net_amount' => -$original->net_amount,
            'description' => "Reversal of {$original->transaction_number}: {$reason}",
            'payment_method' => $original->payment_method,
            'status' => 'completed',
            'transaction_date' => now(),
            'value_date' => now(),
            'related_account_id' => $original->id, // Link to original transaction
            'processed_by' => $reversedBy,
        ]);
    }

    /**
     * Get transaction summary for a member
     */
    public function getMemberTransactionSummary(int $memberId, array $dateRange = null): array
    {
        $query = Transaction::where('member_id', $memberId)
            ->where('status', 'completed');

        if ($dateRange) {
            $query->whereBetween('transaction_date', $dateRange);
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_deposits' => $transactions->where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => $transactions->where('type', 'withdrawal')->sum('amount'),
            'total_loan_disbursements' => $transactions->where('type', 'loan_disbursement')->sum('amount'),
            'total_loan_repayments' => $transactions->where('type', 'loan_repayment')->sum('amount'),
            'total_share_purchases' => $transactions->where('type', 'share_purchase')->sum('amount'),
            'net_cash_flow' => $transactions->sum('net_amount'),
        ];
    }
}
