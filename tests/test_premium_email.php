<?php
/**
 * Test script to send Premium Nitro email template
 */

require_once 'includes/enhanced_functions.php';
require_once 'includes/enhanced_email.php';

// Sample alert data to test the new template
$alert_data = [
    'email' => 'kofi1567@gmail.com',
    'alert_type' => 'below',
    'target_price' => 30.00,
    'product' => [
        'filipino_name' => 'Kamatis',
        'name' => 'Tomatoes',
        'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=300',
        'previous_price' => 50.00,
        'current_price' => 35.00,
        'unit' => 'kg'
    ]
];

try {
    // Initialize email system
    $emailSystem = new EnhancedEmail();
    
    echo "<h2>🧪 Testing Premium Nitro Email Template</h2>";
    
    // Force use of premium_nitro template
    $result = $emailSystem->sendPriceAlert($alert_data, 'premium_nitro');
    
    if ($result) {
        echo "<div style='color: green; padding: 20px; background: #f0f8ff; border-left: 4px solid #4CAF50; margin: 20px 0;'>
                ✅ <strong>SUCCESS!</strong> Premium Nitro email sent successfully to kofi1567@gmail.com
              </div>";
    } else {
        echo "<div style='color: red; padding: 20px; background: #fff5f5; border-left: 4px solid #f56565; margin: 20px 0;'>
                ❌ <strong>FAILED!</strong> Could not send email. Check your email configuration.
              </div>";
    }
    
    echo "<div style='margin-top: 30px;'>
            <h3>📧 Email Details:</h3>
            <ul>
                <li><strong>Template:</strong> premium_nitro (Discord Nitro inspired)</li>
                <li><strong>Recipient:</strong> kofi1567@gmail.com</li>
                <li><strong>Product:</strong> Kamatis (Tomatoes)</li>
                <li><strong>Price:</strong> ₱50.00 → ₱35.00 (30% OFF)</li>
                <li><strong>Design:</strong> Beautiful gradients, celebration elements, premium look</li>
            </ul>
          </div>";
    
    echo "<div style='margin-top: 20px;'>
            <a href='preview_premium_email.php' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 600;'>
                👀 Preview Template
            </a>
            <a href='admin.php' style='background: #6B7280; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 600; margin-left: 10px;'>
                ← Back to Admin
            </a>
          </div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #fff5f5; border-left: 4px solid #f56565; margin: 20px 0;'>
            ❌ <strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
    
    echo "<div style='margin-top: 20px;'>
            <h3>🔧 Troubleshooting:</h3>
            <ol>
                <li>Make sure your email configuration is set up in <code>includes/config/email_config.php</code></li>
                <li>Check that the template file exists: <code>includes/email_templates/price_alert_premium_nitro.html</code></li>
                <li>Verify your SMTP settings if using SMTP</li>
                <li>Check the error logs for more details</li>
            </ol>
          </div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Premium Nitro Email Test</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            background: #f8fafc; 
        }
        h2 { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        code { 
            background: #e2e8f0; 
            padding: 2px 6px; 
            border-radius: 4px; 
        }
    </style>
</head>
<body></body>
</html>