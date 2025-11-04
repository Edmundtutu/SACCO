<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\Share;
use App\Models\ShareAccount;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\NumberGenerationService;

class SharePurchaseHandler implements TransactionHandlerInterface
{
    protected NumberGenerationService $numberGenerationService;

    public function __construct(NumberGenerationService $numberGenerationService)
    {
        $this->numberGenerationService = $numberGenerationService;
    }

    public function validate(TransactionDTO $transactionData): void
    {
        // Validate share purchase amount
        $shareValue = config('sacco.share_value', 1000);
        $maxSharesPerPurchase = config('sacco.max_shares_per_purchase', 100);

        if ($transactionData->amount % $shareValue !== 0) {
            throw new InvalidTransactionException("Share purchase amount must be in multiples of {$shareValue}");
        }

        $shareCount = $transactionData->amount / $shareValue;
        if ($shareCount > $maxSharesPerPurchase) {
            throw new InvalidTransactionException("Maximum {$maxSharesPerPurchase} shares per transaction");
        }

        // ✅ ENFORCE: Member must have ShareAccount
        $shareAccount = Account::where('member_id', $transactionData->memberId)
            ->where('accountable_type', ShareAccount::class)
            ->first();

        if (!$shareAccount) {
            throw new InvalidTransactionException(
                "Member #{$transactionData->memberId} does not have a share account. "
                . "Please contact administrator to complete member account setup."
            );
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        $shareValue = config('sacco.share_value', 1000);
        $shareCount = $transactionData->amount / $shareValue;

        // Get ShareAccount (must exist per validation)
        $account = Account::where('member_id', $transaction->member_id)
            ->where('accountable_type', ShareAccount::class)
            ->first();

        if (!$account) {
            throw new InvalidTransactionException(
                "ShareAccount not found during execution. This should not happen."
            );
        }

        $shareAccount = $account->accountable;

        // Create share certificate linked to ShareAccount
        Share::create([
            'member_id' => $transaction->member_id,
            'share_account_id' => $shareAccount->id,  // ✅ LINKED!
            'certificate_number' => $this->numberGenerationService->generateCertificateNumber(),
            'shares_count' => $shareCount,
            'share_value' => $shareValue,
            'total_value' => $shareCount * $shareValue,
            'purchase_date' => now(),
            'status' => 'active'
        ]);

        // ✅ UPDATE SHARE ACCOUNT AGGREGATES
        $shareAccount->recordSharePurchase($shareCount, $shareValue);
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Share purchase by member #{$transaction->member_id}"
            ),
            new LedgerEntryDTO(
                accountCode: '3001',
                accountName: 'Member Share Capital',
                accountType: 'equity',
                debitAmount: 0,
                creditAmount: $transaction->amount,
                description: "Share capital from member #{$transaction->member_id}"
            ),
        ];
    }
}
