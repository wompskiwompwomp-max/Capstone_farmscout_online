<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Login - FarmScout Online';
$page_description = 'Login to access FarmScout Online admin panel';

$error_message = '';
$success_message = '';

// Handle logout message
if (isset($_GET['message'])) {
    $success_message = sanitizeInput($_GET['message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error_message = 'Please enter both username and password.';
        } else {
            $user = authenticateUser($username, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Redirect to admin panel or intended page
                $redirect = $_GET['redirect'] ?? 'admin.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $error_message = 'Invalid username or password.';
            }
        }
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    $success_message = 'You have been logged out successfully.';
}

include 'includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-surface-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <!-- Logo section removed -->
            <div class="mx-auto text-center">
                <h1 class="text-2xl font-bold text-primary font-accent">FarmScout</h1>
                <p class="text-sm text-text-secondary">Tapat na Presyo</p>
            </div>
            <h2 class="mt-8 text-center text-3xl font-bold text-primary">
                Sign in to FarmScout
            </h2>
            <!-- Added more spacing between heading and subheading -->
            <p class="mt-6 text-center text-sm text-text-secondary mb-8">
                Access the admin panel to manage products and prices
            </p>
        </div>
        
        <div class="bg-white py-8 px-6 shadow-card rounded-lg">
            <?php if ($error_message): ?>
            <div class="mb-4 p-4 bg-error-100 border border-error-300 text-error-700 rounded-lg transition-all duration-300">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-center"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <!-- Success message will be shown via JavaScript notification -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                showTemporaryMessage('<?php echo addslashes($success_message); ?>', 'success');
            });
            </script>
            <?php endif; ?>
            
            <form class="space-y-6" method="POST" action="login.php">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-text-primary mb-2">
                        Username
                    </label>
                    <input id="username" name="username" type="text" required 
                           class="input-field w-full" 
                           placeholder="Enter your username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-text-primary mb-2">
                        Password
                    </label>
                    <input id="password" name="password" type="password" required 
                           class="input-field w-full" 
                           placeholder="Enter your password">
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-text-secondary">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary hover:text-primary-600">
                            Forgot your password?
                        </a>
                    </div>
                </div>
                
                <div>
                    <!-- Made button larger and improved alignment -->
                    <button type="submit" class="btn-primary w-full flex items-center justify-center py-4 px-6 text-lg font-semibold">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"/>
                        </svg>
                        Sign in
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-text-secondary">Access Required</span>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-surface-50 rounded-lg">
                    <!-- Centered administrator note -->
                    <p class="text-sm text-text-secondary mb-2 text-center">Contact your administrator for access credentials.</p>
                </div>
            </div>
        </div>
        
        <!-- Added extra spacing before Back to Homepage link -->
        <div class="text-center mt-8">
            <a href="index.php" class="text-primary hover:text-primary-600 font-medium">
                ← Back to Homepage
            </a>
        </div>
    </div>
</div>

<script>
// Auto-focus on username field
document.getElementById('username').focus();

// Show temporary message without page reload - Same as shopping list
function showTemporaryMessage(message, type) {
    console.log('showTemporaryMessage called:', { message, type });
    
    // Remove any existing temporary messages
    const existingMsg = document.getElementById('temp-message');
    if (existingMsg) {
        existingMsg.remove();
    }
    
    // Create message element with simpler styling
    const messageDiv = document.createElement('div');
    messageDiv.id = 'temp-message';
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 14px;
        font-weight: 500;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    // Set colors and content based on type - Black theme with white text and icons
    if (type === 'success') {
        messageDiv.style.backgroundColor = '#000000'; // Pure black background
        messageDiv.style.borderLeft = '4px solid #22c55e'; // Green accent
        messageDiv.style.color = '#ffffff'; // Pure white text
        messageDiv.style.border = '1px solid #333333'; // Dark border
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="assets/images/demi doggu.gif" alt="Success!" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                <div style="flex: 1; color: #ffffff;">${message}</div>
                <button onclick="document.getElementById('temp-message').remove()" style="margin-left: auto; background: none; border: none; color: #ffffff; cursor: pointer; padding: 6px; border-radius: 4px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; opacity: 0.7;" onmouseover="this.style.backgroundColor='#333333'; this.style.opacity='1';" onmouseout="this.style.backgroundColor='transparent'; this.style.opacity='0.7';">×</button>
            </div>
        `;
    } else if (type === 'error') {
        messageDiv.style.backgroundColor = '#000000'; // Pure black background
        messageDiv.style.borderLeft = '4px solid #ef4444'; // Red accent
        messageDiv.style.color = '#ffffff'; // Pure white text
        messageDiv.style.border = '1px solid #333333'; // Dark border
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #ef4444; color: #ffffff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">×</div>
                <div style="flex: 1; color: #ffffff;">${message}</div>
                <button onclick="document.getElementById('temp-message').remove()" style="margin-left: auto; background: none; border: none; color: #ffffff; cursor: pointer; padding: 6px; border-radius: 4px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; opacity: 0.7;" onmouseover="this.style.backgroundColor='#333333'; this.style.opacity='1';" onmouseout="this.style.backgroundColor='transparent'; this.style.opacity='0.7';">×</button>
            </div>
        `;
    } else {
        messageDiv.style.backgroundColor = '#000000'; // Pure black background
        messageDiv.style.borderLeft = '4px solid #3b82f6'; // Blue accent
        messageDiv.style.color = '#ffffff'; // Pure white text
        messageDiv.style.border = '1px solid #333333'; // Dark border
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #3b82f6; color: #ffffff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">i</div>
                <div style="flex: 1; color: #ffffff;">${message}</div>
                <button onclick="document.getElementById('temp-message').remove()" style="margin-left: auto; background: none; border: none; color: #ffffff; cursor: pointer; padding: 6px; border-radius: 4px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; opacity: 0.7;" onmouseover="this.style.backgroundColor='#333333'; this.style.opacity='1';" onmouseout="this.style.backgroundColor='transparent'; this.style.opacity='0.7';">×</button>
            </div>
        `;
    }
    
    // Add to page
    document.body.appendChild(messageDiv);
    console.log('Message element added to DOM');
    
    // Animate in
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
        console.log('Message animated in');
    }, 100);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
                console.log('Message removed from DOM');
            }
        }, 300);
    }, 4000);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        e.preventDefault();
        showTemporaryMessage('Please enter both username and password.', 'error');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        showTemporaryMessage('Password must be at least 6 characters long.', 'error');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
