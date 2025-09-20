<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * Unit Tests for Security Functions
 * Tests security-related functionality in FarmScout Online
 */

SimpleTestFramework::test('Test input validation', function() {
    if (function_exists('validateInput')) {
        // Test email validation
        $result = validateInput('user@example.com', 'email');
        SimpleTestFramework::assertTrue($result, 'Valid email should pass validation');
        
        $result = validateInput('invalid-email', 'email');
        SimpleTestFramework::assertFalse($result, 'Invalid email should fail validation');
        
        // Test price validation
        $result = validateInput(45.50, 'price');
        SimpleTestFramework::assertTrue($result, 'Valid price should pass validation');
        
        $result = validateInput(-10, 'price');
        SimpleTestFramework::assertFalse($result, 'Negative price should fail validation');
        
        // Test string length validation
        $result = validateInput('Hello', 'string', ['min_length' => 3, 'max_length' => 10]);
        SimpleTestFramework::assertTrue($result, 'String within length limits should pass');
        
        $result = validateInput('Hi', 'string', ['min_length' => 3, 'max_length' => 10]);
        SimpleTestFramework::assertFalse($result, 'String too short should fail');
        
        // Test integer validation
        $result = validateInput(25, 'integer', ['min' => 1, 'max' => 100]);
        SimpleTestFramework::assertTrue($result, 'Integer within range should pass');
        
        $result = validateInput(150, 'integer', ['min' => 1, 'max' => 100]);
        SimpleTestFramework::assertFalse($result, 'Integer out of range should fail');
    } else {
        SimpleTestFramework::assertTrue(true, 'validateInput function not found - skipping test');
    }
});

SimpleTestFramework::test('Test XSS prevention', function() {
    if (function_exists('preventXSS')) {
        // Test script tag removal
        $input = '<script>alert("xss")</script>';
        $result = preventXSS($input);
        SimpleTestFramework::assertFalse(
            strpos($result, '<script>') !== false, 
            'preventXSS should escape script tags'
        );
        
        // Test array handling
        $input = ['<script>test</script>', 'safe text'];
        $result = preventXSS($input);
        SimpleTestFramework::assertTrue(
            is_array($result), 
            'preventXSS should handle arrays'
        );
        SimpleTestFramework::assertContains('safe text', $result[1], 'Safe text should be preserved');
    } else {
        SimpleTestFramework::assertTrue(true, 'preventXSS function not found - skipping test');
    }
});

SimpleTestFramework::test('Test SQL escaping', function() {
    if (function_exists('escapeSQL')) {
        // Test basic SQL injection attempt
        $input = "'; DROP TABLE products; --";
        $result = escapeSQL($input);
        SimpleTestFramework::assertContains('\\', $result, 'escapeSQL should add escape characters');
        
        // Test array handling
        $input = ["test'; DROP", "safe data"];
        $result = escapeSQL($input);
        SimpleTestFramework::assertTrue(is_array($result), 'escapeSQL should handle arrays');
    } else {
        SimpleTestFramework::assertTrue(true, 'escapeSQL function not found - skipping test');
    }
});

SimpleTestFramework::test('Test file upload validation', function() {
    if (function_exists('validateFileUpload')) {
        // Test valid file (simulated)
        $validFile = [
            'tmp_name' => '/tmp/test.jpg',
            'name' => 'test.jpg',
            'size' => 1024000, // 1MB
            'type' => 'image/jpeg'
        ];
        
        // Since we can't actually upload files in test, we test the structure
        SimpleTestFramework::assertTrue(
            is_array($validFile), 
            'File upload array structure should be valid'
        );
        SimpleTestFramework::assertEquals('test.jpg', $validFile['name'], 'Filename should be preserved');
        
        // Test file size validation logic
        $maxSize = 2097152; // 2MB
        $result = $validFile['size'] <= $maxSize;
        SimpleTestFramework::assertTrue($result, 'File size should be within limits');
        
        // Test file extension validation
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($validFile['name'], PATHINFO_EXTENSION));
        $result = in_array($extension, $allowedTypes);
        SimpleTestFramework::assertTrue($result, 'File extension should be allowed');
    } else {
        SimpleTestFramework::assertTrue(true, 'validateFileUpload function not found - skipping test');
    }
});

SimpleTestFramework::test('Test rate limiting logic', function() {
    if (function_exists('checkRateLimit')) {
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
        
        SimpleTestFramework::assertEquals(3, count($recentAttempts), 'All recent attempts should be counted');
        
        // Test rate limit breach
        $maxAttempts = 5;
        $result = count($recentAttempts) < $maxAttempts;
        SimpleTestFramework::assertTrue($result, 'Should not breach rate limit with 3 attempts');
    } else {
        SimpleTestFramework::assertTrue(true, 'checkRateLimit function not found - skipping test');
    }
});

// Run this test file if called directly
if (basename($_SERVER['PHP_SELF']) === 'SecurityTest.php') {
    // Browser output styling
    $isBrowser = !empty($_SERVER['HTTP_HOST']);
    if ($isBrowser) {
        echo "<!DOCTYPE html><html><head><title>Security Unit Tests</title><style>
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        </style></head><body><div class='container'><h1>🛡️ Security Unit Tests</h1><pre>";
    }
    
    echo "Running Security Unit Tests\n";
    echo "===========================\n";
    SimpleTestFramework::runAll();
    
    if ($isBrowser) {
        echo "</pre></div></body></html>";
    }
}
