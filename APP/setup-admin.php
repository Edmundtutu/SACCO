<?php

/**
 * SACCO Admin Panel Setup Script
 * 
 * This script helps set up the admin panel by creating necessary database records
 * and verifying the installation.
 * 
 * Usage: php setup-admin.php
 */

// Load Composer's autoloader first
require_once __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;
use App\Models\ChartOfAccount;

class AdminSetup
{
    public function run()
    {
        echo "SACCO Admin Panel Setup\n";
        echo "========================\n\n";

        $this->checkRequirements();
        $this->createAdminUser();
        $this->createStaffUser();
        $this->createChartOfAccounts();
        $this->createSavingsProducts();
        $this->createLoanProducts();
        $this->verifySetup();

        echo "\nSetup completed successfully!\n";
        echo "Admin Panel Access:\n";
        echo "   URL: " . config('app.url') . "/admin/login\n";
        echo "   Admin: [Set admin-email & password] / admin@sacco.com \n";
        echo "   Staff: [Set staff-email & password] / loans@sacco.com \n\n";
    }

    private function checkRequirements()
    {
        echo "Checking requirements...\n";

        // Check if Laravel is properly installed
        if (!class_exists('Illuminate\Foundation\Application')) {
            throw new Exception("Laravel framework not found. Please ensure Laravel is properly installed.");
        }

        // Check database connection
        try {
            \DB::connection()->getPdo();
            echo "✓ Database connection established\n";
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        // Check if required models exist
        $requiredModels = ['User', 'SavingsAccount', 'Loan', 'Share', 'Transaction', 'SavingsProduct', 'LoanProduct'];
        foreach ($requiredModels as $model) {
            $className = "App\\Models\\{$model}";
            if (!class_exists($className)) {
                throw new Exception("Model {$className} not found. Please ensure all SACCO models are created.");
            }
        }

        echo "✓ Requirements check passed\n\n";
    }

    private function createAdminUser()
    {
        echo "Creating admin user...\n";

        // Check if admin user already exists
        $existingAdmin = User::where('email', 'admin@sacco.com')->first();
        if ($existingAdmin) {
            echo "Admin user already exists: {$existingAdmin->email}\n";
            if ($existingAdmin->role !== 'admin') {
                $existingAdmin->update(['role' => 'admin']);
                echo "✓ Updated user role to admin\n";
            }
            echo "\n";
            return;
        }

        // Get user input for admin creation
        echo "Creating system administrator account:\n";
        
        $name = $this->prompt("Full Name", "System Administrator");
        $email = $this->prompt("Email Address", "admin@sacco.com");
        $password = $this->promptPassword("Password (min 8 characters)");

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        try {
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'account_verified_at' => now(),
                'membership_date' => now(),
            ]);

            echo "✓ Admin user created successfully: {$admin->email}\n\n";
        } catch (Exception $e) {
            echo "Error creating admin user: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    private function createStaffUser()
    {
        echo "👥 Creating staff user (Loan Officer)...\n";

        // Check if staff user already exists
        $existingStaff = User::where('email', 'loans@sacco.com')->first();
        if ($existingStaff) {
            echo "Staff user already exists: {$existingStaff->email}\n\n";
            return;
        }

        $name = $this->prompt("Staff Full Name", "John Loan Officer");
        $email = $this->prompt("Staff Email", "loans@sacco.com");
        $password = $this->promptPassword("Staff Password (min 8 characters)");

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        try {
            $staff = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'staff_level_2',
                'status' => 'active',
                'account_verified_at' => now(),
                'membership_date' => now(),
            ]);

            echo "✓ Staff user created successfully: {$staff->email}\n\n";
        } catch (Exception $e) {
            echo "Error creating staff user: " . $e->getMessage() . "\n\n";
        }
    }

    private function createChartOfAccounts()
    {
        echo "Setting up Chart of Accounts...\n";

        // Check if chart of accounts already exists
        if (ChartOfAccount::count() > 0) {
            echo "Chart of Accounts already exists (" . ChartOfAccount::count() . " accounts)\n\n";
            return;
        }

        try {
            $seeder = new \Database\Seeders\ChartOfAccountsSeeder();
            $seeder->run();
            echo "✓ Chart of Accounts created successfully\n\n";
        } catch (Exception $e) {
            echo "Could not create Chart of Accounts: " . $e->getMessage() . "\n\n";
        }
    }

    private function createSavingsProducts()
    {
        echo "Creating savings products...\n";

        $products = [
            [
                'name' => 'Member Wallet',
                'code' => 'WL001',
                'description' => 'Digital wallet for member transactions',
                'type' => 'wallet',
                'minimum_balance' => 0,
                'maximum_balance' => null,
                'interest_rate' => 0.00,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => null,
                'withdrawal_fee' => 0,
                'allow_partial_withdrawals' => true,
                'minimum_notice_days' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Compulsory Savings',
                'code' => 'CS001',
                'description' => 'Mandatory savings for all members (UGX)',
                'type' => 'compulsory',
                'minimum_balance' => 50000,
                'maximum_balance' => null,
                'interest_rate' => 5.0,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => 25000,
                'maturity_period_months' => null,
                'withdrawal_fee' => 5000,
                'allow_partial_withdrawals' => false,
                'minimum_notice_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Voluntary Savings',
                'code' => 'VS001',
                'description' => 'Flexible savings account with easy access (UGX)',
                'type' => 'voluntary',
                'minimum_balance' => 25000,
                'maximum_balance' => null,
                'interest_rate' => 3.0,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => null,
                'withdrawal_fee' => 2500,
                'allow_partial_withdrawals' => true,
                'minimum_notice_days' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Fixed Deposit',
                'code' => 'FD001',
                'description' => '12-month fixed deposit with higher interest (UGX)',
                'type' => 'fixed_deposit',
                'minimum_balance' => 500000,
                'maximum_balance' => null,
                'interest_rate' => 8.0,
                'interest_calculation' => 'compound',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => 12,
                'withdrawal_fee' => 25000,
                'allow_partial_withdrawals' => false,
                'minimum_notice_days' => 90,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            if (!SavingsProduct::where('code', $product['code'])->exists()) {
                SavingsProduct::create($product);
                echo "✓ Created: {$product['name']} ({$product['code']})\n";
            } else {
                echo "Already exists: {$product['name']} ({$product['code']})\n";
            }
        }

        echo "\n";
    }

    private function createLoanProducts()
    {
        echo "Creating loan products...\n";

        $products = [
            [
                'name' => 'Personal Loan',
                'code' => 'PL001',
                'description' => 'General purpose personal loan (UGX)',
                'type' => 'personal',
                'minimum_amount' => 250000,
                'maximum_amount' => 5000000,
                'interest_rate' => 12.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 6,
                'maximum_period_months' => 36,
                'processing_fee_rate' => 2.0,
                'insurance_fee_rate' => 1.0,
                'required_guarantors' => 2,
                'guarantor_savings_multiplier' => 3.0,
                'grace_period_days' => 5,
                'penalty_rate' => 2.0,
                'minimum_savings_months' => 6.0,
                'savings_to_loan_ratio' => 3.0,
                'require_collateral' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Loan',
                'code' => 'EL001',
                'description' => 'Quick access emergency loan (UGX)',
                'type' => 'emergency',
                'minimum_amount' => 50000,
                'maximum_amount' => 2500000,
                'interest_rate' => 15.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 3,
                'maximum_period_months' => 12,
                'processing_fee_rate' => 1.0,
                'insurance_fee_rate' => 0.5,
                'required_guarantors' => 1,
                'guarantor_savings_multiplier' => 2.0,
                'grace_period_days' => 3,
                'penalty_rate' => 3.0,
                'minimum_savings_months' => 3.0,
                'savings_to_loan_ratio' => 2.0,
                'require_collateral' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Development Loan',
                'code' => 'DL001',
                'description' => 'Long-term development and investment loan (UGX)',
                'type' => 'development',
                'minimum_amount' => 2500000,
                'maximum_amount' => 25000000,
                'interest_rate' => 10.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 12,
                'maximum_period_months' => 60,
                'processing_fee_rate' => 2.5,
                'insurance_fee_rate' => 1.5,
                'required_guarantors' => 3,
                'guarantor_savings_multiplier' => 4.0,
                'grace_period_days' => 7,
                'penalty_rate' => 1.5,
                'minimum_savings_months' => 12.0,
                'savings_to_loan_ratio' => 4.0,
                'require_collateral' => true,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            if (!LoanProduct::where('code', $product['code'])->exists()) {
                LoanProduct::create($product);
                echo "✓ Created: {$product['name']} ({$product['code']})\n";
            } else {
                echo "Already exists: {$product['name']} ({$product['code']})\n";
            }
        }

        echo "\n";
    }

    private function verifySetup()
    {
        echo "Verifying setup...\n";

        // Check admin users
        $adminCount = User::where('role', 'admin')->count();
        $staffCount = User::whereIn('role', ['staff_level_1', 'staff_level_2', 'staff_level_3'])->count();
        echo "✓ Admin users: {$adminCount}\n";
        echo "✓ Staff users: {$staffCount}\n";

        // Check products
        $walletProduct = SavingsProduct::where('type', 'wallet')->count();
        $savingsProducts = SavingsProduct::where('type', '!=', 'wallet')->count();
        $loanProducts = LoanProduct::count();
        echo "✓ Wallet product: {$walletProduct}\n";
        echo "✓ Savings products: {$savingsProducts}\n";
        echo "✓ Loan products: {$loanProducts}\n";

        // Check Chart of Accounts
        $chartAccounts = ChartOfAccount::count();
        echo "✓ Chart of Accounts: {$chartAccounts} accounts\n";

        // Check configuration
        $currency = config('app.currency', 'UGX');
        echo "✓ Currency: {$currency}\n";

        // Check routes
        try {
            $routes = app('router')->getRoutes();
            $adminRoutes = 0;
            foreach ($routes as $route) {
                if (str_starts_with($route->getName() ?? '', 'admin.')) {
                    $adminRoutes++;
                }
            }
            echo "✓ Admin routes: {$adminRoutes}\n";
        } catch (Exception $e) {
            echo "Could not verify routes\n";
        }

        // Check views
        $viewPath = resource_path('views/admin');
        if (is_dir($viewPath)) {
            echo "✓ Admin views directory exists\n";
        } else {
            echo "Admin views directory not found at: {$viewPath}\n";
        }
    }

    private function prompt($question, $default = null)
    {
        $prompt = $question;
        if ($default) {
            $prompt .= " [{$default}]";
        }
        $prompt .= ": ";

        echo $prompt;
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);

        return $input ?: $default;
    }

    private function promptPassword($question)
    {
        echo "{$question}: ";
        
        // Hide password input on Unix systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            system('stty -echo');
        }
        
        $handle = fopen("php://stdin", "r");
        $password = trim(fgets($handle));
        fclose($handle);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            system('stty echo');
        }
        
        echo "\n";
        return $password;
    }
}

// Run setup if called directly
if (php_sapi_name() === 'cli') {
    try {
        $setup = new AdminSetup();
        $setup->run();
    } catch (Exception $e) {
        echo "\nSetup failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }
}