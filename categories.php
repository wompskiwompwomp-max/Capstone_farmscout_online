<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Product Categories - FarmScout Online';
$page_description = 'Browse products by category from Baloan Public Market';

// Get category filter
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : null;

// Get data
$categories = getCategories();
$products = $selected_category ? getProductsByCategory($selected_category) : getAllProducts();

// Get selected category info
$current_category = null;
if ($selected_category) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $selected_category) {
            $current_category = $cat;
            break;
        }
    }
}

include 'includes/header.php';
?>

    <!-- Header Section -->
    <section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                    <?php echo $current_category ? htmlspecialchars($current_category['filipino_name']) . ' (' . htmlspecialchars($current_category['name']) . ')' : 'All Product Categories'; ?>
                </h1>
                <p class="text-lg text-text-secondary mb-6 max-w-2xl mx-auto">
                    <?php echo $current_category ? htmlspecialchars($current_category['description']) : 'Browse all products available at Baloan Public Market'; ?>
                </p>
                
                <?php if ($current_category): ?>
                <a href="categories.php" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L4.414 9H17a1 1 0 110 2H4.414l5.293 5.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Back to All Categories
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (!$current_category): ?>
    <!-- Category Grid -->
    <section class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-primary mb-6 text-center">Browse by Category</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $category): ?>
                <a href="categories.php?category=<?php echo $category['id']; ?>" class="card-elevated hover:shadow-elevated transition-all group">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center group-hover:bg-primary-200 transition-colors mr-4">
                            <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path d="<?php echo htmlspecialchars($category['icon_path']); ?>"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-text-primary mb-1"><?php echo htmlspecialchars($category['filipino_name']); ?></h3>
                            <p class="text-sm text-text-secondary mb-2"><?php echo htmlspecialchars($category['name']); ?></p>
                            <p class="text-xs text-primary font-semibold"><?php echo htmlspecialchars($category['price_range']); ?></p>
                        </div>
                    </div>
                    <p class="text-text-secondary"><?php echo htmlspecialchars($category['description']); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products Section -->
    <section class="py-8 md:py-12 <?php echo !$current_category ? 'bg-surface-50' : ''; ?>">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-primary">
                    <?php echo $current_category ? 'Products in this Category' : 'All Products'; ?>
                </h2>
                <p class="text-text-secondary"><?php echo count($products); ?> products available</p>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-text-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-lg font-semibold text-text-primary mb-2">No products found</h3>
                <p class="text-text-secondary">There are no products available in this category at the moment.</p>
            </div>
            <?php else: ?>
            
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($products as $product): 
                    $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                ?>
                <div class="card hover:shadow-elevated transition-shadow">
                    <div class="relative mb-4">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover rounded-lg" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                        <?php if ($product['is_featured']): ?>
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent text-white">
                                Featured
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <h3 class="font-semibold text-text-primary text-lg mb-1"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                        <p class="text-sm text-text-secondary mb-2"><?php echo htmlspecialchars($product['name']); ?></p>
                        <?php if (!$current_category): ?>
                        <p class="text-xs text-primary font-medium"><?php echo htmlspecialchars($product['category_filipino']); ?></p>
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
                    
                    <?php if ($product['description']): ?>
                    <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>