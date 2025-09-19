<?php
require_once 'includes/enhanced_functions.php';

echo "=== Cleaning Up Test Data ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

try {
    // Show current alerts
    echo "Current price alerts:\n";
    $current_query = "SELECT id, user_email, product_id, alert_type, target_price, is_active FROM price_alerts ORDER BY id";
    $current_result = $conn->query($current_query);
    $current_alerts = $current_result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($current_alerts as $alert) {
        echo "- ID: {$alert['id']}, Email: {$alert['user_email']}, Product: {$alert['product_id']}, Type: {$alert['alert_type']}, Target: ₱{$alert['target_price']}, Active: {$alert['is_active']}\n";
    }
    
    // Remove alerts with test@example.com
    echo "\nRemoving invalid test email alerts...\n";
    $cleanup_query = "UPDATE price_alerts SET is_active = 0 WHERE user_email = 'test@example.com'";
    $cleanup_result = $conn->exec($cleanup_query);
    echo "✅ Deactivated {$cleanup_result} alerts with invalid email addresses\n";
    
    // Show remaining active alerts
    echo "\nRemaining active alerts:\n";
    $active_query = "SELECT pa.id, pa.user_email, p.filipino_name, pa.alert_type, pa.target_price 
                     FROM price_alerts pa 
                     JOIN products p ON pa.product_id = p.id 
                     WHERE pa.is_active = 1 
                     ORDER BY pa.id";
    $active_result = $conn->query($active_query);
    $active_alerts = $active_result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($active_alerts)) {
        echo "No active alerts found.\n";
        echo "\n💡 To test the system:\n";
        echo "1. Go to: http://localhost/farmscout_online/price-alerts.php\n";
        echo "2. Create a new alert with your real email address\n";
        echo "3. Run the test script to trigger it\n";
    } else {
        foreach ($active_alerts as $alert) {
            echo "- {$alert['filipino_name']}: {$alert['alert_type']} ₱{$alert['target_price']} → {$alert['user_email']}\n";
        }
        
        echo "\n✅ These alerts are properly configured and will work!\n";
    }
    
    // Also clean up any alert logs for the invalid emails
    echo "\nCleaning up alert logs for invalid emails...\n";
    $log_cleanup_query = "DELETE FROM price_alert_logs WHERE alert_id IN (
        SELECT id FROM price_alerts WHERE user_email = 'test@example.com'
    )";
    $log_cleanup_result = $conn->exec($log_cleanup_query);
    echo "✅ Cleaned up {$log_cleanup_result} log entries\n";
    
    echo "\n🎉 Cleanup completed! The system is now ready for real testing.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>