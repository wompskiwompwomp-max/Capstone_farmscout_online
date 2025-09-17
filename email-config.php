<?php
require_once 'includes/enhanced_functions.php';
require_once 'includes/enhanced_email.php';

// Require admin authentication
requireAdmin();

$page_title = 'Email Configuration - FarmScout Online';
$page_description = 'Configure and test email settings for FarmScout Online';

// Track page view
trackPageView('email_config');

$message = '';
$message_type = '';
$test_results = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_config':
                // Update email configuration
                $config_updates = [
                    'from_email' => sanitizeInput($_POST['from_email'] ?? ''),
                    'from_name' => sanitizeInput($_POST['from_name'] ?? ''),
                    'use_smtp' => isset($_POST['use_smtp']) ? true : false,
                    'smtp_host' => sanitizeInput($_POST['smtp_host'] ?? ''),
                    'smtp_port' => intval($_POST['smtp_port'] ?? 587),
                    'smtp_username' => sanitizeInput($_POST['smtp_username'] ?? ''),
                    'smtp_password' => sanitizeInput($_POST['smtp_password'] ?? ''),
                    'smtp_secure' => sanitizeInput($_POST['smtp_secure'] ?? 'tls'),
                    'test_mode' => isset($_POST['test_mode']) ? true : false,
                    'test_email' => sanitizeInput($_POST['test_email'] ?? ''),
                    'site_url' => sanitizeInput($_POST['site_url'] ?? ''),
                    'support_email' => sanitizeInput($_POST['support_email'] ?? '')
                ];
                
                if (updateEmailConfig($config_updates)) {
                    $message = 'Email configuration updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to update email configuration.';
                    $message_type = 'error';
                }
                break;
                
            case 'test_connection':
                // Test SMTP connection
                $mailer = getEnhancedMailer();
                $test_results['connection'] = $mailer->testSMTPConnection();
                break;
                
            case 'send_test_email':
                // Send test email
                $test_email = sanitizeInput($_POST['test_email_address'] ?? '');
                if (empty($test_email)) {
                    $message = 'Please enter a test email address.';
                    $message_type = 'error';
                } else {
                    $mailer = getEnhancedMailer();
                    $result = $mailer->sendTestEmail($test_email);
                    if ($result) {
                        $test_results['test_email'] = ['success' => true, 'message' => 'Test email sent successfully to ' . $test_email];
                    } else {
                        $test_results['test_email'] = ['success' => false, 'message' => 'Failed to send test email'];
                    }
                }
                break;
                
            case 'send_sample_alert':
                // Send sample price alert
                $test_email = sanitizeInput($_POST['alert_email_address'] ?? '');
                if (empty($test_email)) {
                    $message = 'Please enter an email address for the sample alert.';
                    $message_type = 'error';
                } else {
                    $sample_alert_data = [
                        'email' => $test_email,
                        'alert_type' => 'below',
                        'target_price' => 45.00,
                        'product' => [
                            'filipino_name' => 'Kamatis',
                            'name' => 'Tomatoes',
                            'previous_price' => 50.00,
                            'current_price' => 40.00,
                            'unit' => 'kg',
                            'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400'
                        ]
                    ];
                    
                    $mailer = getEnhancedMailer();
                    $result = $mailer->sendPriceAlert($sample_alert_data);
                    if ($result) {
                        $test_results['sample_alert'] = ['success' => true, 'message' => 'Sample price alert sent successfully to ' . $test_email];
                    } else {
                        $test_results['sample_alert'] = ['success' => false, 'message' => 'Failed to send sample price alert'];
                    }
                }
                break;
        }
    }
}

// Get current configuration
$current_config = getEmailConfig();

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary mb-4">Email Configuration & Testing</h1>
        <p class="text-text-secondary">Configure SMTP settings and test email functionality for FarmScout Online</p>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700 border border-success-300' : 'bg-error-100 text-error-700 border border-error-300'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Test Results -->
    <?php if (!empty($test_results)): ?>
    <div class="mb-6 space-y-4">
        <?php foreach ($test_results as $test_name => $result): ?>
        <div class="p-4 rounded-lg <?php echo $result['success'] ? 'bg-success-100 text-success-700 border border-success-300' : 'bg-error-100 text-error-700 border border-error-300'; ?>">
            <div class="flex items-center">
                <?php if ($result['success']): ?>
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <?php else: ?>
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?php endif; ?>
                <strong><?php echo ucfirst(str_replace('_', ' ', $test_name)); ?>:</strong>
                <span class="ml-2"><?php echo htmlspecialchars($result['message']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Configuration Form -->
        <div class="bg-white rounded-lg shadow-card p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">Email Configuration</h2>
            
            <form method="POST" action="email-config.php" class="space-y-6">
                <?php echo generateCSRFToken(); ?>
                <input type="hidden" name="action" value="update_config">
                
                <!-- Basic Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-text-primary">Basic Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="from_email" class="block text-sm font-medium text-text-primary mb-2">From Email</label>
                            <input type="email" id="from_email" name="from_email" 
                                   value="<?php echo htmlspecialchars($current_config['from_email']); ?>"
                                   class="input-field" required>
                        </div>
                        
                        <div>
                            <label for="from_name" class="block text-sm font-medium text-text-primary mb-2">From Name</label>
                            <input type="text" id="from_name" name="from_name" 
                                   value="<?php echo htmlspecialchars($current_config['from_name']); ?>"
                                   class="input-field" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="site_url" class="block text-sm font-medium text-text-primary mb-2">Site URL</label>
                            <input type="url" id="site_url" name="site_url" 
                                   value="<?php echo htmlspecialchars($current_config['site_url']); ?>"
                                   class="input-field" required>
                        </div>
                        
                        <div>
                            <label for="support_email" class="block text-sm font-medium text-text-primary mb-2">Support Email</label>
                            <input type="email" id="support_email" name="support_email" 
                                   value="<?php echo htmlspecialchars($current_config['support_email']); ?>"
                                   class="input-field" required>
                        </div>
                    </div>
                </div>
                
                <!-- SMTP Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-text-primary">SMTP Settings</h3>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="use_smtp" name="use_smtp" value="1"
                               <?php echo $current_config['use_smtp'] ? 'checked' : ''; ?>
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="use_smtp" class="ml-2 text-sm text-text-primary">Enable SMTP (Recommended for production)</label>
                    </div>
                    
                    <div id="smtp-settings" class="space-y-4" style="<?php echo $current_config['use_smtp'] ? '' : 'display: none;'; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_host" class="block text-sm font-medium text-text-primary mb-2">SMTP Host</label>
                                <input type="text" id="smtp_host" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($current_config['smtp_host']); ?>"
                                       class="input-field" placeholder="smtp.gmail.com">
                            </div>
                            
                            <div>
                                <label for="smtp_port" class="block text-sm font-medium text-text-primary mb-2">SMTP Port</label>
                                <input type="number" id="smtp_port" name="smtp_port" 
                                       value="<?php echo $current_config['smtp_port']; ?>"
                                       class="input-field" placeholder="587">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_username" class="block text-sm font-medium text-text-primary mb-2">SMTP Username</label>
                                <input type="text" id="smtp_username" name="smtp_username" 
                                       value="<?php echo htmlspecialchars($current_config['smtp_username']); ?>"
                                       class="input-field" placeholder="your-email@gmail.com">
                            </div>
                            
                            <div>
                                <label for="smtp_password" class="block text-sm font-medium text-text-primary mb-2">SMTP Password</label>
                                <input type="password" id="smtp_password" name="smtp_password" 
                                       value="<?php echo htmlspecialchars($current_config['smtp_password']); ?>"
                                       class="input-field" placeholder="App password or regular password">
                            </div>
                        </div>
                        
                        <div>
                            <label for="smtp_secure" class="block text-sm font-medium text-text-primary mb-2">Encryption</label>
                            <select id="smtp_secure" name="smtp_secure" class="input-field">
                                <option value="tls" <?php echo $current_config['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS (Port 587)</option>
                                <option value="ssl" <?php echo $current_config['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL (Port 465)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Testing Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-text-primary">Testing Settings</h3>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="test_mode" name="test_mode" value="1"
                               <?php echo $current_config['test_mode'] ? 'checked' : ''; ?>
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="test_mode" class="ml-2 text-sm text-text-primary">Test Mode (Emails will be logged instead of sent)</label>
                    </div>
                    
                    <div>
                        <label for="test_email" class="block text-sm font-medium text-text-primary mb-2">Test Email Address</label>
                        <input type="email" id="test_email" name="test_email" 
                               value="<?php echo htmlspecialchars($current_config['test_email']); ?>"
                               class="input-field" placeholder="test@example.com">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Testing Panel -->
        <div class="space-y-6">
            <!-- Connection Test -->
            <div class="bg-white rounded-lg shadow-card p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Test SMTP Connection</h2>
                <p class="text-text-secondary mb-4">Test if your SMTP settings are working correctly.</p>
                
                <form method="POST" action="email-config.php">
                    <?php echo generateCSRFToken(); ?>
                    <input type="hidden" name="action" value="test_connection">
                    <button type="submit" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        Test Connection
                    </button>
                </form>
            </div>
            
            <!-- Test Email -->
            <div class="bg-white rounded-lg shadow-card p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Send Test Email</h2>
                <p class="text-text-secondary mb-4">Send a simple test email to verify email delivery.</p>
                
                <form method="POST" action="email-config.php" class="space-y-4">
                    <?php echo generateCSRFToken(); ?>
                    <input type="hidden" name="action" value="send_test_email">
                    
                    <div>
                        <label for="test_email_address" class="block text-sm font-medium text-text-primary mb-2">Test Email Address</label>
                        <input type="email" id="test_email_address" name="test_email_address" 
                               value="<?php echo htmlspecialchars($current_config['test_email']); ?>"
                               class="input-field" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Send Test Email
                    </button>
                </form>
            </div>
            
            <!-- Sample Price Alert -->
            <div class="bg-white rounded-lg shadow-card p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Send Sample Price Alert</h2>
                <p class="text-text-secondary mb-4">Send a sample price alert email to test the alert system.</p>
                
                <form method="POST" action="email-config.php" class="space-y-4">
                    <?php echo generateCSRFToken(); ?>
                    <input type="hidden" name="action" value="send_sample_alert">
                    
                    <div>
                        <label for="alert_email_address" class="block text-sm font-medium text-text-primary mb-2">Email Address</label>
                        <input type="email" id="alert_email_address" name="alert_email_address" 
                               value="<?php echo htmlspecialchars($current_config['test_email']); ?>"
                               class="input-field" required>
                    </div>
                    
                    <button type="submit" class="btn-accent">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                        </svg>
                        Send Sample Alert
                    </button>
                </form>
            </div>
            
            <!-- Configuration Status -->
            <div class="bg-white rounded-lg shadow-card p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Current Configuration</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="font-medium">From Email:</span>
                        <span class="text-text-secondary"><?php echo htmlspecialchars($current_config['from_email']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="font-medium">SMTP Enabled:</span>
                        <span class="<?php echo $current_config['use_smtp'] ? 'text-success-600' : 'text-error-600'; ?>">
                            <?php echo $current_config['use_smtp'] ? '✅ Yes' : '❌ No'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="font-medium">Test Mode:</span>
                        <span class="<?php echo $current_config['test_mode'] ? 'text-warning-600' : 'text-success-600'; ?>">
                            <?php echo $current_config['test_mode'] ? '⚠️ Enabled' : '✅ Disabled'; ?>
                        </span>
                    </div>
                    <?php if ($current_config['use_smtp']): ?>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="font-medium">SMTP Host:</span>
                        <span class="text-text-secondary"><?php echo htmlspecialchars($current_config['smtp_host']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="font-medium">SMTP Port:</span>
                        <span class="text-text-secondary"><?php echo $current_config['smtp_port']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for dynamic form -->
<script>
document.getElementById('use_smtp').addEventListener('change', function() {
    const smtpSettings = document.getElementById('smtp-settings');
    if (this.checked) {
        smtpSettings.style.display = 'block';
    } else {
        smtpSettings.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>

