<?php
require_once 'includes/enhanced_functions.php';

echo "=== Setting up Email Configuration ===\n";

// Note: You'll need to replace 'YOUR_APP_PASSWORD_HERE' with your actual Gmail App Password
$email_config = [
    'from_email' => 'noreply@farmscout.com',
    'from_name' => 'FarmScout Online - Baloan Public Market',
    'use_smtp' => true,  // Enable SMTP
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'wompskiwompwomp@gmail.com',  // Your Gmail
    'smtp_password' => 'YOUR_APP_PASSWORD_HERE',      // Replace with your App Password
    'smtp_secure' => 'tls',
    'site_url' => 'http://localhost/farmscout_online',
    'support_email' => 'support@farmscout.com',
    'test_mode' => false,  // Disable test mode to send real emails
    'test_email' => 'wompskiwompwomp@gmail.com'
];

// Update configuration
if (updateEmailConfig($email_config)) {
    echo "✅ Email configuration updated successfully!\n";
    echo "\nCurrent settings:\n";
    echo "- SMTP: ENABLED\n";
    echo "- Test Mode: DISABLED\n";
    echo "- SMTP Host: " . $email_config['smtp_host'] . "\n";
    echo "- SMTP Username: " . $email_config['smtp_username'] . "\n";
    echo "- SMTP Password: " . (empty($email_config['smtp_password']) || $email_config['smtp_password'] === 'YOUR_APP_PASSWORD_HERE' ? 'NOT SET - PLEASE UPDATE!' : 'SET') . "\n";
    
    if ($email_config['smtp_password'] === 'YOUR_APP_PASSWORD_HERE') {
        echo "\n⚠️  IMPORTANT: You need to edit this file and replace 'YOUR_APP_PASSWORD_HERE' with your actual Gmail App Password!\n";
    } else {
        echo "\n🎉 Configuration looks good! Try sending a test email now.\n";
    }
} else {
    echo "❌ Failed to update email configuration\n";
}
?>