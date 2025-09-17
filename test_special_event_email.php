<?php
require_once 'includes/enhanced_functions.php';

echo "=== Testing Special Event Email Template ===\n";

// Sample price alert data
$sample_alert_data = [
    'email' => 'test@farmscout.com',
    'alert_type' => 'below',
    'target_price' => 40.00,
    'product' => [
        'name' => 'Tomatoes',
        'filipino_name' => 'Kamatis',
        'previous_price' => 50.00,
        'current_price' => 35.00,
        'unit' => 'kg',
        'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400'
    ]
];

try {
    echo "Sending special event price alert...\n";
    
    // Use the enhanced email system
    $mailer = getEnhancedMailer();
    $result = $mailer->sendPriceAlert($sample_alert_data);
    
    if ($result) {
        echo "✅ Special event email sent successfully!\n";
        echo "Email sent to: " . $sample_alert_data['email'] . "\n";
        echo "Product: " . $sample_alert_data['product']['filipino_name'] . "\n";
        echo "Price change: ₱" . number_format($sample_alert_data['product']['previous_price'], 2) . 
             " → ₱" . number_format($sample_alert_data['product']['current_price'], 2) . "\n";
        
        // Calculate savings
        $savings = $sample_alert_data['product']['previous_price'] - $sample_alert_data['product']['current_price'];
        $percentage = round(($savings / $sample_alert_data['product']['previous_price']) * 100, 1);
        echo "Savings: ₱" . number_format($savings, 2) . " (" . $percentage . "% off)\n";
    } else {
        echo "❌ Failed to send special event email\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>