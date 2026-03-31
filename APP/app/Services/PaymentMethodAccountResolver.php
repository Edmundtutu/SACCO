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
 *
 * Feature flag: features.use_centralized_payment_method_mapping
 * When the flag is disabled the resolver always returns the legacy
 * "Cash in Hand" (1001) values so existing behaviour is preserved.
 */
class PaymentMethodAccountResolver
{
    /**
     * Account meta indexed by GL code – used when config mapping resolves.
     */
    private const ACCOUNT_META = [
        '1001' => ['name' => 'Cash in Hand',       'type' => 'asset'],
        '1002' => ['name' => 'Bank Account',        'type' => 'asset'],
        '1003' => ['name' => 'Mobile Money Account','type' => 'asset'],
    ];

    /** Legacy fallback values (pre-Phase 0 hardcoded defaults). */
    private const LEGACY_CODE = '1001';
    private const LEGACY_NAME = 'Cash in Hand';
    private const LEGACY_TYPE = 'asset';

    /**
     * Resolve the GL account details for a given payment method.
     *
     * @param  string|null $paymentMethod  e.g. 'cash', 'bank_transfer', 'mobile_money'
     * @return array{account_code:string, account_name:string, account_type:string}
     */
    public static function resolve(?string $paymentMethod): array
    {
        if (!config('features.use_centralized_payment_method_mapping', true)) {
            return self::legacy();
        }

        $mapping = config('sacco.payment_method_gl_accounts', [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]);

        $method = $paymentMethod ?? 'cash';
        $code   = $mapping[$method] ?? $mapping['cash'] ?? self::LEGACY_CODE;
        $meta   = self::ACCOUNT_META[$code] ?? null;

        if ($meta === null) {
            Log::warning('PaymentMethodAccountResolver: no metadata for GL code, falling back.', [
                'payment_method' => $method,
                'resolved_code'  => $code,
            ]);
            return self::legacy();
        }

        return [
            'account_code' => $code,
            'account_name' => $meta['name'],
            'account_type' => $meta['type'],
        ];
    }

    /** Legacy/fallback — always returns Cash in Hand values. */
    private static function legacy(): array
    {
        return [
            'account_code' => self::LEGACY_CODE,
            'account_name' => self::LEGACY_NAME,
            'account_type' => self::LEGACY_TYPE,
        ];
    }
}
