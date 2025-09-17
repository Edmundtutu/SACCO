<?php

namespace App\Exceptions;

use Exception;

class InvalidTransactionException extends Exception
{
    public function __construct($message = "Invalid transaction data", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => 'INVALID_TRANSACTION',
            ], $this->getCode());
        }

        return redirect()->back()->withErrors(['error' => $this->getMessage()]);
    }
}
