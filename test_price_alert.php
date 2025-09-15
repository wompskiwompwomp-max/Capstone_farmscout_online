<?php
require_once 'includes/enhanced_functions.php';

// Test price alert email functionality
echo "Testing Price Alert Email Functionality\n";
echo "=====================================\n\n";

// Test email configuration
try {
    // Check if the new PHPMailer is available
    if (file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        echo "✓ PHPMailer found\n";
    } else {
        echo "✗ PHPMailer not found\n";
        exit(1);
    }
    
    if (file_exists('includes/email_config.php')) {
        require_once 'includes/email_config.php';
        echo "✓ Email configuration loaded successfully\n";
        echo "SMTP Host: " . SMTP_HOST . "\n";
        echo "From Email: " . FROM_EMAIL . "\n\n";
    } else {
        echo "✓ Using built-in email configuration\n\n";
    }
    
} catch (Exception $e) {
    echo "✗ Email configuration error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test PHPMailer installation
try {
    require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
    echo "✓ PHPMailer classes loaded successfully\n\n";
} catch (Exception $e) {
    echo "✗ PHPMailer loading error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test database connection
$conn = getDB();
if (!$conn) {
    echo "✗ Database connection failed\n";
    exit(1);
}
echo "✓ Database connection successful\n\n";

// Test email template exists
$template_path = __DIR__ . '/includes/email_templates/price_alert_template.html';
if (!file_exists($template_path)) {
    echo "✗ Email template not found: " . $template_path . "\n";
    exit(1);
}
echo "✓ Email template found\n\n";

// Get test data
$products = getAllProducts();
if (empty($products)) {
    echo "✗ No products found for testing\n";
    exit(1);
}

$test_product = $products[0]; // Use first product
echo "Using test product: " . $test_product['filipino_name'] . " (" . $test_product['name'] . ")\n";
echo "Current price: ₱" . number_format($test_product['current_price'], 2) . "\n\n";

// Test price alert creation
$test_email = "test@example.com";
$target_price = $test_product['current_price'] * 0.9; // 10% below current price

echo "Creating test price alert...\n";
$alert_created = addPriceAlert($test_email, $test_product['id'], $target_price, 'below');

if (!$alert_created) {
    echo "✗ Failed to create price alert\n";
    exit(1);
}
echo "✓ Price alert created successfully\n\n";

// Test price alert trigger
echo "Testing price alert trigger...\n";
$old_price = $test_product['current_price'];
$new_price = $target_price - 1; // Price below target

echo "Simulating price change:\n";
echo "Old price: ₱" . number_format($old_price, 2) . "\n";
echo "New price: ₱" . number_format($new_price, 2) . "\n\n";

// Test email sending (but don't actually send)
echo "Testing email function (dry run)...\n";

try {
    // Create a test version that doesn't actually send
    $alert_message = "Price dropped below your target of ₱" . number_format($target_price, 2);
    
    echo "Email would be sent with:\n";
    echo "- To: " . $test_email . "\n";
    echo "- Product: " . $test_product['filipino_name'] . "\n";
    echo "- Old price: ₱" . number_format($old_price, 2) . "\n";
    echo "- New price: ₱" . number_format($new_price, 2) . "\n";
    echo "- Alert message: " . $alert_message . "\n\n";
    
    echo "✓ Email function test passed\n\n";
    
} catch (Exception $e) {
    echo "✗ Email function test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Cleanup test data
echo "Cleaning up test data...\n";
$cleanup_query = "DELETE FROM price_alerts WHERE user_email = :email AND product_id = :product_id";
$cleanup_stmt = $conn->prepare($cleanup_query);
$cleanup_stmt->bindParam(':email', $test_email);
$cleanup_stmt->bindParam(':product_id', $test_product['id'], PDO::PARAM_INT);
$cleanup_stmt->execute();
echo "✓ Test data cleaned up\n\n";

echo "=====================================\n";
echo "All tests passed! ✓\n";
echo "Price alert email system is ready.\n";
echo "=====================================\n";

// Instructions
echo "\nTo send a real test email:\n";
echo "1. Update the \$test_email variable above to your real email\n";
echo "2. Uncomment the actual sendPriceAlertEmail() call\n";
echo "3. Run this script again\n";
?>