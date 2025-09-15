<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Advanced Search - FarmScout Online';
$page_description = 'Advanced product search with filtering and sorting options';

// Track page view
trackPageView('enhanced_search');

// Get search parameters
$search_term = sanitizeInput($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$sort_by = sanitizeInput($_GET['sort'] ?? 'relevance');
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$featured_only = isset($_GET['featured']) ? true : false;

// Get data
$categories = getCategories();
$products = [];
$total_results = 0;

if (!empty($search_term)) {
    // Track search
    trackSearch($search_term);
    
    $products = searchProducts($search_term, $category_id, $sort_by);
    
    // Apply additional filters
    if ($min_price !== null || $max_price !== null || $featured_only) {
        $products = array_filter($products, function($product) use ($min_price, $max_price, $featured_only) {
            if ($min_price !== null && $product['current_price'] < $min_price) return false;
            if ($max_price !== null && $product['current_price'] > $max_price) return false;
            if ($featured_only && !$product['is_featured']) return false;
            return true;
        });
    }
    
    $total_results = count($products);
}

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 scroll-animate">
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                    Advanced Product Search
                </h1>
                <p class="text-lg text-text-secondary mb-6 max-w-2xl mx-auto">
                    Find exactly what you're looking for with our advanced search and filtering options.
                </p>
            </div>
        </div>
    </section>

    <!-- Search Form -->
    <section class="py-8 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-card p-6 mb-8 scroll-animate-card" data-delay="0.1">
                <form method="GET" action="enhanced-search.php" class="space-y-6">
                    <!-- Search Term -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-text-primary mb-2">Search Products</label>
                        <div class="relative">
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   placeholder="Enter product name (e.g., kamatis, bangus)..." 
                                   class="input-field pl-12 w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-text-primary mb-2">Category</label>
                            <select id="category" name="category" class="input-field">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['filipino_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label for="sort" class="block text-sm font-medium text-text-primary mb-2">Sort By</label>
                            <select id="sort" name="sort" class="input-field">
                                <option value="relevance" <?php echo $sort_by == 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                                <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label for="min_price" class="block text-sm font-medium text-text-primary mb-2">Min Price (₱)</label>
                            <input type="number" id="min_price" name="min_price" 
                                   value="<?php echo $min_price !== null ? $min_price : ''; ?>"
                                   step="0.01" min="0" class="input-field" placeholder="0.00">
                        </div>

                        <div>
                            <label for="max_price" class="block text-sm font-medium text-text-primary mb-2">Max Price (₱)</label>
                            <input type="number" id="max_price" name="max_price" 
                                   value="<?php echo $max_price !== null ? $max_price : ''; ?>"
                                   step="0.01" min="0" class="input-field" placeholder="1000.00">
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" value="1" 
                                   <?php echo $featured_only ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <span class="ml-2 text-sm text-text-primary">Featured products only</span>
                        </label>
                    </div>

                    <!-- Search Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="btn-primary px-8">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search Products
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php if (!empty($search_term)): ?>
    <!-- Search Results -->
    <section class="py-8 bg-surface-50 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6 scroll-animate" data-delay="0.1">
                <div>
                    <h2 class="text-2xl font-bold text-primary">Search Results</h2>
                    <p class="text-text-secondary">
                        Found <?php echo $total_results; ?> product<?php echo $total_results != 1 ? 's' : ''; ?> 
                        for "<?php echo htmlspecialchars($search_term); ?>"
                    </p>
                </div>
                
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-text-secondary">Sort by:</span>
                    <span class="text-sm font-medium text-primary">
                        <?php 
                        $sort_labels = [
                            'relevance' => 'Relevance',
                            'price_low' => 'Price: Low to High',
                            'price_high' => 'Price: High to Low',
                            'name' => 'Name: A to Z'
                        ];
                        echo $sort_labels[$sort_by] ?? 'Relevance';
                        ?>
                    </span>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-text-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-text-primary mb-2">No products found</h3>
                <p class="text-text-secondary mb-4">Try adjusting your search criteria or filters.</p>
                <a href="enhanced-search.php" class="btn-secondary">Clear Search</a>
            </div>
            <?php else: ?>
            
            <!-- Results Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 scroll-animate" data-delay="0.3">
                <?php 
                $productDelay = 0.1;
                foreach ($products as $product): 
                    $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                ?>
                <div class="card hover:shadow-elevated transition-shadow scroll-animate-card" data-delay="<?php echo $productDelay; ?>"><?php $productDelay += 0.05; ?>
                    <div class="relative mb-4">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-48 object-cover rounded-lg" 
                             onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                        
                        <?php if ($product['is_featured']): ?>
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent text-white">
                                Featured
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="absolute bottom-2 left-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                                <?php echo htmlspecialchars($product['category_filipino']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h3 class="font-semibold text-text-primary text-lg mb-1"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                        <p class="text-sm text-text-secondary mb-2"><?php echo htmlspecialchars($product['name']); ?></p>
                        <?php if ($product['description']): ?>
                        <p class="text-sm text-text-secondary"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex justify-between items-center mb-3">
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
                    
                    <div class="flex space-x-2">
                        <button onclick="addToShoppingList(<?php echo $product['id']; ?>)" 
                                class="btn-secondary flex-1 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                            </svg>
                            Add to List
                        </button>
                        <button onclick="setPriceAlert(<?php echo $product['id']; ?>)" 
                                class="btn-accent flex-1 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                            </svg>
                            Price Alert
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick Search Suggestions -->
    <section class="py-8 scroll-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-primary mb-6 text-center scroll-animate" data-delay="0.1">Popular Searches</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php 
                $popular_searches = [
                    'Kamatis' => 'Tomatoes',
                    'Bangus' => 'Milkfish',
                    'Bigas' => 'Rice',
                    'Sibuyas' => 'Onions',
                    'Manok' => 'Chicken',
                    'Baboy' => 'Pork',
                    'Tilapia' => 'Tilapia',
                    'Saging' => 'Bananas',
                    'Repolyo' => 'Cabbage',
                    'Mantika' => 'Cooking Oil',
                    'Itlog' => 'Eggs',
                    'Gatas' => 'Milk'
                ];
                
                $searchDelay = 0.1;
                foreach ($popular_searches as $filipino => $english): ?>
                <a href="enhanced-search.php?search=<?php echo urlencode($filipino); ?>" 
                   class="bg-white rounded-lg shadow-card p-4 text-center hover:shadow-elevated transition-shadow scroll-animate-card" data-delay="<?php echo $searchDelay; ?>"><?php $searchDelay += 0.05; ?>
                    <h3 class="font-semibold text-text-primary mb-1"><?php echo $filipino; ?></h3>
                    <p class="text-sm text-text-secondary"><?php echo $english; ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<script>
function addToShoppingList(productId) {
    // Implementation for adding to shopping list
    alert('Added to shopping list! (Feature coming soon)');
}

function setPriceAlert(productId) {
    // Implementation for setting price alert
    alert('Price alert set! (Feature coming soon)');
}

// Auto-submit form when filters change
document.getElementById('category').addEventListener('change', function() {
    if (document.getElementById('search').value.trim()) {
        this.form.submit();
    }
});

document.getElementById('sort').addEventListener('change', function() {
    if (document.getElementById('search').value.trim()) {
        this.form.submit();
    }
});
</script>

    <!-- Scroll Animation Styles -->
    <style>
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
        .scroll-animate-card[data-delay="0.6"] { transition-delay: 0.6s; }
        .scroll-animate-card[data-delay="0.7"] { transition-delay: 0.7s; }
        .scroll-animate-card[data-delay="0.8"] { transition-delay: 0.8s; }
    </style>

    <!-- Scroll Animation Script -->
    <script>
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
        
        // Initialize scroll animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            initScrollAnimations();
        });
    </script>

<?php include 'includes/footer.php'; ?>
