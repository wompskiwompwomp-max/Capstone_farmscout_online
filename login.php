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
            <div class="mx-auto h-16 w-16 flex items-center justify-center">
                <svg class="h-16 w-16" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" fill="#2D5016"/>
                    <path d="M12 20c0-4.4 3.6-8 8-8s8 3.6 8 8-3.6 8-8 8-8-3.6-8-8z" fill="#75A347"/>
                    <path d="M16 18h8v4h-8z" fill="#FF6B35"/>
                    <circle cx="20" cy="20" r="2" fill="white"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-primary">
                Sign in to FarmScout
            </h2>
            <p class="mt-2 text-center text-sm text-text-secondary">
                Access the admin panel to manage products and prices
            </p>
        </div>
        
        <div class="bg-white py-8 px-6 shadow-card rounded-lg">
            <?php if ($error_message): ?>
            <div class="mb-4 p-4 bg-error-100 border border-error-300 text-error-700 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="mb-4 p-4 bg-success-100 border border-success-300 text-success-700 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <form class="space-y-6" method="POST" action="login.php">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
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
                    <button type="submit" class="btn-primary w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
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
                    <p class="text-sm text-text-secondary mb-2">Contact your administrator for access credentials.</p>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="index.php" class="text-primary hover:text-primary-600 font-medium">
                ← Back to Homepage
            </a>
        </div>
    </div>
</div>

<script>
// Auto-focus on username field
document.getElementById('username').focus();

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

<?php include 'includes/footer.php'; ?>
