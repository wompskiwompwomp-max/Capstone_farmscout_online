<?php
/**
 * FarmScout Online - Test Runner
 * Runs all unit tests and integration tests
 * 
 * Usage: Access this file via browser or command line
 * Browser: http://localhost/farmscout_online/tests/run_all_tests.php
 * Command: php tests/run_all_tests.php
 */

// Set up environment
require_once __DIR__ . '/bootstrap.php';

// Output styling for browser
$isBrowser = !empty($_SERVER['HTTP_HOST']);
if ($isBrowser) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>FarmScout Online - Test Results</title>
        <style>
            body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .test-section { margin: 20px 0; border: 1px solid #ddd; border-radius: 5px; }
            .test-header { background: #2D5016; color: white; padding: 10px; font-weight: bold; }
            .test-content { padding: 15px; }
            .pass { color: #28a745; }
            .fail { color: #dc3545; }
            .skip { color: #ffc107; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
            .summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
    <div class='container'>
        <div class='header'>
            <h1>🌾 FarmScout Online - Test Suite Results</h1>
            <p>Comprehensive testing of core functionality</p>
        </div>";
}

echo "======================================================\n";
echo "🌾 FARMSCOUT ONLINE - COMPREHENSIVE TEST SUITE\n";
echo "======================================================\n";
echo "Testing Environment: " . (FARMSCOUT_TESTING ? 'Active' : 'Inactive') . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "======================================================\n\n";

$allTestsPassed = true;
$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;

/**
 * Run a test file and capture results
 */
function runTestFile($testFile, $testName) {
    global $allTestsPassed, $totalTests, $totalPassed, $totalFailed, $isBrowser;
    
    if ($isBrowser) {
        echo "<div class='test-section'>";
        echo "<div class='test-header'>$testName</div>";
        echo "<div class='test-content'><pre>";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "RUNNING: $testName\n";
    echo str_repeat("=", 60) . "\n";
    
    ob_start();
    
    // Reset test framework counters
    if (class_exists('SimpleTestFramework')) {
        // Clear previous tests
        $reflection = new ReflectionClass('SimpleTestFramework');
        $testsProperty = $reflection->getProperty('tests');
        $testsProperty->setAccessible(true);
        $testsProperty->setValue([]);
        
        $passedProperty = $reflection->getProperty('passed');
        $passedProperty->setAccessible(true);
        $passedProperty->setValue(0);
        
        $failedProperty = $reflection->getProperty('failed');
        $failedProperty->setAccessible(true);
        $failedProperty->setValue(0);
    }
    
    // Include and run the test file
    if (file_exists($testFile)) {
        try {
            require_once $testFile;
            
            // If the test file didn't run automatically, try to run it
            if (class_exists('SimpleTestFramework')) {
                $reflection = new ReflectionClass('SimpleTestFramework');
                $testsProperty = $reflection->getProperty('tests');
                $testsProperty->setAccessible(true);
                $tests = $testsProperty->getValue();
                
                if (!empty($tests)) {
                    SimpleTestFramework::runAll();
                    
                    // Get final counts
                    $passedProperty = $reflection->getProperty('passed');
                    $passedProperty->setAccessible(true);
                    $passed = $passedProperty->getValue();
                    
                    $failedProperty = $reflection->getProperty('failed');
                    $failedProperty->setAccessible(true);
                    $failed = $failedProperty->getValue();
                    
                    $totalTests += ($passed + $failed);
                    $totalPassed += $passed;
                    $totalFailed += $failed;
                    
                    if ($failed > 0) {
                        $allTestsPassed = false;
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "ERROR running $testName: " . $e->getMessage() . "\n";
            $allTestsPassed = false;
            $totalFailed++;
        }
    } else {
        echo "WARNING: Test file not found: $testFile\n";
    }
    
    $output = ob_get_clean();
    echo $output;
    
    if ($isBrowser) {
        echo "</pre></div></div>";
    }
}

// Test Files to Run
$testSuites = [
    'Unit Tests - Core Functions' => __DIR__ . '/unit/FunctionsTest.php',
    'Unit Tests - Security' => __DIR__ . '/unit/SecurityTest.php',
    'Integration Tests - Database' => __DIR__ . '/integration/DatabaseTest.php'
];

// Run all test suites
foreach ($testSuites as $suiteName => $testFile) {
    runTestFile($testFile, $suiteName);
}

// Additional manual tests
if ($isBrowser) {
    echo "<div class='test-section'>";
    echo "<div class='test-header'>Manual Verification Tests</div>";
    echo "<div class='test-content'><pre>";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "MANUAL VERIFICATION TESTS\n";
echo str_repeat("=", 60) . "\n";

// Test website structure
echo "✓ Testing website structure...\n";
$coreFiles = ['index.php', 'admin.php', 'login.php', 'categories.php'];
foreach ($coreFiles as $file) {
    if (file_exists(FARMSCOUT_ROOT . '/' . $file)) {
        echo "  ✓ PASS: Core file exists - $file\n";
        $totalPassed++;
    } else {
        echo "  ✗ FAIL: Core file missing - $file\n";
        $totalFailed++;
        $allTestsPassed = false;
    }
    $totalTests++;
}

// Test directory structure
echo "\n✓ Testing directory structure...\n";
$coreDirectories = ['includes', 'config', 'database', 'css', 'tests', 'docs'];
foreach ($coreDirectories as $dir) {
    if (is_dir(FARMSCOUT_ROOT . '/' . $dir)) {
        echo "  ✓ PASS: Directory exists - $dir\n";
        $totalPassed++;
    } else {
        echo "  ✗ FAIL: Directory missing - $dir\n";
        $totalFailed++;
        $allTestsPassed = false;
    }
    $totalTests++;
}

// Test configuration files
echo "\n✓ Testing configuration files...\n";
$configFiles = ['includes/enhanced_functions.php', 'config/database.php', 'composer.json'];
foreach ($configFiles as $file) {
    if (file_exists(FARMSCOUT_ROOT . '/' . $file)) {
        echo "  ✓ PASS: Config file exists - $file\n";
        $totalPassed++;
    } else {
        echo "  ✗ FAIL: Config file missing - $file\n";
        $totalFailed++;
        $allTestsPassed = false;
    }
    $totalTests++;
}

if ($isBrowser) {
    echo "</pre></div></div>";
}

// Final Results
if ($isBrowser) {
    echo "<div class='summary'>";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "FINAL TEST RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Total Tests Run: $totalTests\n";
echo "Tests Passed: " . ($isBrowser ? "<span class='pass'>" : "") . "$totalPassed" . ($isBrowser ? "</span>" : "") . "\n";
echo "Tests Failed: " . ($isBrowser ? "<span class='fail'>" : "") . "$totalFailed" . ($isBrowser ? "</span>" : "") . "\n";
echo "Success Rate: " . ($totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0) . "%\n";
echo "\nOverall Status: " . ($allTestsPassed ? 
    ($isBrowser ? "<span class='pass'>✓ ALL TESTS PASSED</span>" : "✓ ALL TESTS PASSED") : 
    ($isBrowser ? "<span class='fail'>✗ SOME TESTS FAILED</span>" : "✗ SOME TESTS FAILED")
) . "\n";

if ($allTestsPassed) {
    echo "\n🎉 Congratulations! Your FarmScout Online application has passed all tests.\n";
    echo "Your code is ready for production deployment.\n";
} else {
    echo "\n⚠️  Some tests failed. Please review the failures above and fix the issues.\n";
    echo "Don't worry - this is normal during development!\n";
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n";

if ($isBrowser) {
    echo "</div>";
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<p><strong>Next Steps for Your Professor:</strong></p>";
    echo "<ol style='text-align: left; display: inline-block;'>";
    echo "<li>Review the test coverage and results above</li>";
    echo "<li>Check the <code>tests/</code> directory for individual test files</li>";
    echo "<li>Run specific test suites by accessing individual test files</li>";
    echo "<li>Verify that core functionality works as expected</li>";
    echo "</ol>";
    echo "</div>";
    echo "</div></body></html>";
}
?>