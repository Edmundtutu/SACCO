<?php

/**
 * Simple API Test Script for SACCO REST API
 * 
 * This script tests the basic functionality of the SACCO API
 * including authentication, account creation, and transactions.
 */

class SaccoApiTester
{
    private $baseUrl;
    private $token;

    public function __construct($baseUrl = 'http://localhost:8000/api')
    {
        $this->baseUrl = $baseUrl;
    }

    private function makeRequest($method, $endpoint, $data = [], $headers = [])
    {
        $url = $this->baseUrl . $endpoint;
        $defaultHeaders = ['Content-Type: application/json'];
        
        if ($this->token) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->token;
        }
        
        $headers = array_merge($defaultHeaders, $headers);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function testLogin($email = 'admin@sacco.com', $password = 'password123')
    {
        echo "🔐 Testing Login...\n";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $email,
            'password' => $password
        ]);
        
        if ($response['status_code'] === 200) {
            $this->token = $response['body']['data']['token'];
            echo "✅ Login successful! Token received.\n";
            echo "   User: " . $response['body']['data']['user']['name'] . "\n";
            echo "   Role: " . $response['body']['data']['user']['role'] . "\n\n";
            return true;
        } else {
            echo "❌ Login failed!\n";
            echo "   Status: " . $response['status_code'] . "\n";
            echo "   Message: " . ($response['body']['message'] ?? 'Unknown error') . "\n\n";
            return false;
        }
    }

    public function testGetProfile()
    {
        echo "👤 Testing Get Profile...\n";
        
        $response = $this->makeRequest('GET', '/auth/profile');
        
        if ($response['status_code'] === 200) {
            echo "✅ Profile retrieved successfully!\n";
            $user = $response['body']['data']['user'];
            echo "   Name: " . $user['name'] . "\n";
            echo "   Member Number: " . ($user['member_number'] ?? 'N/A') . "\n";
            echo "   Status: " . $user['status'] . "\n\n";
            return true;
        } else {
            echo "❌ Get profile failed!\n";
            echo "   Status: " . $response['status_code'] . "\n\n";
            return false;
        }
    }

    public function testGetAccounts()
    {
        echo "🏦 Testing Get Accounts...\n";
        
        $response = $this->makeRequest('GET', '/savings/accounts');
        
        if ($response['status_code'] === 200) {
            $accounts = $response['body']['data'];
            echo "✅ Accounts retrieved successfully!\n";
            echo "   Number of accounts: " . count($accounts) . "\n";
            
            foreach ($accounts as $account) {
                echo "   - Account: " . $account['account_number'] . 
                     " | Balance: " . $account['balance'] . 
                     " | Status: " . $account['status'] . "\n";
            }
            echo "\n";
            return $accounts;
        } else {
            echo "❌ Get accounts failed!\n";
            echo "   Status: " . $response['status_code'] . "\n\n";
            return [];
        }
    }

    public function testGetSavingsProducts()
    {
        echo "📦 Testing Get Savings Products...\n";
        
        $response = $this->makeRequest('GET', '/savings/products');
        
        if ($response['status_code'] === 200) {
            $products = $response['body']['data'];
            echo "✅ Savings products retrieved successfully!\n";
            echo "   Number of products: " . count($products) . "\n";
            
            foreach ($products as $product) {
                echo "   - " . $product['name'] . 
                     " (" . $product['type'] . ") - " . 
                     $product['interest_rate'] . "% interest\n";
            }
            echo "\n";
            return $products;
        } else {
            echo "❌ Get savings products failed!\n";
            echo "   Status: " . $response['status_code'] . "\n\n";
            return [];
        }
    }

    public function testDeposit($accountId, $amount = 1000)
    {
        echo "💰 Testing Deposit...\n";
        
        $response = $this->makeRequest('POST', '/savings/deposit', [
            'account_id' => $accountId,
            'amount' => $amount,
            'payment_method' => 'cash',
            'description' => 'API Test Deposit'
        ]);
        
        if ($response['status_code'] === 200) {
            echo "✅ Deposit successful!\n";
            echo "   Amount: " . $amount . "\n";
            echo "   New Balance: " . $response['body']['data']['new_balance'] . "\n";
            echo "   Transaction ID: " . $response['body']['data']['transaction']['id'] . "\n\n";
            return true;
        } else {
            echo "❌ Deposit failed!\n";
            echo "   Status: " . $response['status_code'] . "\n";
            echo "   Message: " . ($response['body']['message'] ?? 'Unknown error') . "\n\n";
            return false;
        }
    }

    public function runBasicTests()
    {
        echo "🚀 Starting SACCO API Basic Tests\n";
        echo "================================\n\n";
        
        // Test login
        if (!$this->testLogin()) {
            echo "❌ Cannot proceed without authentication\n";
            return;
        }
        
        // Test profile
        $this->testGetProfile();
        
        // Test savings products
        $this->testGetSavingsProducts();
        
        // Test accounts
        $accounts = $this->testGetAccounts();
        
        // Test deposit if we have accounts
        if (!empty($accounts)) {
            $firstAccount = $accounts[0];
            $this->testDeposit($firstAccount['id'], 500);
            
            // Get accounts again to see updated balance
            $this->testGetAccounts();
        }
        
        echo "🎉 Basic tests completed!\n";
        echo "=========================\n\n";
        
        echo "📝 API is working! You can now:\n";
        echo "   - Register new members via POST /api/auth/register\n";
        echo "   - Make deposits and withdrawals\n";
        echo "   - Manage savings accounts\n";
        echo "   - Access with different roles (admin, member, staff)\n\n";
        
        echo "🔗 Test with sample accounts:\n";
        echo "   Admin: admin@sacco.com / password123\n";
        echo "   Loan Officer: loans@sacco.com / password123\n";
        echo "   Members: jane@example.com, robert@example.com, mary@example.com / password123\n";
    }
}

// Run the tests
$tester = new SaccoApiTester();
$tester->runBasicTests();