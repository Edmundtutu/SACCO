<?php

namespace App\Exceptions;

use Exception;

class TransactionProcessingException extends Exception
{
    public function __construct($message = "Transaction processing failed", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction processing failed. Please try again.',
                'error_code' => 'TRANSACTION_PROCESSING_ERROR',
            ], 500);
        }

        return redirect()->back()->withErrors(['error' => 'Transaction processing failed. Please try again.']);
    }
}
