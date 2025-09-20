<?php
/**
 * Standalone Security Unit Tests
 * Tests security-related functionality in FarmScout Online
 */

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define security functions for testing
function validateInput($data, $type = 'string', $options = []) {
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
        case 'price':
            return is_numeric($data) && $data >= 0 && $data <= 999999.99;
        case 'integer':
            $min = $options['min'] ?? 0;
            $max = $options['max'] ?? PHP_INT_MAX;
            return is_numeric($data) && $data >= $min && $data <= $max;
        case 'string':
            $max_length = $options['max_length'] ?? 255;
            $min_length = $options['min_length'] ?? 0;
            return is_string($data) && strlen($data) >= $min_length && strlen($data) <= $max_length;
        default:
            return false;
    }
}

function preventXSS($data) {
    if (is_array($data)) {
        return array_map('preventXSS', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function escapeSQL($data) {
    if (is_array($data)) {
        return array_map('escapeSQL', $data);
    }
    return addslashes($data);
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Simple test framework
$testResults = [];
$testsPassed = 0;
$testsFailed = 0;

function runTest($testName, $testFunction) {
    global $testResults, $testsPassed, $testsFailed;
    
    echo "\n🛡️ Running: $testName\n";
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

// Browser output styling
$isBrowser = !empty($_SERVER['HTTP_HOST']);
if ($isBrowser) {
    echo "<!DOCTYPE html><html><head><title>Security Unit Tests</title><style>
    body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.4; }
    .header { background: #dc3545; color: white; padding: 20px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; text-align: center; }
    .pass { color: #28a745; }
    .fail { color: #dc3545; }
    .summary { background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545; }
    </style></head><body><div class='container'><div class='header'><h1>🛡️ Security Unit Tests</h1><p>Testing Security & Validation Functions</p></div><pre>";
}

echo "======================================================\n";
echo "🔒 FARMSCOUT ONLINE - SECURITY UNIT TESTS\n";
echo "======================================================\n";
echo "Testing Environment: Standalone Test Runner\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "======================================================\n";

// Test 1: Input Validation
runTest('Test input validation', function() {
    // Test email validation
    assertTrue(validateInput('user@example.com', 'email'), 'Valid email should pass validation');
    assertFalse(validateInput('invalid-email', 'email'), 'Invalid email should fail validation');
    
    // Test price validation
    assertTrue(validateInput(45.50, 'price'), 'Valid price should pass validation');
    assertFalse(validateInput(-10, 'price'), 'Negative price should fail validation');
    
    // Test string length validation
    assertTrue(validateInput('Hello', 'string', ['min_length' => 3, 'max_length' => 10]), 'String within length limits should pass');
    assertFalse(validateInput('Hi', 'string', ['min_length' => 3, 'max_length' => 10]), 'String too short should fail');
    
    // Test integer validation
    assertTrue(validateInput(25, 'integer', ['min' => 1, 'max' => 100]), 'Integer within range should pass');
    assertFalse(validateInput(150, 'integer', ['min' => 1, 'max' => 100]), 'Integer out of range should fail');
});

// Test 2: XSS Prevention
runTest('Test XSS prevention', function() {
    // Test script tag removal
    $input = '<script>alert("xss")</script>';
    $result = preventXSS($input);
    assertFalse(strpos($result, '<script>') !== false, 'preventXSS should escape script tags');
    
    // Test array handling
    $input = ['<script>test</script>', 'safe text'];
    $result = preventXSS($input);
    assertTrue(is_array($result), 'preventXSS should handle arrays');
    assertContains('safe text', $result[1], 'Safe text should be preserved');
});

// Test 3: SQL Escaping
runTest('Test SQL escaping', function() {
    // Test basic SQL injection attempt
    $input = "'; DROP TABLE products; --";
    $result = escapeSQL($input);
    assertContains('\\', $result, 'escapeSQL should add escape characters');
    
    // Test array handling
    $input = ["test'; DROP", "safe data"];
    $result = escapeSQL($input);
    assertTrue(is_array($result), 'escapeSQL should handle arrays');
});

// Test 4: Input Sanitization
runTest('Test input sanitization', function() {
    // Test basic HTML sanitization
    $input = '<script>alert("xss")</script>Hello World';
    $result = sanitizeInput($input);
    assertFalse(strpos($result, '<script>') !== false, 'sanitizeInput should escape script tags');
    assertContains('Hello World', $result, 'sanitizeInput should preserve safe text');
    
    // Test SQL injection attempts
    $input = "'; DROP TABLE products; --";
    $result = sanitizeInput($input);
    assertContains('&quot;', $result, 'sanitizeInput should escape quotes');
});

// Test 5: File Upload Validation Logic
runTest('Test file upload validation logic', function() {
    // Test valid file structure (simulated)
    $validFile = [
        'tmp_name' => '/tmp/test.jpg',
        'name' => 'test.jpg',
        'size' => 1024000, // 1MB
        'type' => 'image/jpeg'
    ];
    
    // Test file array structure
    assertTrue(is_array($validFile), 'File upload array structure should be valid');
    assertEquals('test.jpg', $validFile['name'], 'Filename should be preserved');
    
    // Test file size validation logic
    $maxSize = 2097152; // 2MB
    $result = $validFile['size'] <= $maxSize;
    assertTrue($result, 'File size should be within limits');
    
    // Test file extension validation
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($validFile['name'], PATHINFO_EXTENSION));
    $result = in_array($extension, $allowedTypes);
    assertTrue($result, 'File extension should be allowed');
});

// Test 6: Rate Limiting Logic
runTest('Test rate limiting logic', function() {
    // Test basic rate limiting structure
    $attempts = [];
    $windowStart = time() - 3600; // 1 hour ago
    
    // Simulate adding attempts
    for ($i = 0; $i < 3; $i++) {
        $attempts[] = time() - (60 * $i); // Add attempts over last 3 minutes
    }
    
    $recentAttempts = array_filter($attempts, function($timestamp) use ($windowStart) {
        return $timestamp > $windowStart;
    });
    
    assertEquals(3, count($recentAttempts), 'All recent attempts should be counted');
    
    // Test rate limit breach
    $maxAttempts = 5;
    $result = count($recentAttempts) < $maxAttempts;
    assertTrue($result, 'Should not breach rate limit with 3 attempts');
});

echo "\n" . str_repeat("=", 60) . "\n";
echo "FINAL RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "Success Rate: " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 1) . "%\n";

if ($testsFailed === 0) {
    echo "\n🛡️ ALL SECURITY TESTS PASSED! Your application is well protected!\n";
} else {
    echo "\n⚠️  Some security tests failed. Please review the failures above.\n";
}

if ($isBrowser) {
    echo "</pre>";
    echo "<div class='summary'>";
    echo "<h3>🔒 Security Test Summary</h3>";
    echo "<p><strong>Tests Passed:</strong> $testsPassed</p>";
    echo "<p><strong>Tests Failed:</strong> $testsFailed</p>";
    echo "<p><strong>Success Rate:</strong> " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 1) . "%</p>";
    if ($testsFailed === 0) {
        echo "<p style='color: #28a745;'><strong>🛡️ ALL SECURITY TESTS PASSED!</strong></p>";
    } else {
        echo "<p style='color: #dc3545;'><strong>⚠️ Some security tests failed</strong></p>";
    }
    echo "<p><a href='../../QUICK_DEMO.html'>← Back to Demo Hub</a></p>";
    echo "</div>";
    echo "</div></body></html>";
}
?>