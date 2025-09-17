<?php
require_once 'includes/enhanced_functions.php';

echo "=== FarmScout Price Alert Testing ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

// Function to simulate price change and trigger alerts
function testPriceChange($product_id, $new_price) {
    global $conn;
    
    echo "\n=== Testing Price Alert System ===\n";
    
    // Get current product info
    $query = "SELECT id, name, filipino_name, current_price, previous_price FROM products WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "❌ Product not found!\n";
        return;
    }
    
    $old_price = $product['current_price'];
    echo "Product: {$product['filipino_name']} ({$product['name']})\n";
    echo "Current Price: ₱" . number_format($old_price, 2) . "\n";
    echo "New Price: ₱" . number_format($new_price, 2) . "\n";
    
    // Check how many active alerts exist for this product
    $alert_query = "SELECT COUNT(*) as count FROM price_alerts WHERE product_id = :id AND is_active = 1";
    $alert_stmt = $conn->prepare($alert_query);
    $alert_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $alert_stmt->execute();
    $alert_count = $alert_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Active alerts for this product: {$alert_count}\n";
    
    if ($alert_count == 0) {
        echo "⚠️ No active alerts found for this product. Create an alert first!\n";
        echo "   Go to: http://localhost/farmscout_online/price-alerts.php\n";
        return;
    }
    
    // Update the price
    echo "\nUpdating price...\n";
    $update_query = "UPDATE products SET previous_price = current_price, current_price = :new_price, updated_at = NOW() WHERE id = :id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':new_price', $new_price);
    $update_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        echo "✅ Price updated successfully!\n";
        
        // Now trigger the alert checking
        echo "\n🔔 Checking for alerts to trigger...\n";
        $result = checkAndTriggerPriceAlerts($product_id, $old_price, $new_price);
        
        if ($result) {
            echo "✅ Alert checking completed!\n";
            
            // Check how many alerts were actually sent
            $sent_query = "SELECT COUNT(*) as sent FROM price_alert_logs 
                          WHERE alert_id IN (SELECT id FROM price_alerts WHERE product_id = :id AND is_active = 1)
                          AND DATE(triggered_at) = CURDATE()";
            $sent_stmt = $conn->prepare($sent_query);
            $sent_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
            $sent_stmt->execute();
            $sent_count = $sent_stmt->fetch(PDO::FETCH_ASSOC)['sent'];
            
            echo "📧 {$sent_count} alert email(s) were sent today for this product\n";
            
            if ($sent_count > 0) {
                echo "🎉 SUCCESS! Check your email inbox for the price alert!\n";
                echo "   The email should look similar to the sample alert you received earlier.\n";
            } else {
                echo "ℹ️ No alerts were triggered. This might be because:\n";
                echo "   - Alert conditions weren't met (e.g., price didn't drop below target)\n";
                echo "   - Alert was already sent today\n";
                
                // Show alert details
                $details_query = "SELECT pa.*, p.name FROM price_alerts pa 
                                 JOIN products p ON pa.product_id = p.id 
                                 WHERE pa.product_id = :id AND pa.is_active = 1";
                $details_stmt = $conn->prepare($details_query);
                $details_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $details_stmt->execute();
                $alerts = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "\n   Current alerts for this product:\n";
                foreach ($alerts as $alert) {
                    echo "   - {$alert['alert_type']} ₱{$alert['target_price']} (Email: {$alert['user_email']})\n";
                }
            }
        } else {
            echo "❌ Alert checking failed!\n";
        }
        
    } else {
        echo "❌ Failed to update price!\n";
    }
}

// Function to show current system status
function showSystemStatus() {
    global $conn;
    
    echo "=== Current System Status ===\n";
    
    // Show total alerts
    $total_query = "SELECT COUNT(*) as total FROM price_alerts WHERE is_active = 1";
    $total_result = $conn->query($total_query)->fetch(PDO::FETCH_ASSOC);
    echo "Total active alerts: {$total_result['total']}\n";
    
    // Show alerts by product
    $product_query = "SELECT p.id, p.filipino_name, COUNT(pa.id) as alert_count 
                      FROM products p 
                      LEFT JOIN price_alerts pa ON p.id = pa.product_id AND pa.is_active = 1
                      WHERE pa.id IS NOT NULL
                      GROUP BY p.id 
                      ORDER BY alert_count DESC";
    $product_result = $conn->query($product_query);
    $products_with_alerts = $product_result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nProducts with active alerts:\n";
    foreach ($products_with_alerts as $product) {
        echo "- {$product['filipino_name']} (ID: {$product['id']}): {$product['alert_count']} alerts\n";
    }
    
    // Show recent alert activity
    $recent_query = "SELECT COUNT(*) as recent FROM price_alert_logs WHERE DATE(triggered_at) = CURDATE()";
    $recent_result = $conn->query($recent_query)->fetch(PDO::FETCH_ASSOC);
    echo "\nAlerts triggered today: {$recent_result['recent']}\n";
    
    echo "\n";
}

// Show current status
showSystemStatus();

echo "=== Testing Options ===\n";
echo "1. Test price drop for Kamatis (ID: 1) - set to ₱40.00\n";
echo "2. Test price rise for Kamatis (ID: 1) - set to ₱55.00\n";
echo "3. Custom test\n";
echo "\nChoose an option or modify the script below:\n\n";

// UNCOMMENT ONE OF THESE LINES TO TEST:

// Test 1: Price drop (should trigger "below" alerts)
testPriceChange(1, 50.00);

// Test 2: Price rise (should trigger "above" alerts)  
// testPriceChange(1, 55.00);

// Test 3: Custom - change these values
// testPriceChange(PRODUCT_ID, NEW_PRICE);

echo "💡 Instructions:\n";
echo "1. First, create a price alert on the website\n";
echo "2. Then uncomment one of the test lines above\n";
echo "3. Run this script again to trigger the alert\n";
echo "4. Check your email for the alert notification!\n";
?>