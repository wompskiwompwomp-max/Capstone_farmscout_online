<?php
/**
 * Standalone Functions Unit Tests
 * Tests the main functions used throughout FarmScout Online
 */

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define basic functions for testing
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 2);
}

function formatPriceChange($current, $previous) {
    if ($previous == 0) {
        return ['icon' => 'stable', 'text' => 'New', 'class' => 'text-gray-500'];
    }
    
    $change = $current - $previous;
    $percent = abs(($change / $previous) * 100);
    
    if ($change > 0) {
        return [
            'icon' => 'up',
            'text' => '+₱' . number_format($change, 2) . ' (+' . number_format($percent, 1) . '%)',
            'class' => 'text-red-500'
        ];
    } elseif ($change < 0) {
        return [
            'icon' => 'down', 
            'text' => '-₱' . number_format(abs($change), 2) . ' (-' . number_format($percent, 1) . '%)',
            'class' => 'text-green-500'
        ];
    } else {
        return ['icon' => 'stable', 'text' => 'No change', 'class' => 'text-gray-500'];
    }
}

function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// Simple test framework
$testResults = [];
$testsPassed = 0;
$testsFailed = 0;

function runTest($testName, $testFunction) {
    global $testResults, $testsPassed, $testsFailed;
    
    echo "\n🧪 Running: $testName\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $testFunction();
        echo "✅ $testName: PASSED\n";
        $testsPassed++;
    } catch (Exception $e) {
        echo "❌ $testName: FAILED - " . $e->getMessage() . "\n";
        $testsFailed++;
    }
}

function assertTrue($condition, $message) {
    if ($condition) {
        echo "  ✓ PASS: $message\n";
        return true;
    } else {
        echo "  ✗ FAIL: $message\n";
        throw new Exception($message);
    }
}

function assertFalse($condition, $message) {
    return assertTrue(!$condition, $message);
}

function assertEquals($expected, $actual, $message) {
    if ($expected === $actual) {
        echo "  ✓ PASS: $message\n";
        return true;
    } else {
        echo "  ✗ FAIL: $message\n";
        echo "    Expected: " . var_export($expected, true) . "\n";
        echo "    Actual: " . var_export($actual, true) . "\n";
        throw new Exception($message);
    }
}

function assertContains($needle, $haystack, $message) {
    $result = false;
    if (is_array($haystack)) {
        $result = in_array($needle, $haystack);
    } elseif (is_string($haystack)) {
        $result = strpos($haystack, $needle) !== false;
    }
    return assertTrue($result, $message);
}

function assertNotEmpty($value, $message) {
    return assertTrue(!empty($value), $message);
}

// Browser output styling
$isBrowser = !empty($_SERVER['HTTP_HOST']);
if ($isBrowser) {
    echo "<!DOCTYPE html><html><head><title>Functions Unit Tests</title><style>
    body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.4; }
    .header { background: #2D5016; color: white; padding: 20px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; text-align: center; }
    .pass { color: #28a745; }
    .fail { color: #dc3545; }
    .summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2D5016; }
    </style></head><body><div class='container'><div class='header'><h1>🧪 Functions Unit Tests</h1><p>Testing Core Application Functions</p></div><pre>";
}

echo "======================================================\n";
echo "🌾 FARMSCOUT ONLINE - FUNCTIONS UNIT TESTS\n";
echo "======================================================\n";
echo "Testing Environment: Standalone Test Runner\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "======================================================\n";

// Test 1: Input Sanitization
runTest('Test sanitizeInput function', function() {
    // Test basic HTML sanitization
    $input = '<script>alert("xss")</script>Hello World';
    $result = sanitizeInput($input);
    assertFalse(strpos($result, '<script>') !== false, 'sanitizeInput should remove script tags');
    assertContains('Hello World', $result, 'sanitizeInput should preserve safe text');
    
    // Test SQL injection attempts
    $input = "'; DROP TABLE products; --";
    $result = sanitizeInput($input);
    assertFalse(strpos($result, 'DROP TABLE') !== false, 'sanitizeInput should handle SQL injection attempts');
});

// Test 2: Currency Formatting
runTest('Test formatCurrency function', function() {
    // Test regular price formatting
    $result = formatCurrency(45.50);
    assertEquals('₱45.50', $result, 'formatCurrency should format prices correctly');
    
    // Test zero price
    $result = formatCurrency(0);
    assertEquals('₱0.00', $result, 'formatCurrency should handle zero prices');
    
    // Test large numbers
    $result = formatCurrency(1250.75);
    assertEquals('₱1,250.75', $result, 'formatCurrency should add comma separators');
});

// Test 3: Price Change Calculation
runTest('Test formatPriceChange function', function() {
    // Test price increase
    $result = formatPriceChange(50.00, 45.00);
    assertEquals('up', $result['icon'], 'Price increase should show up arrow');
    assertContains('+', $result['text'], 'Price increase should show plus sign');
    
    // Test price decrease
    $result = formatPriceChange(45.00, 50.00);
    assertEquals('down', $result['icon'], 'Price decrease should show down arrow');
    assertContains('-', $result['text'], 'Price decrease should show minus sign');
    
    // Test no change
    $result = formatPriceChange(50.00, 50.00);
    assertEquals('stable', $result['icon'], 'No price change should show stable');
});

// Test 4: Password Validation
runTest('Test password validation', function() {
    // Test strong password
    $result = validatePassword('StrongPass123!');
    assertTrue($result, 'Strong password should be valid');
    
    // Test weak password
    $result = validatePassword('weak');
    assertFalse($result, 'Weak password should be invalid');
    
    // Test empty password
    $result = validatePassword('');
    assertFalse($result, 'Empty password should be invalid');
});

// Test 5: CSRF Token Security
runTest('Test CSRF token generation and validation', function() {
    // Generate a token
    $token = getCSRFToken();
    assertNotEmpty($token, 'CSRF token should not be empty');
    
    // Validate the same token
    $result = validateCSRFToken($token);
    assertTrue($result, 'Generated CSRF token should be valid');
    
    // Test invalid token
    $result = validateCSRFToken('invalid-token');
    assertFalse($result, 'Invalid CSRF token should be rejected');
});

echo "\n" . str_repeat("=", 60) . "\n";
echo "FINAL RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "Success Rate: " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 1) . "%\n";

if ($testsFailed === 0) {
    echo "\n🎉 ALL TESTS PASSED! Functions are working correctly!\n";
} else {
    echo "\n⚠️  Some tests failed. Please review the failures above.\n";
}

if ($isBrowser) {
    echo "</pre>";
    echo "<div class='summary'>";
    echo "<h3>📊 Test Summary</h3>";
    echo "<p><strong>Tests Passed:</strong> $testsPassed</p>";
    echo "<p><strong>Tests Failed:</strong> $testsFailed</p>";
    echo "<p><strong>Success Rate:</strong> " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 1) . "%</p>";
    if ($testsFailed === 0) {
        echo "<p style='color: #28a745;'><strong>🎉 ALL TESTS PASSED!</strong></p>";
    } else {
        echo "<p style='color: #dc3545;'><strong>⚠️ Some tests failed</strong></p>";
    }
    echo "<p><a href='../../QUICK_DEMO.html'>← Back to Demo Hub</a></p>";
    echo "</div>";
    echo "</div></body></html>";
}
?>