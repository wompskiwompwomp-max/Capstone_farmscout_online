<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? $page_title : 'FarmScout Online - Real-Time Agricultural Price Monitoring'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Get real-time prices from Baloan Public Market. Your trusted digital guide for fresh produce, meat, fish, and processed goods with transparent pricing.'; ?>" />
    <link rel="stylesheet" href="css/main.css?v=20250916C" />
    <link rel="stylesheet" href="css/enhancements.css?v=20250916C" />
    <link rel="stylesheet" href="css/admin-enhancements.css?v=20250916C" />
    <!-- Updated 2025-09-15 16:50:00 - Added more spacing between hero and products sections -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    
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
    
    <!-- Logo Animation Styles -->
    <style>
        /* Animated Logo Enhancements */
        .animated-logo {
            border-radius: 8px;
        }
        
        /* Mobile optimization */
        @media (max-width: 640px) {
            .animated-logo {
                height: 2.5rem; /* h-10 equivalent */
                width: 2.5rem;
                border-radius: 6px;
            }
            
        }
        
        /* Tablet optimization */
        @media (min-width: 641px) and (max-width: 768px) {
            .animated-logo {
                height: 3rem; /* h-12 equivalent */
                width: 3rem;
            }
        }
        
        /* Desktop optimization */
        @media (min-width: 769px) {
            .animated-logo {
                height: 3.5rem; /* h-14 equivalent */
                width: 3.5rem;
            }
        }
        
        /* Smooth loading for GIF */
        .animated-logo {
            opacity: 0;
            animation: fadeInLogo 0.5s ease-in forwards;
        }
        
        @keyframes fadeInLogo {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Prefers reduced motion - respect accessibility */
        @media (prefers-reduced-motion: reduce) {
            .animated-logo {
                animation: none;
                opacity: 1;
                transform: none;
            }
            
        }
        
        /* Navigation Underline Reveal Animation */
        .modern-navigation {
            position: relative;
            overflow: hidden;
        }
        
        .modern-navigation::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #000000; /* Black underline */
            transition: width 0.3s ease;
        }
        
        .modern-navigation:hover::before {
            width: 100%;
        }
        
        /* Keep active page underline always visible */
        .modern-navigation.text-black::before {
            width: 100%;
            background-color: #000000;
        }
        
        /* Ensure hover doesn't interfere with active state */
        .modern-navigation.text-black:hover::before {
            width: 100%;
        }
        
        /* Mobile navigation underline animation */
        @media (max-width: 767px) {
            .mobile-nav-item {
                position: relative;
                overflow: hidden;
            }
            
            .mobile-nav-item::before {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 0;
                height: 2px;
                background-color: #000000;
                transition: width 0.3s ease;
            }
            
            .mobile-nav-item:hover::before {
                width: 100%;
            }
            
            .mobile-nav-item.text-gray-900::before {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-background min-h-screen">
    <!-- Navigation Header -->
    <header class="modern-header sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-8">
            <div class="flex items-center h-24">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 mr-12">
                    <div class="flex-shrink-0">
                        <img src="assets/images/gif wazulafu no bg.gif" 
                             alt="FarmScout - Tapat na Presyo" 
                             class="h-12 w-12 md:h-14 md:w-14 object-contain logo-img animated-logo" 
                             onerror="this.src='assets/images/farmscoutlogo.png'; this.onerror=null;" />
                    </div>
                    <div class="ml-4">
                        <h1 class="modern-logo-text">FARMSCOUT</h1>
                        <p class="modern-tagline">Tapat na Presyo</p>
                    </div>
                </div>

                <!-- Desktop Navigation - Center -->
                <div class="flex items-center space-x-12 flex-1 justify-center px-8">
                    <a href="index.php" class="modern-navigation <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-black' : 'text-gray-500 hover:text-black'; ?> transition-colors duration-200 whitespace-nowrap px-3 py-2">
                        HOME
                    </a>
                    <a href="categories.php" class="modern-navigation <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-black' : 'text-gray-500 hover:text-black'; ?> transition-colors duration-200 whitespace-nowrap px-3 py-2">
                        CATEGORIES
                    </a>
                    <a href="enhanced-search.php" class="modern-navigation <?php echo basename($_SERVER['PHP_SELF']) == 'enhanced-search.php' ? 'text-black' : 'text-gray-500 hover:text-black'; ?> transition-colors duration-200 whitespace-nowrap px-3 py-2">
                        SEARCH
                    </a>
                    <a href="price-alerts.php" class="modern-navigation <?php echo basename($_SERVER['PHP_SELF']) == 'price-alerts.php' ? 'text-black' : 'text-gray-500 hover:text-black'; ?> transition-colors duration-200 whitespace-nowrap px-3 py-2">
                        PRICE ALERTS
                    </a>
                    <a href="shopping-list.php" class="modern-navigation <?php echo basename($_SERVER['PHP_SELF']) == 'shopping-list.php' ? 'text-black' : 'text-gray-500 hover:text-black'; ?> transition-colors duration-200 whitespace-nowrap px-3 py-2 relative">
                        SHOPPING LIST
                        <?php 
                        $shopping_count = getShoppingListCount(session_id());
                        if ($shopping_count > 0): 
                        ?>
                        <span class="absolute -top-1 -right-1 bg-accent text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold"><?php echo min($shopping_count, 99); ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Right Section: Action Buttons -->
                <div class="flex items-center flex-shrink-0 ml-12 px-4">
                    <a href="quick-check.php" class="action-btn modern-button modern-button-primary mr-4">
                        QUICK CHECK
                    </a>
                    <?php if (isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php" class="action-btn modern-button modern-button-primary mr-4">
                        ADMIN
                    </a>
                    <a href="analytics.php" class="action-btn modern-button modern-button-primary mr-4">
                        ANALYTICS
                    </a>
                    <a href="logout.php" class="modern-button modern-button-secondary ml-4">
                        LOGOUT
                    </a>
                    <?php else: ?>
                    <a href="login.php" class="modern-button modern-button-secondary ml-4">
                        LOGIN
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="hidden">
                    <button type="button" class="text-gray-600 hover:text-gray-900 p-2" onclick="toggleMobileMenu()">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 bg-white">
                <div class="px-6 pt-4 pb-6 space-y-4">
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900'; ?> block text-sm font-medium transition-colors duration-200 mobile-nav-item">
                        HOME
                    </a>
                    <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900'; ?> block text-sm font-medium transition-colors duration-200 mobile-nav-item">
                        CATEGORIES
                    </a>
                    <a href="enhanced-search.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'enhanced-search.php' ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900'; ?> block text-sm font-medium transition-colors duration-200 mobile-nav-item">
                        SEARCH
                    </a>
                    <a href="price-alerts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'price-alerts.php' ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900'; ?> block text-sm font-medium transition-colors duration-200 mobile-nav-item">
                        PRICE ALERTS
                    </a>
                    <a href="shopping-list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'shopping-list.php' ? 'text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900'; ?> block text-sm font-medium transition-colors duration-200 mobile-nav-item">
                        SHOPPING LIST
                    </a>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <a href="quick-check.php" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-3">
                            QUICK CHECK
                        </a>
                        <?php if (isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin.php" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-3">
                            ADMIN
                        </a>
                        <a href="analytics.php" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-3">
                            ANALYTICS
                        </a>
                        <a href="logout.php" class="bg-lime-400 hover:bg-lime-500 text-gray-900 px-6 py-2 rounded text-sm font-semibold transition-colors duration-200 inline-block">
                            LOGOUT
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="bg-lime-400 hover:bg-lime-500 text-gray-900 px-6 py-2 rounded text-sm font-semibold transition-colors duration-200 inline-block">
                            LOGIN
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }
    </script>
