<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Centralized resolver that maps a payment_method value to its corresponding
 * GL asset account code and name.
 *
 * Mapping is driven by config('sacco.payment_method_gl_accounts') so that
 * chart-of-account codes can be changed without touching handler code.
 * An unknown payment_method falls back to the 'cash' entry safely.
 */
class PaymentMethodAccountResolver
{
    /**
     * Account meta indexed by GL code.
     */
    private const ACCOUNT_META = [
        '1001' => ['name' => 'Cash in Hand',       'type' => 'asset'],
        '1002' => ['name' => 'Bank Account',        'type' => 'asset'],
        '1003' => ['name' => 'Mobile Money Account','type' => 'asset'],
    ];

    /**
     * Resolve the GL account details for a given payment method.
     *
     * @param  string|null $paymentMethod  e.g. 'cash', 'bank_transfer', 'mobile_money'
     * @return array{account_code:string, account_name:string, account_type:string}
     */
    public static function resolve(?string $paymentMethod): array
    {
        $mapping = config('sacco.payment_method_gl_accounts', [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]);

        $method = $paymentMethod ?? 'cash';
        $code   = $mapping[$method] ?? $mapping['cash'] ?? '1001';
        $meta   = self::ACCOUNT_META[$code] ?? null;

        if ($meta === null) {
            Log::warning('PaymentMethodAccountResolver: no metadata for GL code, falling back to cash.', [
                'payment_method' => $method,
                'resolved_code'  => $code,
            ]);
            $code = '1001';
            $meta = self::ACCOUNT_META['1001'];
        }

        return [
            'account_code' => $code,
            'account_name' => $meta['name'],
            'account_type' => $meta['type'],
        ];
    }
}
