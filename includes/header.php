<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? $page_title : 'FarmScout Online - Real-Time Agricultural Price Monitoring'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Get real-time prices from Baloan Public Market. Your trusted digital guide for fresh produce, meat, fish, and processed goods with transparent pricing.'; ?>" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="public/manifest.json" />
    <meta name="theme-color" content="#2D5016" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="FarmScout" />
    <link rel="apple-touch-icon" href="public/favicon.ico" />
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/public/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</head>
<body class="bg-background min-h-screen">
    <!-- Navigation Header -->
    <header class="bg-white shadow-minimal sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="#2D5016"/>
                            <path d="M12 20c0-4.4 3.6-8 8-8s8 3.6 8 8-3.6 8-8 8-8-3.6-8-8z" fill="#75A347"/>
                            <path d="M16 18h8v4h-8z" fill="#FF6B35"/>
                            <circle cx="20" cy="20" r="2" fill="white"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-primary font-accent">FarmScout</h1>
                        <p class="text-xs text-text-secondary">Tapat na Presyo</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <?php if (isLoggedIn()): ?>
                        <div class="text-sm text-text-secondary mr-4">
                            Welcome, <span class="font-semibold text-primary"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                        </div>
                        <?php endif; ?>
                        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> px-3 py-2 rounded-md text-sm transition-colors">Home</a>
                        <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> px-3 py-2 rounded-md text-sm transition-colors">Categories</a>
                        <a href="enhanced-search.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'enhanced-search.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> px-3 py-2 rounded-md text-sm transition-colors">Search</a>
                        <a href="price-alerts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'price-alerts.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> px-3 py-2 rounded-md text-sm transition-colors">Price Alerts</a>
                        <a href="quick-check.php" class="btn-accent text-sm px-4 py-2">Quick Check</a>
                        <?php if (isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin.php" class="btn-secondary text-sm px-4 py-2">Admin</a>
                        <a href="analytics.php" class="btn-secondary text-sm px-4 py-2">Analytics</a>
                        <a href="logout.php" class="btn-accent text-sm px-4 py-2">Logout</a>
                        <?php else: ?>
                        <a href="login.php" class="btn-secondary text-sm px-4 py-2">Login</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-text-secondary hover:text-primary p-2" onclick="toggleMobileMenu()">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-surface-50 rounded-lg mt-2">
                    <?php if (isLoggedIn()): ?>
                    <div class="px-3 py-2 text-sm text-text-secondary border-b border-surface-200 mb-2">
                        Welcome, <span class="font-semibold text-primary"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                    </div>
                    <?php endif; ?>
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> block px-3 py-2 rounded-md text-base">Home</a>
                    <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> block px-3 py-2 rounded-md text-base">Categories</a>
                    <a href="enhanced-search.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'enhanced-search.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> block px-3 py-2 rounded-md text-base">Search</a>
                    <a href="price-alerts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'price-alerts.php' ? 'text-primary font-semibold' : 'text-text-secondary hover:text-primary'; ?> block px-3 py-2 rounded-md text-base">Price Alerts</a>
                    <a href="quick-check.php" class="btn-accent block text-center mx-3 my-2">Quick Check</a>
                    <?php if (isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php" class="btn-secondary block text-center mx-3 my-2">Admin</a>
                    <a href="analytics.php" class="btn-secondary block text-center mx-3 my-2">Analytics</a>
                    <a href="logout.php" class="btn-accent block text-center mx-3 my-2">Logout</a>
                    <?php else: ?>
                    <a href="login.php" class="btn-secondary block text-center mx-3 my-2">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>