<?php
/**
 * Email Configuration for FarmScout Online
 * Configure your email settings here
 */

// Email configuration array
$email_config = [
    // Basic email settings
    'from_email' => 'noreply@farmscout.com',
    'from_name' => 'FarmScout Online - Baloan Public Market',
    
    // SMTP Settings (set use_smtp to true to enable SMTP)
    'use_smtp' => false, // Change to true for production SMTP
    'smtp_host' => 'smtp.gmail.com', // Gmail SMTP server
    'smtp_port' => 587, // Gmail SMTP port (587 for TLS, 465 for SSL)
    'smtp_username' => '', // Your Gmail address
    'smtp_password' => '', // Your Gmail app password or regular password
    
    // Alternative SMTP providers:
    // 'smtp_host' => 'smtp.outlook.com', // Outlook
    // 'smtp_port' => 587,
    
    // 'smtp_host' => 'mail.yourdomain.com', // Custom domain
    // 'smtp_port' => 587,
    
    // Email content settings
    'site_url' => 'http://localhost/farmscout_online',
    'support_email' => 'support@farmscout.com',
    
    // Testing mode - when true, emails are logged instead of sent
    'test_mode' => true, // Set to false in production
    'test_email' => 'test@example.com', // Where test emails are sent
    
    // Email templates directory
    'templates_dir' => __DIR__ . '/../templates/email/',
    
    // Logging
    'log_emails' => true,
    'email_log_file' => __DIR__ . '/../logs/email.log',
];

/**
 * Get email configuration
 */
function getEmailConfig() {
    global $email_config;
    return $email_config;
}

/**
 * Get mailer instance with configuration
 */
function getMailer() {
    require_once __DIR__ . '/../includes/email.php';
    $config = getEmailConfig();
    return new SimpleMailer($config);
}

/**
 * Log email activity
 */
function logEmailActivity($to, $subject, $status, $error = null) {
    $config = getEmailConfig();
    
    if (!$config['log_emails']) {
        return;
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'to' => $to,
        'subject' => $subject,
        'status' => $status,
        'error' => $error,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
    ];
    
    $log_dir = dirname($config['email_log_file']);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents(
        $config['email_log_file'], 
        json_encode($log_entry) . "\n", 
        FILE_APPEND | LOCK_EX
    );
}


/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($email, $user_name) {
    try {
        $config = getEmailConfig();
        $mailer = getMailer();
        
        // Create email content
        $email_content = createWelcomeEmail($user_name);
        $full_html = $mailer->wrapTemplate($email_content, 'Welcome to FarmScout Online');
        
        $subject = "Welcome to FarmScout Online - Your Market Price Companion!";
        
        // Test mode handling
        if ($config['test_mode']) {
            logEmailActivity($email, $subject, 'TEST_MODE', 'Welcome email sent to test mode');
            error_log("TEST EMAIL: Welcome email to $email");
            return true;
        }
        
        // Send email
        $success = $mailer->send($email, $subject, $full_html, true);
        
        // Log activity
        if ($success) {
            logEmailActivity($email, $subject, 'SENT');
        } else {
            logEmailActivity($email, $subject, 'FAILED', 'Mail function returned false');
        }
        
        return $success;
        
    } catch (Exception $e) {
        logEmailActivity($email, $subject ?? 'Welcome Email', 'ERROR', $e->getMessage());
        error_log("Welcome email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Test email functionality
 */
function sendTestEmail($to_email = null) {
    $config = getEmailConfig();
    $test_email = $to_email ?? $config['test_email'];
    
    $mailer = getMailer();
    
    $content = '
    <h2>Test Email - FarmScout Online</h2>
    <p>This is a test email to verify that the email system is working correctly.</p>
    <div class="alert-box">
        <p><strong>Email System Status:</strong> ✅ Working</p>
        <p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>Configuration:</strong> ' . ($config['use_smtp'] ? 'SMTP' : 'PHP Mail') . '</p>
    </div>
    <p>If you received this email, your FarmScout Online email system is configured correctly!</p>
    ';
    
    $full_html = $mailer->wrapTemplate($content, 'Test Email - FarmScout Online');
    $subject = "Test Email - FarmScout Online Email System";
    
    try {
        $success = $mailer->send($test_email, $subject, $full_html, true);
        
        if ($success) {
            logEmailActivity($test_email, $subject, 'TEST_SENT');
            return ['success' => true, 'message' => 'Test email sent successfully to ' . $test_email];
        } else {
            logEmailActivity($test_email, $subject, 'TEST_FAILED', 'Mail function returned false');
            return ['success' => false, 'message' => 'Failed to send test email'];
        }
    } catch (Exception $e) {
        logEmailActivity($test_email, $subject, 'TEST_ERROR', $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

?>