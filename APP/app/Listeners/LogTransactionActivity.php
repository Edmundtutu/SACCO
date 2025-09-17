<?php

namespace App\Listeners;

use App\Events\TransactionProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogTransactionActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionProcessed $event): void
    {
        Log::info('Transaction processed successfully', [
            'transaction_id' => $event->transaction->id,
            'transaction_number' => $event->transaction->transaction_number,
            'type' => $event->transaction->type,
            'amount' => $event->transaction->amount,
            'member_id' => $event->transaction->member_id,
            'processed_at' => now(),
        ]);
    }
}
