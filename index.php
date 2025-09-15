<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'FarmScout Online - Real-Time Market Prices | Baloan Public Market';
$page_description = 'Get real-time prices from Baloan Public Market. Your trusted digital guide for fresh produce, meat, fish, and processed goods with transparent pricing.';

// Get data for the page
$featured_products = getFeaturedProducts();
$categories = getCategories();
$market_status = getMarketStatus();

// Handle search
$search_results = [];
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitizeInput($_GET['search']);
    $search_results = searchProducts($search_term);
}

include 'includes/header.php';
?>

    <!-- Moving Text Animation -->
    <div class="moving-text-container">
        <div class="moving-text">
            <span>FARMSCOUT • Tapat na Presyo • FARMSCOUT • Tapat na Presyo • FARMSCOUT • Tapat na Presyo • FARMSCOUT • Tapat na Presyo • FARMSCOUT • Tapat na Presyo • FARMSCOUT • Tapat na Presyo • </span>
        </div>
    </div>

    <!-- Hero Section with Search -->
    <section class="bg-gray-50 min-h-screen flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="text-center mb-8">
                <!-- Letter Reveal Animation -->
                <div class="reveal-container" style="min-height: 200px; overflow: hidden;">
                    <h1 id="reveal-title" class="text-8xl md:text-9xl lg:text-10xl font-black text-gray-900 mb-8 tracking-tighter leading-none" style="font-size: 10rem; line-height: 0.9; font-weight: 900; letter-spacing: -0.03em;">
                        KAPAMILYA SA PALENGKE
                    </h1>
                </div>
                <!-- Delayed Subtitle -->
                <p id="subtitle" class="text-xl md:text-2xl text-gray-600 mb-24 max-w-5xl mx-auto leading-relaxed font-medium" style="opacity: 0; transition: opacity 0.8s ease-in;">
                    Real-time prices from Baloan Public Market. Plan your shopping with confidence and transparency.
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-md mx-auto relative mt-12">
                    <form method="GET" action="index.php">
                        <div class="relative">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search products (e.g., kamatis, bangus)..." class="w-full px-4 py-3 pl-12 pr-4 text-base bg-white border-2 border-gray-300 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="product-search" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Market Status Widget -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-8 max-w-md mx-auto border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-semibold text-gray-900">Baloan Public Market</p>
                            <p class="text-sm text-gray-600">Open • <?php echo $market_status['active_vendors']; ?> vendors active</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Last updated</p>
                        <p class="text-sm font-semibold text-gray-900"><?php echo $market_status['last_updated']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($search_results)): ?>
    <!-- Search Results Section -->
    <section class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-primary">Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>
                <p class="text-text-secondary"><?php echo count($search_results); ?> products found</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <?php foreach ($search_results as $product): 
                    $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                ?>
                <div class="card hover:shadow-elevated transition-shadow">
                    <div class="flex items-center mb-3">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover mr-3" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                        <div>
                            <h3 class="font-semibold text-text-primary"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                            <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['name']); ?></p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-primary"><?php echo formatCurrency($product['current_price']); ?></p>
                            <p class="text-sm text-text-secondary">per <?php echo htmlspecialchars($product['unit']); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center <?php echo $price_change['class']; ?> text-sm">
                                <?php if ($price_change['icon'] == 'up'): ?>
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 15.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                <?php elseif ($price_change['icon'] == 'down'): ?>
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                                <?php echo $price_change['text']; ?>
                            </div>
                            <p class="text-xs text-text-muted">from yesterday</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products Section -->
    <section class="pt-24 md:pt-32 pb-16 md:pb-20 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 scroll-animate" data-delay="0.1">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Today's Featured Prices</h2>
                <div class="text-sm text-gray-500">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Verified by Market Admin
                    </span>
                </div>
            </div>

            <!-- Featured Products Grid -->
            <div class="flex justify-center scroll-animate" data-delay="0.3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl">
                <?php 
                $delay = 0.1;
                foreach ($featured_products as $product): 
                    $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                ?>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 border scroll-animate-card" data-delay="<?php echo $delay; ?>"><?php $delay += 0.1; ?>
                    <div class="flex items-center mb-3">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover mr-3" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMJA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                        <div>
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['name']); ?></p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-green-600"><?php echo formatCurrency($product['current_price']); ?></p>
                            <p class="text-sm text-gray-500">per <?php echo htmlspecialchars($product['unit']); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center text-sm mb-1">
                                <?php if ($price_change['icon'] == 'up'): ?>
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-red-500 font-medium"><?php echo $price_change['text']; ?></span>
                                <?php elseif ($price_change['icon'] == 'down'): ?>
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 15.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-green-500 font-medium"><?php echo $price_change['text']; ?></span>
                                <?php else: ?>
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-500 font-medium"><?php echo $price_change['text']; ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-400">from yesterday</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Navigation -->
    <section class="py-8 bg-surface-50 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-primary mb-6 text-center scroll-animate" data-delay="0.1">Browse by Category</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 scroll-animate" data-delay="0.2">
                <?php 
                $categoryDelay = 0.1;
                foreach ($categories as $category): ?>
                <a href="categories.php?category=<?php echo $category['id']; ?>" class="card hover:shadow-elevated transition-all text-center group scroll-animate-card" data-delay="<?php echo $categoryDelay; ?>"><?php $categoryDelay += 0.05; ?>
                    <div class="w-16 h-16 mx-auto mb-3 bg-primary-100 rounded-full flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                        <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path d="<?php echo htmlspecialchars($category['icon_path']); ?>"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-text-primary mb-1"><?php echo htmlspecialchars($category['filipino_name']); ?></h3>
                    <p class="text-sm text-text-secondary mb-2"><?php echo htmlspecialchars($category['name']); ?></p>
                    <p class="text-xs text-primary font-semibold"><?php echo htmlspecialchars($category['price_range']); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Live Price Table -->
    <section class="py-8 md:py-12 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 scroll-animate" data-delay="0.1">
                <h2 class="text-2xl font-bold text-primary mb-4 md:mb-0">Live Market Prices</h2>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button class="btn-secondary text-sm px-4 py-2">
                        <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                        </svg>
                        Filter
                    </button>
                    <button class="btn-accent text-sm px-4 py-2">
                        <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Set Price Alert
                    </button>
                </div>
            </div>

            <!-- Price Table -->
            <div class="overflow-x-auto scroll-animate" data-delay="0.3">
                <table class="price-table">
                    <thead>
                        <tr>
                            <th class="text-left">Product</th>
                            <th class="text-left">Category</th>
                            <th class="text-right">Current Price</th>
                            <th class="text-right">Yesterday</th>
                            <th class="text-right">Change</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $all_products = getAllProducts();
                        $display_products = array_slice($all_products, 0, 5); // Show first 5 products
                        foreach ($display_products as $product): 
                            $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                        ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-8 h-8 rounded object-cover mr-3" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($product['filipino_name']); ?></p>
                                        <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['name']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-text-secondary"><?php echo htmlspecialchars($product['category_filipino']); ?></td>
                            <td class="text-right font-semibold"><?php echo formatCurrency($product['current_price']); ?>/<?php echo htmlspecialchars($product['unit']); ?></td>
                            <td class="text-right text-text-secondary"><?php echo formatCurrency($product['previous_price']); ?>/<?php echo htmlspecialchars($product['unit']); ?></td>
                            <td class="text-right">
                                <span class="inline-flex items-center <?php echo $price_change['class']; ?> text-sm">
                                    <?php if ($price_change['icon'] == 'up'): ?>
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 15.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php elseif ($price_change['icon'] == 'down'): ?>
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                    <?php echo $price_change['text']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">
                                    Fresh
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center scroll-animate" data-delay="0.5">
                <a href="categories.php" class="btn-primary">
                    View All Products
                    <svg class="w-4 h-4 ml-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="py-8 bg-primary-50 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shopping List Card -->
                <div class="card-elevated scroll-animate-card" data-delay="0.1">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-text-primary">Smart Shopping List</h3>
                            <p class="text-text-secondary">Plan your market visit with current prices</p>
                        </div>
                    </div>
                    <button class="btn-accent w-full">Create Shopping List</button>
                </div>

                <!-- Price Alerts Card -->
                <div class="card-elevated scroll-animate-card" data-delay="0.2">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-text-primary">Price Alerts</h3>
                            <p class="text-text-secondary">Get notified when prices drop</p>
                        </div>
                    </div>
                    <button class="btn-primary w-full">Set Up Alerts</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Moving Text Animation CSS -->
    <style>
        .moving-text-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: #000000;
            color: white;
            z-index: 1000;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .moving-text {
            white-space: nowrap;
            animation: moveText 30s linear infinite;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.1em;
        }
        
        @keyframes moveText {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }
        
        /* Push header down to make room for moving text */
        .modern-header {
            top: 40px !important;
        }
        
        /* Letter Reveal Animation Styles */
        .reveal-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        #reveal-title {
            text-align: center;
            line-height: 0.9;
            display: block;
            max-width: 1200px;
        }
        
        .reveal-letter {
            display: inline-block;
            transform: translateY(100px);
            opacity: 0;
            animation: letterSlideUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        
        .reveal-letter.space {
            display: inline-block;
            width: 0.3em;
        }
        
        @keyframes letterSlideUp {
            0% {
                transform: translateY(100px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Scroll-triggered Animation Styles */
        .scroll-animate {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .scroll-animate.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .scroll-animate-card {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .scroll-animate-card.animate-in {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        /* Stagger animation delays */
        .scroll-animate[data-delay="0.1"] { transition-delay: 0.1s; }
        .scroll-animate[data-delay="0.2"] { transition-delay: 0.2s; }
        .scroll-animate[data-delay="0.3"] { transition-delay: 0.3s; }
        .scroll-animate[data-delay="0.4"] { transition-delay: 0.4s; }
        .scroll-animate[data-delay="0.5"] { transition-delay: 0.5s; }
        
        .scroll-animate-card[data-delay="0.1"] { transition-delay: 0.1s; }
        .scroll-animate-card[data-delay="0.2"] { transition-delay: 0.2s; }
        .scroll-animate-card[data-delay="0.3"] { transition-delay: 0.3s; }
        .scroll-animate-card[data-delay="0.4"] { transition-delay: 0.4s; }
        .scroll-animate-card[data-delay="0.5"] { transition-delay: 0.5s; }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .moving-text {
                font-size: 12px;
            }
            .moving-text-container {
                height: 35px;
                top: 0;
            }
            .modern-header {
                top: 35px !important;
            }
            #reveal-title {
                font-size: 4rem !important;
            }
            .reveal-container {
                min-height: 120px !important;
            }
        }
        
        @media (max-width: 768px) {
            #reveal-title {
                font-size: 6rem !important;
            }
            .reveal-container {
                min-height: 150px !important;
            }
        }
    </style>
    
    <!-- Letter Reveal Animation Script -->
    <script>
        // Word-by-word reveal animation (Baseborn.studio style)
        function initLetterRevealAnimation() {
            const titleElement = document.getElementById('reveal-title');
            const subtitleElement = document.getElementById('subtitle');
            const text = titleElement.textContent.trim();
            
            // Clear the original text
            titleElement.innerHTML = '';
            
            // Split text into words
            const words = text.split(' ');
            
            // Create spans for each word with proper spacing
            words.forEach((word, wordIndex) => {
                const wordSpan = document.createElement('span');
                wordSpan.style.display = 'inline-block';
                
                // Split word into individual letters
                const letters = word.split('');
                letters.forEach((char, letterIndex) => {
                    const letterSpan = document.createElement('span');
                    letterSpan.className = 'reveal-letter';
                    letterSpan.textContent = char;
                    
                    // Calculate stagger delay: word delay + letter delay within word
                    const totalIndex = wordIndex * 10 + letterIndex; // Spread letters across words
                    letterSpan.style.animationDelay = `${totalIndex * 0.03}s`;
                    
                    wordSpan.appendChild(letterSpan);
                });
                
                titleElement.appendChild(wordSpan);
                
                // Add space after word (except for last word)
                if (wordIndex < words.length - 1) {
                    const spaceSpan = document.createElement('span');
                    spaceSpan.innerHTML = '&nbsp;';
                    titleElement.appendChild(spaceSpan);
                }
            });
            
            // Show subtitle after all letters have animated
            setTimeout(() => {
                subtitleElement.style.opacity = '1';
            }, 2000);
        }
        
        // Scroll-triggered animations
        function initScrollAnimations() {
            const animateElements = document.querySelectorAll('.scroll-animate, .scroll-animate-card');
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('animate-in');
                        }, parseFloat(entry.target.dataset.delay || 0) * 1000);
                    }
                });
            }, observerOptions);
            
            animateElements.forEach(element => {
                observer.observe(element);
            });
        }
        
        // Letter-by-letter fill-up animation (existing functionality)
        function initFillUpAnimation() {
            const fillUpElements = document.querySelectorAll('.fill-up-text');
            
            fillUpElements.forEach(element => {
                const text = element.textContent;
                element.innerHTML = '';
                
                // Split text into letters and spaces
                text.split('').forEach((char, index) => {
                    const span = document.createElement('span');
                    span.className = char === ' ' ? 'letter space' : 'letter';
                    span.style.setProperty('--letter-index', index);
                    span.setAttribute('data-letter', char);
                    span.textContent = char;
                    element.appendChild(span);
                });
            });
        }
        
        // Alternative: Convert simple fill-up to letter-by-letter
        function convertToLetterByLetter() {
            const simpleElements = document.querySelectorAll('.simple-fill-up');
            
            simpleElements.forEach(element => {
                element.classList.remove('simple-fill-up');
                element.classList.add('fill-up-text');
                
                const text = element.textContent;
                element.innerHTML = '';
                
                text.split('').forEach((char, index) => {
                    const span = document.createElement('span');
                    span.className = char === ' ' ? 'letter space' : 'letter';
                    span.style.setProperty('--letter-index', index);
                    span.setAttribute('data-letter', char);
                    span.textContent = char;
                    element.appendChild(span);
                });
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Start letter reveal animation
            initLetterRevealAnimation();
            
            // Initialize scroll-triggered animations
            initScrollAnimations();
            
            // Initialize other animations
            initFillUpAnimation();
        });
    </script>

<?php include 'includes/footer.php'; ?>
