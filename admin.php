<?php
require_once 'includes/enhanced_functions.php';

// Require admin authentication
requireAdmin();

$page_title = 'Admin Panel - FarmScout Online';
$page_description = 'Manage products and prices for FarmScout Online';

// Track page view
trackPageView('admin');

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'filipino_name' => sanitizeInput($_POST['filipino_name']),
                    'description' => sanitizeInput($_POST['description']),
                    'category_id' => intval($_POST['category_id']),
                    'current_price' => floatval($_POST['current_price']),
                    'previous_price' => floatval($_POST['previous_price']),
                    'unit' => sanitizeInput($_POST['unit']),
                    'image_url' => sanitizeInput($_POST['image_url']),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if (addProduct($data)) {
                    $message = 'Product added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error adding product.';
                    $message_type = 'error';
                }
                break;
                
            case 'update_product':
                $product_id = intval($_POST['product_id']);
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'filipino_name' => sanitizeInput($_POST['filipino_name']),
                    'description' => sanitizeInput($_POST['description']),
                    'category_id' => intval($_POST['category_id']),
                    'current_price' => floatval($_POST['current_price']),
                    'previous_price' => floatval($_POST['previous_price']),
                    'unit' => sanitizeInput($_POST['unit']),
                    'image_url' => sanitizeInput($_POST['image_url']),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if (updateProduct($product_id, $data)) {
                    $message = 'Product updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error updating product.';
                    $message_type = 'error';
                }
                break;
                
            case 'delete_product':
                $product_id = intval($_POST['product_id']);
                if (deleteProduct($product_id)) {
                    $message = 'Product deleted successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error deleting product.';
                    $message_type = 'error';
                }
                break;
                
            case 'add_category':
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'filipino_name' => sanitizeInput($_POST['filipino_name']),
                    'description' => sanitizeInput($_POST['description']),
                    'icon_path' => sanitizeInput($_POST['icon_path']),
                    'price_range' => sanitizeInput($_POST['price_range']),
                    'sort_order' => intval($_POST['sort_order'])
                ];
                
                if (addCategory($data)) {
                    $message = 'Category added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error adding category.';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get view preference
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'table';
$current_category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$current_category = $current_category_id ? getCategoryById($current_category_id) : null;

$categories = getCategories();
$products = getAllProducts();

// Get category-based data
$category_overview = getProductsByCategories();
$products_in_category = $current_category_id ? getProductsByCategoryForAdmin($current_category_id) : [];

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-primary mb-2">
                    <?php if ($current_category && $view_mode === 'categories'): ?>
                        Manage <?php echo htmlspecialchars($current_category['filipino_name']); ?> Products
                    <?php else: ?>
                        Admin Panel
                    <?php endif; ?>
                </h1>
                <p class="text-text-secondary">
                    <?php if ($current_category && $view_mode === 'categories'): ?>
                        Manage products in the <?php echo htmlspecialchars($current_category['filipino_name']); ?> category
                    <?php else: ?>
                        Manage products and prices for FarmScout Online
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($current_category && $view_mode === 'categories'): ?>
            <div class="flex gap-3">
                <a href="admin.php?view=categories" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L4.414 9H17a1 1 0 110 2H4.414l5.293 5.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Back to Categories
                </a>
                <button onclick="showAddProductModal()" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Product
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- View Toggle Tabs -->
        <?php if (!$current_category): ?>
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-16">
                <a href="admin.php?view=table" 
                   class="<?php echo $view_mode === 'table' ? 'border-primary text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-gray-300'; ?> py-3 px-4 border-b-2 font-medium text-base transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
                    </svg>
                    Traditional View
                </a>
                <a href="admin.php?view=categories" 
                   class="<?php echo $view_mode === 'categories' ? 'border-primary text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-gray-300'; ?> py-3 px-4 border-b-2 font-medium text-base transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    Category View
                </a>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700' : 'bg-error-100 text-error-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <?php if ($view_mode === 'categories' && !$current_category): ?>
    <!-- Categories Overview -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-primary">Product Categories</h2>
            <button onclick="showAddCategoryModal()" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Add Category
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($category_overview as $category): ?>
            <div class="bg-white rounded-lg shadow-card hover:shadow-elevated transition-shadow">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path d="<?php echo htmlspecialchars($category['icon_path']); ?>"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-text-primary"><?php echo htmlspecialchars($category['category_filipino']); ?></h3>
                            <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($category['category_name']); ?></p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-text-secondary mb-4"><?php echo htmlspecialchars($category['category_description']); ?></p>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm">
                            <span class="font-semibold text-primary"><?php echo $category['product_count']; ?></span>
                            <span class="text-text-secondary">products</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-semibold text-accent"><?php echo htmlspecialchars($category['price_range']); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="admin.php?view=categories&category=<?php echo $category['category_id']; ?>" 
                           class="flex-1 btn-primary text-center text-sm py-2">
                            Manage Products
                        </a>
                        <button onclick="editCategory(<?php echo $category['category_id']; ?>)" 
                                class="btn-secondary text-sm py-2 px-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php elseif ($view_mode === 'categories' && $current_category): ?>
    <!-- Products in Selected Category -->
    <div class="bg-white rounded-lg shadow-card p-6 mb-8">
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                    <path d="<?php echo htmlspecialchars($current_category['icon_path']); ?>"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary"><?php echo htmlspecialchars($current_category['filipino_name']); ?></h2>
                <p class="text-text-secondary"><?php echo htmlspecialchars($current_category['description']); ?></p>
                <p class="text-sm text-accent font-semibold"><?php echo htmlspecialchars($current_category['price_range']); ?></p>
            </div>
        </div>

        <?php if (empty($products_in_category)): ?>
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-surface-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-text-muted" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-text-primary mb-2">No products in this category</h3>
            <p class="text-text-secondary mb-4">Start by adding some products to this category.</p>
            <button onclick="showAddProductModal()" class="btn-primary">
                Add First Product
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products_in_category as $product): 
                $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
            ?>
            <div class="border border-surface-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start mb-3">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-16 h-16 rounded-lg object-cover mr-3" 
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-text-primary truncate"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                        <p class="text-sm text-text-secondary truncate"><?php echo htmlspecialchars($product['name']); ?></p>
                        <?php if ($product['is_featured']): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-accent-100 text-accent-700 mt-1">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Featured
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <p class="text-xl font-bold text-primary"><?php echo formatCurrency($product['current_price']); ?></p>
                        <p class="text-sm text-text-secondary">per <?php echo htmlspecialchars($product['unit']); ?></p>
                    </div>
                    <div class="text-right text-sm">
                        <div class="flex items-center <?php echo $price_change['class']; ?>">
                            <?php if ($price_change['icon'] == 'up'): ?>
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            <?php elseif ($price_change['icon'] == 'down'): ?>
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 15.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            <?php endif; ?>
                            <?php echo $price_change['text']; ?>
                        </div>
                        <p class="text-xs text-text-muted">from yesterday</p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                            class="flex-1 btn-secondary text-sm py-2">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit
                    </button>
                    <form method="POST" action="admin.php?view=categories&category=<?php echo $current_category['id']; ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn-error text-sm py-2 px-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v6a1 1 0 11-2 0V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M5 3a1 1 0 000 2h10a1 1 0 100-2H5zM4 7v10a2 2 0 002 2h8a2 2 0 002-2V7H4z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Traditional Table View -->
    <!-- Add Product Form -->
    <div class="bg-white rounded-lg shadow-card p-6 mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Add New Product</h2>
        <form method="POST" action="admin.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="add_product">
            
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary mb-2">Product Name (English)</label>
                <input type="text" id="name" name="name" required class="input-field">
            </div>
            
            <div>
                <label for="filipino_name" class="block text-sm font-medium text-text-primary mb-2">Filipino Name</label>
                <input type="text" id="filipino_name" name="filipino_name" required class="input-field">
            </div>
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-text-primary mb-2">Description</label>
                <textarea id="description" name="description" rows="3" class="input-field"></textarea>
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-text-primary mb-2">Category</label>
                <select id="category_id" name="category_id" required class="input-field">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['filipino_name']); ?> (<?php echo htmlspecialchars($category['name']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="unit" class="block text-sm font-medium text-text-primary mb-2">Unit</label>
                <select id="unit" name="unit" required class="input-field">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="piece">Piece</option>
                    <option value="pack">Pack</option>
                    <option value="bundle">Bundle</option>
                </select>
            </div>
            
            <div>
                <label for="current_price" class="block text-sm font-medium text-text-primary mb-2">Current Price (₱)</label>
                <input type="number" id="current_price" name="current_price" step="0.01" min="0" required class="input-field">
            </div>
            
            <div>
                <label for="previous_price" class="block text-sm font-medium text-text-primary mb-2">Previous Price (₱)</label>
                <input type="number" id="previous_price" name="previous_price" step="0.01" min="0" class="input-field">
            </div>
            
            <div class="md:col-span-2">
                <label for="image_url" class="block text-sm font-medium text-text-primary mb-2">Image URL</label>
                <input type="url" id="image_url" name="image_url" class="input-field" placeholder="https://example.com/image.jpg">
            </div>
            
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" class="mr-2">
                    <span class="text-sm font-medium text-text-primary">Featured Product</span>
                </label>
            </div>
            
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary">Add Product</button>
            </div>
        </form>
    </div>

    <!-- Products List -->
    <div class="bg-white rounded-lg shadow-card p-6">
        <h2 class="text-xl font-semibold text-primary mb-4">Manage Products</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Product</th>
                        <th class="text-left py-3 px-4">Category</th>
                        <th class="text-right py-3 px-4">Current Price</th>
                        <th class="text-right py-3 px-4">Previous Price</th>
                        <th class="text-center py-3 px-4">Featured</th>
                        <th class="text-center py-3 px-4 min-w-[150px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr class="border-b hover:bg-surface-50">
                        <td class="py-3 px-4">
                            <div class="flex items-center">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-10 h-10 rounded object-cover mr-3" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($product['filipino_name']); ?></p>
                                    <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['name']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-text-secondary"><?php echo htmlspecialchars($product['category_filipino']); ?></td>
                        <td class="py-3 px-4 text-right font-semibold"><?php echo formatCurrency($product['current_price']); ?></td>
                        <td class="py-3 px-4 text-right text-text-secondary"><?php echo formatCurrency($product['previous_price']); ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($product['is_featured']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent-100 text-accent-700">Featured</span>
                            <?php else: ?>
                                <span class="text-text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2 admin-btn-group">
                                <button 
                                    onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                    class="modern-button modern-button-primary admin-compact-btn">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="admin.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="modern-button modern-button-danger admin-compact-btn">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Product</h3>
                <button onclick="hideAddProductModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="add_product">
                <?php if ($current_category): ?>
                <input type="hidden" name="category_id" value="<?php echo $current_category['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="filipino_name_modal" class="block text-sm font-medium text-text-primary mb-2">Filipino Name *</label>
                    <input type="text" id="filipino_name_modal" name="filipino_name" required class="input-field">
                </div>
                
                <div>
                    <label for="name_modal" class="block text-sm font-medium text-text-primary mb-2">English Name</label>
                    <input type="text" id="name_modal" name="name" class="input-field">
                </div>
                
                <?php if (!$current_category): ?>
                <div>
                    <label for="category_id_modal" class="block text-sm font-medium text-text-primary mb-2">Category *</label>
                    <select id="category_id_modal" name="category_id" required class="input-field">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['filipino_name']); ?> (<?php echo htmlspecialchars($category['name']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <label for="unit_modal" class="block text-sm font-medium text-text-primary mb-2">Unit *</label>
                    <select id="unit_modal" name="unit" required class="input-field">
                        <option value="kg">Kilogram (kg)</option>
                        <option value="piece">Piece</option>
                        <option value="pack">Pack</option>
                        <option value="bundle">Bundle</option>
                        <option value="liter">Liter</option>
                    </select>
                </div>
                
                <div>
                    <label for="current_price_modal" class="block text-sm font-medium text-text-primary mb-2">Current Price (₱) *</label>
                    <input type="number" id="current_price_modal" name="current_price" step="0.01" min="0" required class="input-field">
                </div>
                
                <div>
                    <label for="previous_price_modal" class="block text-sm font-medium text-text-primary mb-2">Previous Price (₱)</label>
                    <input type="number" id="previous_price_modal" name="previous_price" step="0.01" min="0" class="input-field">
                </div>
                
                <div class="md:col-span-2">
                    <label for="description_modal" class="block text-sm font-medium text-text-primary mb-2">Description</label>
                    <textarea id="description_modal" name="description" rows="3" class="input-field"></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label for="image_url_modal" class="block text-sm font-medium text-text-primary mb-2">Image URL</label>
                    <input type="url" id="image_url_modal" name="image_url" class="input-field" placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" class="mr-2">
                        <span class="text-sm font-medium text-text-primary">Featured Product</span>
                    </label>
                </div>
                
                <div class="md:col-span-2 flex justify-end gap-3 mt-4">
                    <button type="button" onclick="hideAddProductModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Category</h3>
                <button onclick="hideAddCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="admin.php?view=categories" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="add_category">
                
                <div>
                    <label for="filipino_name_cat" class="block text-sm font-medium text-text-primary mb-2">Filipino Name *</label>
                    <input type="text" id="filipino_name_cat" name="filipino_name" required class="input-field">
                </div>
                
                <div>
                    <label for="name_cat" class="block text-sm font-medium text-text-primary mb-2">English Name *</label>
                    <input type="text" id="name_cat" name="name" required class="input-field">
                </div>
                
                <div class="md:col-span-2">
                    <label for="description_cat" class="block text-sm font-medium text-text-primary mb-2">Description</label>
                    <textarea id="description_cat" name="description" rows="3" class="input-field"></textarea>
                </div>
                
                <div>
                    <label for="price_range_cat" class="block text-sm font-medium text-text-primary mb-2">Price Range</label>
                    <input type="text" id="price_range_cat" name="price_range" class="input-field" placeholder="₱25-₱120/kg">
                </div>
                
                <div>
                    <label for="sort_order_cat" class="block text-sm font-medium text-text-primary mb-2">Sort Order</label>
                    <input type="number" id="sort_order_cat" name="sort_order" min="0" class="input-field" value="0">
                </div>
                
                <div class="md:col-span-2">
                    <label for="icon_path_cat" class="block text-sm font-medium text-text-primary mb-2">SVG Icon Path</label>
                    <textarea id="icon_path_cat" name="icon_path" rows="3" class="input-field" placeholder="SVG path data for the category icon"></textarea>
                </div>
                
                <div class="md:col-span-2 flex justify-end gap-3 mt-4">
                    <button type="button" onclick="hideAddCategoryModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-primary">Edit Product</h3>
                    <button onclick="closeEditModal()" class="text-text-muted hover:text-text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="editForm" method="POST" action="admin.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-text-primary mb-2">Product Name (English)</label>
                        <input type="text" id="edit_name" name="name" required class="input-field">
                    </div>
                    
                    <div>
                        <label for="edit_filipino_name" class="block text-sm font-medium text-text-primary mb-2">Filipino Name</label>
                        <input type="text" id="edit_filipino_name" name="filipino_name" required class="input-field">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="edit_description" class="block text-sm font-medium text-text-primary mb-2">Description</label>
                        <textarea id="edit_description" name="description" rows="3" class="input-field"></textarea>
                    </div>
                    
                    <div>
                        <label for="edit_category_id" class="block text-sm font-medium text-text-primary mb-2">Category</label>
                        <select id="edit_category_id" name="category_id" required class="input-field">
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['filipino_name']); ?> (<?php echo htmlspecialchars($category['name']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="edit_unit" class="block text-sm font-medium text-text-primary mb-2">Unit</label>
                        <select id="edit_unit" name="unit" required class="input-field">
                            <option value="kg">Kilogram (kg)</option>
                            <option value="piece">Piece</option>
                            <option value="pack">Pack</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="edit_current_price" class="block text-sm font-medium text-text-primary mb-2">Current Price (₱)</label>
                        <input type="number" id="edit_current_price" name="current_price" step="0.01" min="0" required class="input-field">
                    </div>
                    
                    <div>
                        <label for="edit_previous_price" class="block text-sm font-medium text-text-primary mb-2">Previous Price (₱)</label>
                        <input type="number" id="edit_previous_price" name="previous_price" step="0.01" min="0" class="input-field">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="edit_image_url" class="block text-sm font-medium text-text-primary mb-2">Image URL</label>
                        <input type="url" id="edit_image_url" name="image_url" class="input-field">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" id="edit_is_featured" name="is_featured" class="mr-2">
                            <span class="text-sm font-medium text-text-primary">Featured Product</span>
                        </label>
                    </div>
                    
                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit" class="btn-primary">Update Product</button>
                        <button type="button" onclick="closeEditModal()" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Product modal functions
function showAddProductModal() {
    document.getElementById('addProductModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideAddProductModal() {
    document.getElementById('addProductModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Category modal functions
function showAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_filipino_name').value = product.filipino_name;
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_unit').value = product.unit;
    document.getElementById('edit_current_price').value = product.current_price;
    document.getElementById('edit_previous_price').value = product.previous_price;
    document.getElementById('edit_image_url').value = product.image_url || '';
    document.getElementById('edit_is_featured').checked = product.is_featured == 1;
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function editCategory(categoryId) {
    // Implement category editing in the future
    alert('Category editing will be implemented soon.');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const addProductModal = document.getElementById('addProductModal');
    const addCategoryModal = document.getElementById('addCategoryModal');
    
    if (event.target == editModal) {
        closeEditModal();
    } else if (event.target == addProductModal) {
        hideAddProductModal();
    } else if (event.target == addCategoryModal) {
        hideAddCategoryModal();
    }
}

// Close modal when clicking outside (fallback)
document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>