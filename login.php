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
            <!-- Updated logo to match header -->
            <div class="mx-auto flex items-center justify-center">
                <div class="flex items-center">
                    <img src="assets/images/farmscoutlogo.png" alt="FarmScout - Tapat na Presyo" class="h-10 w-10 object-contain" />
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-primary font-accent">FarmScout</h1>
                        <p class="text-xs text-text-secondary">Tapat na Presyo</p>
                    </div>
                </div>
            </div>
            <h2 class="mt-8 text-center text-3xl font-bold text-primary">
                Sign in to FarmScout
            </h2>
            <!-- Added more spacing between heading and subheading -->
            <p class="mt-6 text-center text-sm text-text-secondary">
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
            <div id="success-message" class="mb-4 p-4 bg-success-100 border border-success-300 text-success-700 rounded-lg transition-all duration-300 opacity-100 transform translate-y-0">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-center"><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            </div>
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

// Auto-fade success message
const successMessage = document.getElementById('success-message');
if (successMessage) {
    // Add a close button for manual dismissal
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '&times;';
    closeButton.className = 'ml-2 text-success-700 hover:text-success-800 font-bold text-xl leading-none cursor-pointer';
    closeButton.onclick = () => fadeOutMessage();
    
    const messageContainer = successMessage.querySelector('div');
    messageContainer.appendChild(closeButton);
    
    // Function to handle fade out
    function fadeOutMessage() {
        successMessage.classList.add('fade-out');
        
        // Remove from DOM after fade animation completes
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 300); // Match the CSS transition duration
    }
    
    // Start auto-fading out after 4 seconds
    setTimeout(() => {
        fadeOutMessage();
    }, 4000); // Show for 4 seconds before auto-fading
    
    // Allow manual dismissal by clicking anywhere on the message
    successMessage.addEventListener('click', fadeOutMessage);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        e.preventDefault();
        alert('Please enter both username and password.');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        return false;
    }
});
</script>

<style>
/* Enhanced success message styling */
#success-message {
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border: 2px solid #22c55e;
}

#success-message svg {
    color: #15803d;
}

/* Smooth fade out animation */
#success-message.fade-out {
    opacity: 0;
    transform: translateY(-10px);
}

/* Pulse animation on first appearance */
@keyframes successPulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.02); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

#success-message {
    animation: successPulse 0.5s ease-out;
}
</style>

<?php include 'includes/footer.php'; ?>
