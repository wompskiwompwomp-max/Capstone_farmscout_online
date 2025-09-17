<?php
/**
 * Email System Installation Script
 * Run this once to set up the email system for FarmScout Online
 */

require_once 'includes/enhanced_functions.php';

$page_title = 'Email System Installation - FarmScout Online';

$steps_completed = [];
$errors = [];
$installation_complete = false;

// Check if price_alerts table exists
function checkPriceAlertsTable() {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        $query = "SHOW TABLES LIKE 'price_alerts'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Create price_alerts table if it doesn't exist
function createPriceAlertsTable() {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        $query = "
        CREATE TABLE IF NOT EXISTS price_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_email VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            target_price DECIMAL(10, 2) NOT NULL,
            alert_type ENUM('below', 'above', 'change') DEFAULT 'below',
            is_active BOOLEAN DEFAULT TRUE,
            last_sent TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_price_alerts_email (user_email),
            INDEX idx_price_alerts_product (product_id),
            INDEX idx_price_alerts_active (is_active)
        )";
        
        $conn->exec($query);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Create email log directory
function createEmailLogDirectory() {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        return mkdir($log_dir, 0755, true);
    }
    return true;
}

// Run installation steps
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    
    // Step 1: Create price_alerts table
    if (!checkPriceAlertsTable()) {
        if (createPriceAlertsTable()) {
            $steps_completed['price_alerts_table'] = 'Price alerts table created successfully';
        } else {
            $errors['price_alerts_table'] = 'Failed to create price_alerts table';
        }
    } else {
        $steps_completed['price_alerts_table'] = 'Price alerts table already exists';
    }
    
    // Step 2: Create email log directory
    if (createEmailLogDirectory()) {
        $steps_completed['log_directory'] = 'Email log directory created successfully';
    } else {
        $errors['log_directory'] = 'Failed to create email log directory';
    }
    
    // Step 3: Test basic email configuration
    $config = getEmailConfig();
    if (!empty($config['from_email'])) {
        $steps_completed['email_config'] = 'Email configuration loaded successfully';
    } else {
        $errors['email_config'] = 'Email configuration not found or incomplete';
    }
    
    // Check if installation is complete
    $installation_complete = empty($errors);
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-primary mb-4">📧 Email System Installation</h1>
        <p class="text-text-secondary">Set up the email system for FarmScout Online price alerts and notifications</p>
    </div>

    <?php if (!empty($steps_completed) || !empty($errors)): ?>
    <div class="mb-8">
        <!-- Success Steps -->
        <?php foreach ($steps_completed as $step => $message): ?>
        <div class="mb-4 p-4 bg-success-100 text-success-700 rounded-lg border border-success-300">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Error Steps -->
        <?php foreach ($errors as $step => $message): ?>
        <div class="mb-4 p-4 bg-error-100 text-error-700 rounded-lg border border-error-300">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ($installation_complete): ?>
        <div class="p-6 bg-gradient-to-r from-success-100 to-success-200 rounded-lg border border-success-300 text-center">
            <div class="text-success-700">
                <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-2xl font-bold mb-2">Installation Complete!</h2>
                <p class="text-lg mb-4">Your email system is now set up and ready to use.</p>
                <div class="space-y-2">
                    <a href="email-config.php" class="btn-primary mr-4">Configure Email Settings</a>
                    <a href="price-alerts.php" class="btn-secondary">Test Price Alerts</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-card p-8">
        <h2 class="text-xl font-semibold text-primary mb-6">Installation Overview</h2>
        
        <div class="space-y-6">
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold text-text-primary mb-2">What will be installed:</h3>
                <ul class="list-disc list-inside space-y-2 text-text-secondary">
                    <li>Price alerts database table for storing user email preferences</li>
                    <li>Email logging directory for tracking email activity</li>
                    <li>Email configuration validation</li>
                    <li>Basic email system testing</li>
                </ul>
            </div>
            
            <div class="border rounded-lg p-4 bg-warning-50 border-warning-200">
                <h3 class="font-semibold text-warning-700 mb-2">⚠️ Before you start:</h3>
                <ul class="list-disc list-inside space-y-2 text-warning-600">
                    <li>Make sure your database is accessible</li>
                    <li>Ensure PHP has write permissions to the logs directory</li>
                    <li>Have your SMTP credentials ready (Gmail, Outlook, or custom SMTP)</li>
                </ul>
            </div>
            
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold text-text-primary mb-2">After installation:</h3>
                <ul class="list-disc list-inside space-y-2 text-text-secondary">
                    <li>Configure your SMTP settings in the Email Configuration page</li>
                    <li>Test email delivery with the built-in testing tools</li>
                    <li>Users can set up price alerts for their favorite products</li>
                    <li>Automatic email notifications will be sent when prices change</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <?php if (!$installation_complete): ?>
            <form method="POST" action="install-email.php">
                <button type="submit" name="install" value="1" class="btn-primary btn-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Install Email System
                </button>
            </form>
            <?php else: ?>
            <div class="text-success-600">
                <p class="text-lg mb-4">✅ Installation completed successfully!</p>
                <a href="email-config.php" class="btn-primary">Go to Email Configuration</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Current Status -->
    <div class="mt-8 bg-white rounded-lg shadow-card p-6">
        <h2 class="text-xl font-semibold text-primary mb-4">Current System Status</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">
                    <?php echo checkPriceAlertsTable() ? '✅' : '❌'; ?>
                </div>
                <h3 class="font-semibold">Price Alerts Table</h3>
                <p class="text-sm text-text-secondary">
                    <?php echo checkPriceAlertsTable() ? 'Ready' : 'Not installed'; ?>
                </p>
            </div>
            
            <div class="border rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">
                    <?php echo is_dir(__DIR__ . '/logs') ? '✅' : '❌'; ?>
                </div>
                <h3 class="font-semibold">Log Directory</h3>
                <p class="text-sm text-text-secondary">
                    <?php echo is_dir(__DIR__ . '/logs') ? 'Ready' : 'Not created'; ?>
                </p>
            </div>
            
            <div class="border rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">
                    <?php 
                    $config = getEmailConfig();
                    echo !empty($config['from_email']) ? '✅' : '⚠️'; 
                    ?>
                </div>
                <h3 class="font-semibold">Email Configuration</h3>
                <p class="text-sm text-text-secondary">
                    <?php 
                    $config = getEmailConfig();
                    echo !empty($config['from_email']) ? 'Configured' : 'Needs setup'; 
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}
</style>

<?php include 'includes/footer.php'; ?>