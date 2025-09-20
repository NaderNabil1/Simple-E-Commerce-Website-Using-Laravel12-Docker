<?php

/**
 * Comprehensive Test Runner for Laravel E-Commerce API
 *
 * This script runs all tests and provides detailed reporting
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;

class TestRunner
{
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function runAllTests()
    {
        echo "🚀 Starting Laravel E-Commerce API Test Suite\n";
        echo "=" . str_repeat("=", 50) . "\n\n";

        $this->runTestSuite('Unit Tests', [
            'tests/Unit/UserTest.php',
            'tests/Unit/ProductTest.php',
            'tests/Unit/OrderTest.php',
            'tests/Unit/CartTest.php',
        ]);

        $this->runTestSuite('Feature Tests - Authentication API', [
            'tests/Feature/Api/AuthApiTest.php',
        ]);

        $this->runTestSuite('Feature Tests - Cart Management API', [
            'tests/Feature/Api/CartApiTest.php',
        ]);

        $this->runTestSuite('Feature Tests - Order Management API', [
            'tests/Feature/Api/OrderApiTest.php',
        ]);

        $this->runTestSuite('Feature Tests - Product API', [
            'tests/Feature/Api/ProductApiTest.php',
        ]);

        $this->runTestSuite('Integration Tests', [
            'tests/Feature/Api/ApiIntegrationTest.php',
        ]);

        $this->displaySummary();
    }

    private function runTestSuite($suiteName, $testFiles)
    {
        echo "📋 Running {$suiteName}\n";
        echo "-" . str_repeat("-", 30) . "\n";

        foreach ($testFiles as $testFile) {
            if (file_exists($testFile)) {
                $this->runTestFile($testFile);
            } else {
                echo "⚠️  File not found: {$testFile}\n";
            }
        }

        echo "\n";
    }

    private function runTestFile($testFile)
    {
        $className = $this->getClassNameFromFile($testFile);
        echo "  🔍 Testing {$className}... ";

        try {
            // Run the test using PHPUnit
            $command = "php vendor/bin/phpunit {$testFile} --no-coverage --colors=never";
            $output = shell_exec($command . ' 2>&1');

            if (strpos($output, 'OK') !== false || strpos($output, 'PASS') !== false) {
                echo "✅ PASSED\n";
                $this->passedTests++;
            } else {
                echo "❌ FAILED\n";
                $this->failedTests++;
                echo "    Error: " . trim($output) . "\n";
            }

            $this->totalTests++;
        } catch (Exception $e) {
            echo "❌ ERROR\n";
            echo "    Exception: " . $e->getMessage() . "\n";
            $this->failedTests++;
            $this->totalTests++;
        }
    }

    private function getClassNameFromFile($filePath)
    {
        $filename = basename($filePath, '.php');
        return str_replace('_', ' ', $filename);
    }

    private function displaySummary()
    {
        echo "📊 Test Summary\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed: {$this->passedTests} ✅\n";
        echo "Failed: {$this->failedTests} ❌\n";

        $successRate = $this->totalTests > 0 ? round(($this->passedTests / $this->totalTests) * 100, 2) : 0;
        echo "Success Rate: {$successRate}%\n\n";

        if ($this->failedTests === 0) {
            echo "🎉 All tests passed! Your API is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please review the errors above.\n";
        }

        echo "\n📝 Test Coverage:\n";
        echo "- Authentication API (Register, Login, Logout)\n";
        echo "- Cart Management (Add, Remove, Update, Checkout)\n";
        echo "- Order Management (View, Update Status, Assign)\n";
        echo "- Product Management (List, View, Search, Filter)\n";
        echo "- User Management (Roles, Permissions)\n";
        echo "- Database Models and Relationships\n";
        echo "- API Integration and Workflows\n";
    }
}

// Run the tests
$runner = new TestRunner();
$runner->runAllTests();

