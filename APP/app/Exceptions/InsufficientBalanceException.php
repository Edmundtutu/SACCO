<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct($message = "Insufficient balance for this transaction", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => 'INSUFFICIENT_BALANCE',
            ], $this->getCode());
        }

        return redirect()->back()->withErrors(['amount' => $this->getMessage()]);
    }
}
