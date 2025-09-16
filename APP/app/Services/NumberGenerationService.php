<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Share;
use Illuminate\Support\Str;

class NumberGenerationService
{
    /**
     * Generate unique transaction number
     */
    public function generateTransactionNumber(string $type): string
    {
        $prefixMap = [
            'deposit' => 'DEP',
            'withdrawal' => 'WTH',
            'share_purchase' => 'SHR',
            'loan_disbursement' => 'LDB',
            'loan_repayment' => 'LRP',
            'reversal' => 'REV',
        ];

        $prefix = $prefixMap[$type] ?? 'TXN';
        $date = now()->format('Ymd');

        // Get next sequence number for today
        $sequence = Transaction::whereDate('created_at', now()->toDateString())
                ->where('transaction_number', 'like', $prefix . $date . '%')
                ->count() + 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique share certificate number
     */
    public function generateCertificateNumber(): string
    {
        $year = now()->format('Y');
        $sequence = Share::whereYear('created_at', $year)->count() + 1;

        return 'SHR' . $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate receipt number for external documents
     */
    public function generateReceiptNumber(string $type = 'RCP'): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        return $type . $date . $random;
    }
}
