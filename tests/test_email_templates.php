<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Email Template Test - FarmScout Online';
$page_description = 'Test the new modern email templates';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'test_price_alert':
            // Sample price alert data
            $alert_data = [
                'email' => sanitizeInput($_POST['test_email']),
                'alert_type' => 'below',
                'target_price' => 40.00,
                'product' => [
                    'filipino_name' => 'Kangkong',
                    'name' => 'Water Spinach',
                    'previous_price' => 45.00,
                    'current_price' => 39.00,
                    'unit' => 'bundle',
                    'image_url' => 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=300&h=300&fit=crop&crop=center'
                ]
            ];
            
            $result = sendEnhancedPriceAlert($alert_data);
            
            if ($result) {
                $message = 'Price alert test email sent successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to send price alert test email.';
                $message_type = 'error';
            }
            break;
            
        case 'test_welcome':
            $result = sendEnhancedWelcomeEmail(sanitizeInput($_POST['test_email']), 'John Doe');
            
            if ($result) {
                $message = 'Welcome test email sent successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to send welcome test email.';
                $message_type = 'error';
            }
            break;
            
        case 'test_system':
            $result = sendEnhancedTestEmail(sanitizeInput($_POST['test_email']));
            
            if ($result['success']) {
                $message = $result['message'];
                $message_type = 'success';
            } else {
                $message = $result['message'];
                $message_type = 'error';
            }
            break;
    }
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary mb-2">Email Template Test</h1>
        <p class="text-text-secondary">Test the new modern email templates for FarmScout Online</p>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700' : 'bg-error-100 text-error-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Price Alert Test -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-2xl">🔔</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-text-primary">Price Alert Email</h3>
                        <p class="text-sm text-text-secondary">Test price change notification</p>
                    </div>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="test_price_alert">
                    <div>
                        <label for="price_alert_email" class="block text-sm font-medium text-text-primary mb-2">Test Email:</label>
                        <input type="email" id="price_alert_email" name="test_email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="your-email@example.com">
                    </div>
                    <button type="submit" class="w-full btn-primary">
                        Send Price Alert Test
                    </button>
                </form>
                
                <div class="mt-4 p-3 bg-surface-50 rounded-lg">
                    <p class="text-xs text-text-muted">
                        <strong>Sample:</strong> Kangkong price drop from ₱45.00 to ₱39.00 per bundle
                    </p>
                </div>
            </div>
        </div>

        <!-- Welcome Email Test -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-2xl">🌾</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-text-primary">Welcome Email</h3>
                        <p class="text-sm text-text-secondary">Test new user welcome message</p>
                    </div>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="test_welcome">
                    <div>
                        <label for="welcome_email" class="block text-sm font-medium text-text-primary mb-2">Test Email:</label>
                        <input type="email" id="welcome_email" name="test_email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="your-email@example.com">
                    </div>
                    <button type="submit" class="w-full btn-accent">
                        Send Welcome Test
                    </button>
                </form>
                
                <div class="mt-4 p-3 bg-surface-50 rounded-lg">
                    <p class="text-xs text-text-muted">
                        <strong>Sample:</strong> Welcome email for user "John Doe"
                    </p>
                </div>
            </div>
        </div>

        <!-- System Test Email -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-2xl">✅</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-text-primary">System Test Email</h3>
                        <p class="text-sm text-text-secondary">Test email system functionality</p>
                    </div>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="test_system">
                    <div>
                        <label for="system_email" class="block text-sm font-medium text-text-primary mb-2">Test Email:</label>
                        <input type="email" id="system_email" name="test_email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="your-email@example.com">
                    </div>
                    <button type="submit" class="w-full btn-success">
                        Send System Test
                    </button>
                </form>
                
                <div class="mt-4 p-3 bg-surface-50 rounded-lg">
                    <p class="text-xs text-text-muted">
                        <strong>Info:</strong> Tests email configuration and template system
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Features -->
    <div class="mt-8 card">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-text-primary mb-4">✨ New Email Template Features</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-text-primary mb-3">🎨 Design Improvements</h4>
                    <ul class="space-y-2 text-sm text-text-secondary">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Modern, professional design with gradients and shadows
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Improved typography and spacing
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Better color scheme and visual hierarchy
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Professional branded header and footer
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-text-primary mb-3">📱 Technical Features</h4>
                    <ul class="space-y-2 text-sm text-text-secondary">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-accent rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Fully responsive for all devices
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-accent rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Outlook and Gmail compatible
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-accent rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Dark mode support
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-accent rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Optimized loading and performance
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-surface-50 rounded-lg">
                <p class="text-sm text-text-secondary">
                    <strong>Note:</strong> These templates are designed to work across all major email clients including Gmail, Outlook, Apple Mail, and mobile devices. They follow email development best practices for maximum compatibility and deliverability.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.btn-success {
    @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200;
}
</style>

<?php include 'includes/footer.php'; ?>