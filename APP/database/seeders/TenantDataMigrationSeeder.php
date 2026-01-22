<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantDataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates a default tenant and backfills all existing data
     * with the default tenant_id to maintain data integrity during migration.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        try {
            // Create default tenant for existing data
            $defaultTenant = Tenant::create([
                'sacco_code' => 'SAC000001',
                'sacco_name' => 'Main SACCO',
                'slug' => 'main-sacco',
                'email' => 'admin@mainsacco.com',
                'phone' => '+254700000000',
                'address' => 'Default Address',
                'country' => 'Kenya',
                'currency' => 'KES',
                'subscription_plan' => 'premium',
                'status' => 'active',
                'max_members' => 10000,
                'max_staff' => 100,
                'max_loans' => 50000,
                'enabled_features' => [
                    'loans',
                    'savings',
                    'shares',
                    'dividends',
                    'wallet',
                    'mobile_banking',
                ],
                'owner_name' => 'System Administrator',
                'owner_email' => 'admin@mainsacco.com',
                'notes' => 'Default tenant created during multi-tenant migration',
            ]);

            $this->command->info("✓ Created default tenant: {$defaultTenant->sacco_name} (ID: {$defaultTenant->id})");

            // Backfill tenant_id to all existing records
            $this->backfillTenantId($defaultTenant->id);

            DB::commit();

            $this->command->info('✓ Successfully migrated all existing data to multi-tenant structure');
            $this->command->info("✓ All records have been assigned to tenant: {$defaultTenant->sacco_name}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Backfill tenant_id to all tenant-scoped tables
     */
    protected function backfillTenantId(int $tenantId): void
    {
        $tables = [
            'users',
            'memberships',
            'individual_profiles',
            'vsla_profiles',
            'mfi_profiles',
            'savings_products',
            'loan_products',
            'accounts',
            'savings_accounts',
            'loan_accounts',
            'share_accounts',
            'loans',
            'loan_guarantors',
            'loan_repayments',
            'shares',
            'transactions',
            'savings_goals',
            'general_ledger',
            'chart_of_accounts',
            'dividends',
            'dividend_payments',
        ];

        foreach ($tables as $table) {
            try {
                $updated = DB::table($table)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $tenantId]);

                if ($updated > 0) {
                    $this->command->info("  ✓ Updated {$updated} records in {$table}");
                }
            } catch (\Exception $e) {
                // Table might not exist or might not have tenant_id column yet
                $this->command->warn("  ! Skipped {$table}: " . $e->getMessage());
            }
        }
    }
}
