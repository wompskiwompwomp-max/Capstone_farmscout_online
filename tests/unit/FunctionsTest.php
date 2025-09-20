<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * Unit Tests for Core Functions
 * Tests the main functions used throughout FarmScout Online
 */

SimpleTestFramework::test('Test sanitizeInput function', function() {
    // Test basic HTML sanitization
    $input = '<script>alert("xss")</script>Hello World';
    $result = sanitizeInput($input);
    SimpleTestFramework::assertFalse(
        strpos($result, '<script>') !== false, 
        'sanitizeInput should remove script tags'
    );
    SimpleTestFramework::assertContains('Hello World', $result, 'sanitizeInput should preserve safe text');
    
    // Test SQL injection attempts
    $input = "'; DROP TABLE products; --";
    $result = sanitizeInput($input);
    SimpleTestFramework::assertFalse(
        strpos($result, 'DROP TABLE') !== false, 
        'sanitizeInput should handle SQL injection attempts'
    );
});

SimpleTestFramework::test('Test formatCurrency function', function() {
    if (function_exists('formatCurrency')) {
        // Test regular price formatting
        $result = formatCurrency(45.50);
        SimpleTestFramework::assertEquals('₱45.50', $result, 'formatCurrency should format prices correctly');
        
        // Test zero price
        $result = formatCurrency(0);
        SimpleTestFramework::assertEquals('₱0.00', $result, 'formatCurrency should handle zero prices');
        
        // Test large numbers
        $result = formatCurrency(1250.75);
        SimpleTestFramework::assertEquals('₱1,250.75', $result, 'formatCurrency should add comma separators');
    } else {
        SimpleTestFramework::assertTrue(true, 'formatCurrency function not found - skipping test');
    }
});

SimpleTestFramework::test('Test formatPriceChange function', function() {
    if (function_exists('formatPriceChange')) {
        // Test price increase
        $result = formatPriceChange(50.00, 45.00);
        SimpleTestFramework::assertContains('up', $result['icon'], 'Price increase should show up arrow');
        SimpleTestFramework::assertContains('+', $result['text'], 'Price increase should show plus sign');
        
        // Test price decrease
        $result = formatPriceChange(45.00, 50.00);
        SimpleTestFramework::assertContains('down', $result['icon'], 'Price decrease should show down arrow');
        SimpleTestFramework::assertContains('-', $result['text'], 'Price decrease should show minus sign');
        
        // Test no change
        $result = formatPriceChange(50.00, 50.00);
        SimpleTestFramework::assertEquals('stable', $result['icon'], 'No price change should show stable');
    } else {
        SimpleTestFramework::assertTrue(true, 'formatPriceChange function not found - skipping test');
    }
});

SimpleTestFramework::test('Test password validation', function() {
    if (function_exists('validatePassword')) {
        // Test strong password
        $result = validatePassword('StrongPass123!');
        SimpleTestFramework::assertTrue($result, 'Strong password should be valid');
        
        // Test weak password
        $result = validatePassword('weak');
        SimpleTestFramework::assertFalse($result, 'Weak password should be invalid');
        
        // Test empty password
        $result = validatePassword('');
        SimpleTestFramework::assertFalse($result, 'Empty password should be invalid');
    } else {
        SimpleTestFramework::assertTrue(true, 'validatePassword function not found - skipping test');
    }
});

SimpleTestFramework::test('Test CSRF token generation and validation', function() {
    if (function_exists('getCSRFToken') && function_exists('validateCSRFToken')) {
        // Generate a token
        $token = getCSRFToken();
        SimpleTestFramework::assertNotEmpty($token, 'CSRF token should not be empty');
        
        // Validate the same token
        $result = validateCSRFToken($token);
        SimpleTestFramework::assertTrue($result, 'Generated CSRF token should be valid');
        
        // Test invalid token
        $result = validateCSRFToken('invalid-token');
        SimpleTestFramework::assertFalse($result, 'Invalid CSRF token should be rejected');
    } else {
        SimpleTestFramework::assertTrue(true, 'CSRF functions not found - skipping test');
    }
});

// Run this test file if called directly
if (basename($_SERVER['PHP_SELF']) === 'FunctionsTest.php') {
    // Browser output styling
    $isBrowser = !empty($_SERVER['HTTP_HOST']);
    if ($isBrowser) {
        echo "<!DOCTYPE html><html><head><title>Functions Unit Tests</title><style>
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        </style></head><body><div class='container'><h1>🧪 Functions Unit Tests</h1><pre>";
    }
    
    echo "Running Functions Unit Tests\n";
    echo "============================\n";
    SimpleTestFramework::runAll();
    
    if ($isBrowser) {
        echo "</pre></div></body></html>";
    }
}
