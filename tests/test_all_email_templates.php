<?php
require_once 'includes/enhanced_functions.php';

echo "=== Testing All Email Templates ===\n\n";

// Sample price alert data
$sample_alert_data = [
    'email' => 'test@farmscout.com',
    'alert_type' => 'below',
    'target_price' => 40.00,
    'product' => [
        'name' => 'Fresh Tomatoes',
        'filipino_name' => 'Sariwang Kamatis',
        'previous_price' => 55.00,
        'current_price' => 32.00,
        'unit' => 'kg',
        'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400'
    ]
];

$templates_to_test = [
    'special_event_v2' => '🎉 Special Event V2 (Email Client Optimized)',
    'special_event' => '🔥 Special Event (Original)',
    'standard' => '📧 Standard Template',
    'professional' => '💼 Professional Template',
    'dark' => '🌙 Dark Theme'
];

$success_count = 0;
$total_tests = count($templates_to_test);

echo "Testing " . $total_tests . " different email templates...\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($templates_to_test as $template_key => $template_name) {
    echo "Testing: {$template_name}\n";
    echo str_repeat("-", 40) . "\n";
    
    try {
        // Use the enhanced email system with template selection
        $mailer = getEnhancedMailer();
        $result = $mailer->sendPriceAlert($sample_alert_data, $template_key);
        
        if ($result) {
            echo "✅ SUCCESS - Email sent successfully!\n";
            $success_count++;
        } else {
            echo "❌ FAILED - Email not sent\n";
        }
        
        // Calculate savings for display
        $savings = $sample_alert_data['product']['previous_price'] - $sample_alert_data['product']['current_price'];
        $percentage = round(($savings / $sample_alert_data['product']['previous_price']) * 100, 1);
        
        echo "   📧 To: " . $sample_alert_data['email'] . "\n";
        echo "   🥕 Product: " . $sample_alert_data['product']['filipino_name'] . "\n";
        echo "   💰 Price: ₱" . number_format($sample_alert_data['product']['previous_price'], 2) . 
             " → ₱" . number_format($sample_alert_data['product']['current_price'], 2) . "\n";
        echo "   💸 Savings: ₱" . number_format($savings, 2) . " (" . $percentage . "% off)\n";
        echo "   📋 Template: {$template_key}\n";
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "✅ Successful: {$success_count}/{$total_tests}\n";
echo "❌ Failed: " . ($total_tests - $success_count) . "/{$total_tests}\n";

if ($success_count > 0) {
    echo "\n🎉 Email templates are working! Check your email to see the different designs.\n";
    echo "\nKey Features of the Special Event Templates:\n";
    echo "• 🎨 Bold, eye-catching design inspired by promotional materials\n";
    echo "• 📱 Mobile-responsive layout\n";
    echo "• 💳 Clear price comparison with savings highlighted\n";
    echo "• 🔗 Call-to-action buttons for engagement\n";
    echo "• 🏪 FarmScout branding and market information\n";
    
    echo "\nTemplate Recommendations:\n";
    echo "• special_event_v2: Best for most email clients (Gmail, Outlook, etc.)\n";
    echo "• special_event: Modern design with advanced CSS features\n";
    echo "• standard: Clean, simple design for maximum compatibility\n";
} else {
    echo "\n⚠️  No emails were sent successfully. Please check your email configuration.\n";
}

echo "\n=== Test Complete ===\n";
?>