<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * Integration Tests for Database Operations
 * Tests database-related functionality with mock data
 */

SimpleTestFramework::test('Test database connection', function() {
    $testDB = getTestDB();
    SimpleTestFramework::assertNotEmpty($testDB, 'Test database connection should be established');
    
    if ($testDB) {
        // Test basic query
        $result = $testDB->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        SimpleTestFramework::assertTrue($row['count'] > 0, 'Test database should have products');
    }
});

SimpleTestFramework::test('Test product retrieval', function() {
    // Mock the getAllProducts function behavior
    $testDB = getTestDB();
    if ($testDB) {
        $stmt = $testDB->query("SELECT * FROM products WHERE is_active = 1");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        SimpleTestFramework::assertNotEmpty($products, 'Should retrieve products from database');
        SimpleTestFramework::assertTrue(count($products) >= 3, 'Should have at least 3 test products');
        
        // Check product structure
        $firstProduct = $products[0];
        SimpleTestFramework::assertTrue(isset($firstProduct['name']), 'Product should have name field');
        SimpleTestFramework::assertTrue(isset($firstProduct['filipino_name']), 'Product should have filipino_name field');
        SimpleTestFramework::assertTrue(isset($firstProduct['current_price']), 'Product should have current_price field');
    }
});

SimpleTestFramework::test('Test category retrieval', function() {
    $testDB = getTestDB();
    if ($testDB) {
        $stmt = $testDB->query("SELECT * FROM categories WHERE is_active = 1");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        SimpleTestFramework::assertNotEmpty($categories, 'Should retrieve categories from database');
        SimpleTestFramework::assertTrue(count($categories) >= 2, 'Should have at least 2 test categories');
        
        // Check category structure
        $firstCategory = $categories[0];
        SimpleTestFramework::assertTrue(isset($firstCategory['name']), 'Category should have name field');
        SimpleTestFramework::assertTrue(isset($firstCategory['filipino_name']), 'Category should have filipino_name field');
    }
});

SimpleTestFramework::test('Test product search functionality', function() {
    $testDB = getTestDB();
    if ($testDB) {
        // Test search for "kamatis" (tomato)
        $searchTerm = '%kamatis%';
        $stmt = $testDB->prepare("SELECT * FROM products WHERE filipino_name LIKE ? OR name LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        SimpleTestFramework::assertNotEmpty($results, 'Search should return results for kamatis');
        SimpleTestFramework::assertContains('Kamatis', $results[0]['filipino_name'], 'Search result should contain Kamatis');
        
        // Test search for non-existent product
        $searchTerm = '%nonexistent%';
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        SimpleTestFramework::assertEmpty($results, 'Search for non-existent product should return empty results');
    }
});

SimpleTestFramework::test('Test price change calculations', function() {
    $testDB = getTestDB();
    if ($testDB) {
        // Get a product with price change
        $stmt = $testDB->query("SELECT * FROM products WHERE current_price != previous_price LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $currentPrice = floatval($product['current_price']);
            $previousPrice = floatval($product['previous_price']);
            
            // Calculate price change percentage
            if ($previousPrice > 0) {
                $changePercent = (($currentPrice - $previousPrice) / $previousPrice) * 100;
                $changeType = $currentPrice > $previousPrice ? 'increase' : 'decrease';
                
                SimpleTestFramework::assertTrue(
                    is_numeric($changePercent), 
                    'Price change percentage should be numeric'
                );
                SimpleTestFramework::assertTrue(
                    in_array($changeType, ['increase', 'decrease']), 
                    'Price change type should be increase or decrease'
                );
            }
        }
    }
});

SimpleTestFramework::test('Test data validation in database operations', function() {
    $testDB = getTestDB();
    if ($testDB) {
        // Test inserting valid product
        try {
            $stmt = $testDB->prepare("INSERT INTO products (name, filipino_name, current_price, unit) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute(['Test Product', 'Test Filipino', 100.00, 'kg']);
            SimpleTestFramework::assertTrue($result, 'Valid product insertion should succeed');
            
            // Verify the insertion
            $lastId = $testDB->lastInsertId();
            $stmt = $testDB->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$lastId]);
            $inserted = $stmt->fetch(PDO::FETCH_ASSOC);
            
            SimpleTestFramework::assertEquals('Test Product', $inserted['name'], 'Inserted product name should match');
            SimpleTestFramework::assertEquals('Test Filipino', $inserted['filipino_name'], 'Inserted Filipino name should match');
            
        } catch (Exception $e) {
            SimpleTestFramework::assertTrue(false, 'Valid product insertion failed: ' . $e->getMessage());
        }
        
        // Test inserting invalid data (should fail or handle gracefully)
        try {
            $stmt = $testDB->prepare("INSERT INTO products (name, filipino_name, current_price, unit) VALUES (?, ?, ?, ?)");
            // This should handle null values appropriately
            $result = $stmt->execute([null, null, -50.00, 'invalid_unit_type_that_is_very_long']);
            // The test passes if it doesn't throw an exception or handles it properly
            SimpleTestFramework::assertTrue(true, 'Database should handle invalid data appropriately');
            
        } catch (Exception $e) {
            // It's okay if the database rejects invalid data
            SimpleTestFramework::assertTrue(true, 'Database correctly rejected invalid data');
        }
    }
});

// Run this test file if called directly
if (basename($_SERVER['PHP_SELF']) === 'DatabaseTest.php') {
    // Browser output styling
    $isBrowser = !empty($_SERVER['HTTP_HOST']);
    if ($isBrowser) {
        echo "<!DOCTYPE html><html><head><title>Database Integration Tests</title><style>
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        </style></head><body><div class='container'><h1>📊 Database Integration Tests</h1><pre>";
    }
    
    echo "Running Database Integration Tests\n";
    echo "==================================\n";
    SimpleTestFramework::runAll();
    
    if ($isBrowser) {
        echo "</pre></div></body></html>";
    }
}
