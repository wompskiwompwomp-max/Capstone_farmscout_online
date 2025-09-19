<?php
require_once 'includes/enhanced_functions.php';

echo "=== Fixing Price Alerts Table ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

try {
    // Check if last_sent column exists
    $check_query = "SHOW COLUMNS FROM price_alerts LIKE 'last_sent'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->rowCount() == 0) {
        echo "Adding last_sent column...\n";
        $conn->exec("ALTER TABLE price_alerts ADD COLUMN last_sent TIMESTAMP NULL AFTER updated_at");
        echo "✅ last_sent column added successfully!\n";
    } else {
        echo "✅ last_sent column already exists!\n";
    }
    
    // Also add index for better performance
    echo "Adding index for last_sent column...\n";
    try {
        $conn->exec("ALTER TABLE price_alerts ADD INDEX idx_last_sent (last_sent)");
        echo "✅ Index added successfully!\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✅ Index already exists!\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n=== Table Structure Fixed ===\n";
    echo "The price alerts system is now ready to use!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>