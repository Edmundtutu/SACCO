<?php

/**
 * SACCO Admin Panel Setup Script
 * 
 * This script helps set up the admin panel by creating necessary database records
 * and verifying the installation.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;

class AdminSetup
{
    public function run()
    {
        echo "🏦 SACCO Admin Panel Setup\n";
        echo "========================\n\n";

        $this->checkRequirements();
        $this->createAdminUser();
        $this->createDefaultProducts();
        $this->verifySetup();

        echo "\n✅ Setup completed successfully!\n";
        echo "📱 You can now access the admin panel at: /admin/login\n\n";
    }

    private function checkRequirements()
    {
        echo "🔍 Checking requirements...\n";

        // Check if Laravel is properly installed
        if (!class_exists('Illuminate\Foundation\Application')) {
            throw new Exception("Laravel framework not found. Please ensure Laravel is properly installed.");
        }

        // Check if required models exist
        $requiredModels = ['User', 'Account', 'Loan', 'Share', 'Transaction'];
        foreach ($requiredModels as $model) {
            $className = "App\\Models\\{$model}";
            if (!class_exists($className)) {
                throw new Exception("Model {$className} not found. Please ensure all SACCO models are created.");
            }
        }

        // Check if middleware is registered
        $kernel = app('Illuminate\Contracts\Http\Kernel');
        if (!array_key_exists('admin', $kernel->getRouteMiddleware())) {
            echo "⚠️  Warning: Admin middleware not registered. Please add to Kernel.php\n";
        }

        echo "✓ Requirements check passed\n\n";
    }

    private function createAdminUser()
    {
        echo "👤 Creating admin user...\n";

        // Check if admin user already exists
        $existingAdmin = User::where('role', 'admin')->first();
        if ($existingAdmin) {
            echo "ℹ️  Admin user already exists: {$existingAdmin->email}\n\n";
            return;
        }

        // Get user input for admin creation
        echo "Please enter admin user details:\n";
        
        $name = $this->prompt("Full Name", "SACCO Administrator");
        $email = $this->prompt("Email Address", "admin@sacco.local");
        $password = $this->promptPassword("Password");

        try {
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            echo "✓ Admin user created successfully: {$admin->email}\n\n";
        } catch (Exception $e) {
            echo "❌ Error creating admin user: " . $e->getMessage() . "\n\n";
        }
    }

    private function createDefaultProducts()
    {
        echo "🏪 Creating default products...\n";

        // Create default savings products
        $savingsProducts = [
            [
                'name' => 'Regular Savings',
                'description' => 'Standard savings account for all members',
                'minimum_balance' => 1000,
                'interest_rate' => 5.0,
                'is_mandatory' => true,
            ],
            [
                'name' => 'Fixed Deposit',
                'description' => 'High-interest fixed deposit account',
                'minimum_balance' => 10000,
                'interest_rate' => 8.0,
                'is_mandatory' => false,
            ]
        ];

        foreach ($savingsProducts as $product) {
            if (!SavingsProduct::where('name', $product['name'])->exists()) {
                SavingsProduct::create($product);
                echo "✓ Created savings product: {$product['name']}\n";
            }
        }

        // Create default loan products
        $loanProducts = [
            [
                'name' => 'Personal Loan',
                'description' => 'Short-term personal loans for members',
                'interest_rate' => 12.0,
                'max_amount' => 500000,
                'max_term_months' => 36,
                'processing_fee_rate' => 2.0,
            ],
            [
                'name' => 'Emergency Loan',
                'description' => 'Quick emergency loans for urgent needs',
                'interest_rate' => 15.0,
                'max_amount' => 100000,
                'max_term_months' => 12,
                'processing_fee_rate' => 1.0,
            ]
        ];

        foreach ($loanProducts as $product) {
            if (!LoanProduct::where('name', $product['name'])->exists()) {
                LoanProduct::create($product);
                echo "✓ Created loan product: {$product['name']}\n";
            }
        }

        echo "\n";
    }

    private function verifySetup()
    {
        echo "🔬 Verifying setup...\n";

        // Check admin user
        $adminCount = User::where('role', 'admin')->count();
        echo "✓ Admin users: {$adminCount}\n";

        // Check products
        $savingsProducts = SavingsProduct::count();
        $loanProducts = LoanProduct::count();
        echo "✓ Savings products: {$savingsProducts}\n";
        echo "✓ Loan products: {$loanProducts}\n";

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
            echo "⚠️  Could not verify routes\n";
        }

        // Check views
        $viewPath = resource_path('views/admin');
        if (is_dir($viewPath)) {
            echo "✓ Admin views directory exists\n";
        } else {
            echo "❌ Admin views directory not found\n";
        }

        // Check CSS
        $cssPath = public_path('css/admin.css');
        if (file_exists($cssPath)) {
            echo "✓ Admin CSS file exists\n";
        } else {
            echo "❌ Admin CSS file not found\n";
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
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === 'setup-admin.php') {
    try {
        $setup = new AdminSetup();
        $setup->run();
    } catch (Exception $e) {
        echo "❌ Setup failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}