<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Shopping List - FarmScout Online';
$page_description = 'Create and manage your shopping lists with real-time prices from Baloan Public Market';

// Handle AJAX search suggestions and shopping list requests
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    if (isset($_GET['get_shopping_list']) && $_GET['get_shopping_list'] === '1') {
        // Get shopping list data
        $session_id = session_id();
        
        // Debug output for AJAX shopping list request
        if (isset($_GET['debug'])) {
            error_log("AJAX Shopping List Debug - Session ID: $session_id, Session status: " . 
                     (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not active'));
        }
        
        $shopping_list = getShoppingList($session_id);
        $total_amount = 0;
        $total_items = 0;
        
        foreach ($shopping_list as $item) {
            $item_total = floatval($item['current_price']) * intval($item['quantity']);
            $total_amount += $item_total;
            $total_items += intval($item['quantity']);
        }
        
        echo json_encode([
            'items' => $shopping_list,
            'total_items' => $total_items,
            'total_amount' => $total_amount
        ]);
        
    } elseif (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
        // Search by category ID
        $category_id = intval($_GET['category_id']);
        $results = getProductsByCategory($category_id);
        
        $formatted_results = [];
        foreach ($results as $product) {
            $formatted_results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'filipino_name' => $product['filipino_name'],
                'current_price' => $product['current_price'],
                'unit' => $product['unit'],
                'image_url' => $product['image_url'],
                'category' => $product['category_filipino'] ?? 'Unknown'
            ];
        }
        
        echo json_encode($formatted_results);
    } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
        // Search by text
        $search_term = sanitizeInput($_GET['search']);
        $results = searchProducts($search_term);
        
        $formatted_results = [];
        foreach ($results as $product) {
            $formatted_results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'filipino_name' => $product['filipino_name'],
                'current_price' => $product['current_price'],
                'unit' => $product['unit'],
                'image_url' => $product['image_url'],
                'category' => $product['category_filipino'] ?? 'Unknown'
            ];
        }
        
        echo json_encode($formatted_results);
    } else {
        echo json_encode([]);
    }
    exit;
}

// Track page view
trackPageView('shopping_list');

$session_id = session_id();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug session info for POST requests
    if (isset($_GET['debug']) || isset($_POST['debug'])) {
        error_log("POST Handler Debug - Session ID: $session_id, Action: " . ($_POST['action'] ?? 'none') . ", Session status: " . 
                 (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not active'));
    }
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        $redirect_search = $_POST['search'] ?? ''; // Get search term for redirect
        
        switch ($action) {
            case 'add_to_list':
                $product_id = intval($_POST['product_id'] ?? 0);
                $quantity = intval($_POST['quantity'] ?? 1);
                $notes = sanitizeInput($_POST['notes'] ?? '');
                
                if ($product_id > 0 && $quantity > 0) {
                    if (addToShoppingList($session_id, $product_id, $quantity, $notes)) {
                        // Redirect back with search term to preserve search results
                        $redirect_url = 'shopping-list.php';
                        if (!empty($redirect_search)) {
                            $redirect_url .= '?search=' . urlencode($redirect_search) . '&added=1';
                        } else {
                            $redirect_url .= '?added=1';
                        }
                        header('Location: ' . $redirect_url);
                        exit;
                    } else {
                        $message = 'Failed to add product to shopping list.';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'update_quantity':
                $list_id = intval($_POST['list_id'] ?? 0);
                $quantity = intval($_POST['quantity'] ?? 1);
                
                if ($list_id > 0 && $quantity > 0) {
                    if (updateShoppingListQuantity($session_id, $list_id, $quantity)) {
                        $message = 'Quantity updated!';
                        $message_type = 'success';
                    }
                }
                break;
                
            case 'remove_item':
                $list_id = intval($_POST['list_id'] ?? 0);
                
                if ($list_id > 0) {
                    if (removeFromShoppingList($session_id, $list_id)) {
                        $message = 'Item removed from shopping list!';
                        $message_type = 'success';
                    }
                }
                break;
                
            case 'clear_list':
                if (clearShoppingList($session_id)) {
                    $message = 'Shopping list cleared!';
                    $message_type = 'success';
                }
                break;
        }
    }
}

// Check for success message from redirect
if (isset($_GET['added']) && $_GET['added'] == '1') {
    $message = 'Product added to shopping list!';
    $message_type = 'success';
}

// Handle search
$search_results = [];
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitizeInput($_GET['search']);
    $search_results = searchProducts($search_term);
}

// Get shopping list and calculate totals
$shopping_list = getShoppingList($session_id);
$total_amount = 0;
$total_items = 0;

// Debug session and shopping list
if (isset($_GET['debug'])) {
    echo "<div style='background: #000; color: #0f0; padding: 10px; margin: 10px; font-family: monospace;'>";
    echo "<strong>Session Debug:</strong><br>";
    echo "Session ID: " . $session_id . "<br>";
    echo "Shopping list items: " . count($shopping_list) . "<br>";
    echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not active') . "<br>";
    if (!empty($shopping_list)) {
        echo "<strong>Items in shopping list:</strong><br>";
        foreach ($shopping_list as $item) {
            echo "- ID: {$item['id']}, Product: {$item['filipino_name']}, Quantity: {$item['quantity']}<br>";
        }
    }
    echo "</div>";
}

foreach ($shopping_list as $item) {
    $item_total = $item['current_price'] * $item['quantity'];
    $total_amount += $item_total;
    $total_items += $item['quantity'];
}

// Get categories for quick browsing
$categories = getCategories();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12 scroll-animate">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 scroll-animate" data-delay="0.1">
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                🛒 Smart Shopping List
            </h1>
            <p class="text-lg text-text-secondary mb-6 max-w-2xl mx-auto scroll-animate" data-delay="0.2">
                Create your shopping list with real-time prices. Drag products from search results or add them manually.
            </p>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($message): ?>
    <div id="message-alert" class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700 border border-success-300' : 'bg-error-100 text-error-700 border border-error-300'; ?> transition-all duration-300">
        <div class="flex items-center">
            <?php if ($message_type === 'success'): ?>
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <?php else: ?>
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?php endif; ?>
            <?php echo htmlspecialchars($message); ?>
            <button onclick="dismissAlert()" class="ml-auto text-lg font-bold hover:opacity-70">×</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 scroll-animate" data-delay="0.3">
        
        <!-- Left Panel: Product Search & Add -->
        <div class="bg-white rounded-lg shadow-card p-6 scroll-animate-card" data-delay="0.1">
            <h2 class="text-xl font-semibold text-primary mb-4">🔍 Add Products to List</h2>
            
            <!-- Search Bar with Autocomplete -->
            <div class="mb-6 relative">
                <form method="GET" action="shopping-list.php" class="relative search-container">
                    <div class="search-icon-container">
                        <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo htmlspecialchars($search_term); ?>" 
                        placeholder="Search products (e.g., kamatis, bangus)..." 
                        class="input-field w-full"
                        id="product-search"
                        autocomplete="off"
                        oninput="showSearchSuggestions(this.value)"
                        onfocus="showSearchSuggestions(this.value)"
                    >
                </form>
                
                <!-- Search Suggestions Dropdown -->
                <div id="search-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden max-h-80 overflow-y-auto">
                    <!-- Suggestions will be populated here by JavaScript -->
                </div>
            </div>

            <!-- Category Quick Filters -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-text-primary mb-3">Quick Categories:</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <button 
                        onclick="searchByCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['filipino_name']); ?>')"
                        class="px-3 py-1 text-sm bg-surface-100 hover:bg-primary-100 text-text-secondary hover:text-primary rounded-full transition-all duration-200"
                        data-category-id="<?php echo $category['id']; ?>"
                        data-category-english="<?php echo htmlspecialchars($category['name']); ?>"
                        data-category-filipino="<?php echo htmlspecialchars($category['filipino_name']); ?>"
                    >
                        <?php echo htmlspecialchars($category['filipino_name']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Results / Product Browse -->
            <div id="search-results" class="space-y-3">
                <?php if (!empty($search_results)): ?>
                    <div class="bg-primary-50 border border-primary-200 rounded-lg p-3 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-primary-700">
                                Showing <?php echo count($search_results); ?> result<?php echo count($search_results) !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search_term); ?>"
                            </span>
                            <div class="flex space-x-3">
                                <a href="shopping-list.php" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                    Clear
                                </a>
                                <button onclick="testDragDrop()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    🔧 Test Drag
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $delay = 0.1;
                    foreach ($search_results as $product): ?>
                    <div 
                        class="product-item bg-surface-50 rounded-lg p-4 border border-surface-200 hover:border-primary-300 hover:shadow-md transition-all duration-200 cursor-grab scroll-animate-card" 
                        data-delay="<?php echo $delay; ?>"
                        draggable="true"
                        data-product-id="<?php echo $product['id']; ?>"
                        data-product-name="<?php echo htmlspecialchars($product['filipino_name']); ?>"
                        data-product-price="<?php echo $product['current_price']; ?>"
                        data-product-unit="<?php echo htmlspecialchars($product['unit']); ?>"
                        data-product-image="<?php echo htmlspecialchars($product['image_url']); ?>"
                    >
                    <?php $delay += 0.05; ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img 
                                    src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                    class="w-12 h-12 rounded-lg object-cover mr-3"
                                    onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop'; this.onerror=null;"
                                >
                                <div>
                                    <h4 class="font-semibold text-text-primary"><?php echo htmlspecialchars($product['filipino_name']); ?></h4>
                                    <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="text-lg font-bold text-primary"><?php echo formatCurrency($product['current_price']); ?> 
                                        <span class="text-sm font-normal text-text-secondary">per <?php echo htmlspecialchars($product['unit']); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button 
                                    onclick="addToListModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['filipino_name']); ?>', <?php echo $product['current_price']; ?>, '<?php echo htmlspecialchars($product['unit']); ?>')"
                                    class="btn-accent text-sm flex items-center justify-center px-3 py-2"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add
                                </button>
                                <div class="drag-handle cursor-grab text-text-secondary hover:text-primary" title="Drag to shopping list">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: block;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php elseif (!empty($search_term)): ?>
                    <!-- No results found for search -->
                    <div class="bg-warning-50 border border-warning-200 rounded-lg p-6 text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-lg font-semibold text-warning-700 mb-2">No products found for "<?php echo htmlspecialchars($search_term); ?>"</p>
                        <p class="text-sm text-warning-600 mb-4">Try searching with different keywords or browse by category</p>
                        <div class="flex justify-center space-x-3">
                            <button onclick="clearSearch()" class="btn-secondary text-sm">Clear Search</button>
                            <button onclick="document.getElementById('product-search').focus()" class="btn-accent text-sm">Try Again</button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Default empty state -->
                    <div class="text-center py-8 text-text-secondary">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-lg mb-2">Search for products to add to your list</p>
                        <p class="text-sm">Try searching for "kamatis", "bangus", or browse by category</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel: Shopping List -->
        <div class="bg-white rounded-lg shadow-card p-6 scroll-animate-card" data-delay="0.2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-primary">🛒 Your Shopping List</h2>
                <?php if (!empty($shopping_list)): ?>
                <button onclick="clearListConfirm()" class="text-error hover:text-error-700 text-sm font-medium">
                    Clear All
                </button>
                <?php endif; ?>
            </div>

            <!-- Shopping List Summary -->
            <div class="bg-gradient-to-r from-accent-50 to-success-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-primary"><?php echo $total_items; ?></p>
                        <p class="text-sm text-text-secondary">Total Items</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-success"><?php echo formatCurrency($total_amount); ?></p>
                        <p class="text-sm text-text-secondary">Total Cost</p>
                    </div>
                </div>
            </div>

            <!-- Drop Zone -->
            <div 
                id="drop-zone" 
                class="border-2 border-dashed border-surface-300 rounded-lg p-8 mb-6 text-center transition-all duration-200"
            >
                <svg class="w-12 h-12 mx-auto mb-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-text-secondary">Drop products here to add to your list</p>
            </div>

            <!-- Shopping List Items -->
            <div id="shopping-list-items" class="space-y-3">
                <?php if (!empty($shopping_list)): ?>
                    <?php 
                    $listDelay = 0.1;
                    foreach ($shopping_list as $item): ?>
                    <div class="shopping-item bg-surface-50 rounded-lg p-4 border border-surface-200 scroll-animate-card" data-delay="<?php echo $listDelay; ?>"> 
                    <?php $listDelay += 0.05; ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1">
                                <img 
                                    src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                    alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                    class="w-12 h-12 rounded-lg object-cover mr-3"
                                    onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop'; this.onerror=null;"
                                >
                                <div class="flex-1">
                                    <h4 class="font-semibold text-text-primary"><?php echo htmlspecialchars($item['filipino_name']); ?></h4>
                                    <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="text-sm text-text-secondary">
                                        <?php echo formatCurrency($item['current_price']); ?> per <?php echo htmlspecialchars($item['unit']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-4 bg-white rounded-lg p-3 border border-surface-200">
                                    <button 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)"
                                        class="w-9 h-9 flex items-center justify-center bg-surface-100 hover:bg-error-100 hover:text-error rounded-lg text-text-secondary transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>
                                        title="Decrease quantity"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-16 text-center font-bold text-lg text-primary px-2"><?php echo $item['quantity']; ?></span>
                                    <button 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                        class="w-9 h-9 flex items-center justify-center bg-surface-100 hover:bg-success-100 hover:text-success rounded-lg text-text-secondary transition-all duration-200"
                                        title="Increase quantity"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                </div>
                                <!-- Item Total -->
                                <div class="text-right min-w-0">
                                    <p class="font-bold text-primary"><?php echo formatCurrency($item['current_price'] * $item['quantity']); ?></p>
                                </div>
                                <!-- Remove Button -->
                                <button 
                                    onclick="removeItem(<?php echo $item['id']; ?>)"
                                    class="w-8 h-8 flex items-center justify-center text-error hover:bg-error-100 rounded-full transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-text-secondary">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p class="text-lg mb-2">Your shopping list is empty</p>
                        <p class="text-sm">Search for products and drag them here or click the Add button</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <?php if (!empty($shopping_list)): ?>
            <div class="mt-6 pt-6 border-t border-surface-200">
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="shareList()" class="btn-secondary flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                        </svg>
                        Share List
                    </button>
                    <button onclick="printList()" class="btn-accent flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print List
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to List Modal -->
<div id="add-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-primary">Add to Shopping List</h3>
            <button onclick="closeAddModal()" class="text-text-secondary hover:text-text-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="add-form" method="POST" action="shopping-list.php">
            <input type="hidden" name="action" value="add_to_list">
            <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <input type="hidden" name="product_id" id="modal-product-id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-text-primary mb-2">Product:</label>
                <p id="modal-product-name" class="text-text-primary font-semibold"></p>
                <p id="modal-product-price" class="text-sm text-text-secondary"></p>
            </div>
            
            <div class="mb-4">
                <label for="modal-quantity" class="block text-sm font-medium text-text-primary mb-2">Quantity:</label>
                <input type="number" id="modal-quantity" name="quantity" value="1" min="1" class="input-field w-full">
            </div>
            
            <div class="mb-6">
                <label for="modal-notes" class="block text-sm font-medium text-text-primary mb-2">Notes (optional):</label>
                <input type="text" id="modal-notes" name="notes" placeholder="e.g., ripe, organic, etc." class="input-field w-full">
            </div>
            
            <div class="flex space-x-3">
                <button type="button" onclick="closeAddModal()" class="btn-secondary flex-1">Cancel</button>
                <button type="submit" class="btn-primary flex-1">Add to List</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-card max-w-md w-full mx-4 transform transition-all duration-200 scale-95">
        <!-- Modal Header -->
        <div class="flex items-center p-6 pb-4">
            <div class="w-10 h-10 bg-error-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-text-primary" id="confirm-title">Remove Item</h3>
            </div>
        </div>
        
        <!-- Modal Content -->
        <div class="px-6 pb-6">
            <p class="text-text-secondary mb-6 leading-relaxed" id="confirm-message">Are you sure you want to remove this item from your shopping list?</p>
            
            <div class="flex space-x-3 justify-end">
                <button onclick="closeConfirmModal()" class="px-4 py-2 bg-surface-100 hover:bg-surface-200 text-text-secondary hover:text-text-primary rounded-md transition-colors duration-150 font-medium">Cancel</button>
                <button onclick="confirmAction()" class="px-4 py-2 bg-error text-white hover:bg-error-700 rounded-md transition-colors duration-150 font-medium" id="confirm-button">Remove</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for actions -->
<form id="update-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="update_quantity">
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
    <input type="hidden" name="list_id" id="update-list-id">
    <input type="hidden" name="quantity" id="update-quantity">
</form>

<form id="remove-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="remove_item">
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
    <input type="hidden" name="list_id" id="remove-list-id">
</form>

<form id="clear-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="clear_list">
    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
</form>

<script>
// Global variables to prevent multiple simultaneous requests
let currentCategoryRequest = null;
let categorySearchTimeout = null;

// Category search using category ID (shows ALL products in that category)
function searchByCategory(categoryId, categoryName) {
    // Prevent multiple rapid clicks
    if (categorySearchTimeout) {
        clearTimeout(categorySearchTimeout);
    }
    
    // Cancel any existing request
    if (currentCategoryRequest) {
        currentCategoryRequest.abort();
        currentCategoryRequest = null;
    }
    
    const searchInput = document.getElementById('product-search');
    
    // Put category name in search box
    searchInput.value = categoryName;
    
    // Focus the search box 
    searchInput.focus();
    
    // Show loading state
    const suggestionsDiv = document.getElementById('search-suggestions');
    suggestionsDiv.innerHTML = `
        <div class="p-4 text-center text-gray-500">
            <div class="text-sm">Loading ${categoryName} products...</div>
        </div>
    `;
    suggestionsDiv.classList.remove('hidden');
    
    // Debounce the search to prevent rapid multiple requests
    categorySearchTimeout = setTimeout(() => {
        // Create new AbortController for this request
        const controller = new AbortController();
        currentCategoryRequest = controller;
        
        // Fetch ALL products in this category
        fetch(`shopping-list.php?ajax=1&category_id=${categoryId}`, {
            signal: controller.signal
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(products => {
                // Clear the current request reference
                currentCategoryRequest = null;
                
                console.log(`Found ${products.length} products for category ${categoryName}`);
                
                if (products.length > 0) {
                    displaySearchSuggestions(products, categoryName);
                } else {
                    // No products in this category
                    suggestionsDiv.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            <div class="text-sm">No products found in "${categoryName}" category</div>
                            <div class="text-xs mt-1">Try a different category or search term</div>
                        </div>
                    `;
                    suggestionsDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                // Clear the current request reference
                currentCategoryRequest = null;
                
                if (error.name === 'AbortError') {
                    console.log('Category search request was cancelled');
                    return; // Don't show error for cancelled requests
                }
                
                console.error('Category search error:', error);
                suggestionsDiv.innerHTML = `
                    <div class="p-4 text-center text-red-500">
                        <div class="text-sm">Error loading ${categoryName} products</div>
                        <div class="text-xs mt-1">Please try again</div>
                    </div>
                `;
                suggestionsDiv.classList.remove('hidden');
            });
    }, 300); // 300ms debounce
}

// Legacy text-based search function (kept for compatibility)
function searchCategory(categoryName) {
    const searchInput = document.getElementById('product-search');
    
    // Put category name in search box for user to manually search
    searchInput.value = categoryName;
    
    // Focus the search box so user can modify or press Enter
    searchInput.focus();
    
    // Try to show suggestions - if no results, try alternative terms
    attemptCategorySearch(categoryName);
}

// Attempt category search with fallback options
function attemptCategorySearch(categoryName) {
    const searchTerms = getCategorySearchTerms(categoryName);
    
    // Try the first search term
    trySearchTerm(searchTerms, 0);
}

// Get multiple search terms for a category
function getCategorySearchTerms(categoryName) {
    const terms = [categoryName]; // Start with the provided name
    
    // Add common search terms for categories
    const categoryMappings = {
        'Prutas': ['Prutas', 'Fruit', 'Mangga', 'Saging', 'Ubas'],
        'Fruit': ['Fruit', 'Prutas', 'Mangga', 'Saging', 'Ubas'],
        'Gulay': ['Gulay', 'Vegetable', 'Kamatis', 'Sibuyas', 'Kangkong'],
        'Vegetable': ['Vegetable', 'Gulay', 'Kamatis', 'Sibuyas', 'Kangkong'],
        'Isda': ['Isda', 'Fish', 'Bangus', 'Tilapia', 'Galunggong'],
        'Fish': ['Fish', 'Isda', 'Bangus', 'Tilapia', 'Galunggong'],
        'Karne': ['Karne', 'Meat', 'Baboy', 'Manok', 'Baka'],
        'Meat': ['Meat', 'Karne', 'Baboy', 'Manok', 'Baka'],
        'Processed': ['Processed', 'Canned', 'Corned', 'Sardinas']
    };
    
    // Add specific terms for this category
    if (categoryMappings[categoryName]) {
        return categoryMappings[categoryName];
    }
    
    return terms;
}

// Try search terms one by one until we find results
function trySearchTerm(searchTerms, index) {
    if (index >= searchTerms.length) {
        // No more terms to try, show "no results" message
        const suggestionsDiv = document.getElementById('search-suggestions');
        suggestionsDiv.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <div class="text-sm">No products found for this category</div>
                <div class="text-xs mt-1">Try typing specific product names</div>
            </div>
        `;
        suggestionsDiv.classList.remove('hidden');
        return;
    }
    
    const currentTerm = searchTerms[index];
    
    // Try fetching suggestions for this term
    fetch(`shopping-list.php?ajax=1&search=${encodeURIComponent(currentTerm)}`)
        .then(response => response.json())
        .then(products => {
            if (products.length > 0) {
                // Found results! Show them
                displaySearchSuggestions(products, currentTerm);
            } else {
                // No results, try next term
                trySearchTerm(searchTerms, index + 1);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            // Try next term on error
            trySearchTerm(searchTerms, index + 1);
        });
}

// Global variables for regular search debouncing
let currentSearchRequest = null;
let searchTimeout = null;

// Show search suggestions dropdown
function showSearchSuggestions(query) {
    const suggestionsDiv = document.getElementById('search-suggestions');
    
    if (!query || query.trim().length < 2) {
        suggestionsDiv.classList.add('hidden');
        // Cancel any pending requests
        if (currentSearchRequest) {
            currentSearchRequest.abort();
            currentSearchRequest = null;
        }
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        return;
    }
    
    // Cancel any existing search request
    if (currentSearchRequest) {
        currentSearchRequest.abort();
        currentSearchRequest = null;
    }
    
    // Cancel any pending search
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Debounce the search
    searchTimeout = setTimeout(() => {
        // Create new AbortController for this request
        const controller = new AbortController();
        currentSearchRequest = controller;
        
        // Fetch suggestions via AJAX
        fetch(`shopping-list.php?ajax=1&search=${encodeURIComponent(query.trim())}`, {
            signal: controller.signal
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(products => {
                // Clear the current request reference
                currentSearchRequest = null;
                displaySearchSuggestions(products, query.trim());
            })
            .catch(error => {
                // Clear the current request reference
                currentSearchRequest = null;
                
                if (error.name === 'AbortError') {
                    console.log('Search request was cancelled');
                    return; // Don't show error for cancelled requests
                }
                
                console.error('Search error:', error);
                suggestionsDiv.classList.add('hidden');
            });
    }, 200); // 200ms debounce for typing
}

// Display search suggestions in dropdown
function displaySearchSuggestions(products, query) {
    const suggestionsDiv = document.getElementById('search-suggestions');
    
    if (products.length === 0) {
        suggestionsDiv.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <div class="text-sm">No products found for "${query}"</div>
                <div class="text-xs mt-1">Try a different search term</div>
            </div>
        `;
        suggestionsDiv.classList.remove('hidden');
        return;
    }
    
    let suggestionsHtml = '';
    products.slice(0, 6).forEach(product => { // Show max 6 suggestions
        suggestionsHtml += `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0" onclick="selectProduct('${product.filipino_name}')">
                <div class="flex items-center">
                    <img src="${product.image_url}" alt="${product.name}" class="w-10 h-10 rounded object-cover mr-3" onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop'; this.onerror=null;">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${product.filipino_name}</div>
                        <div class="text-sm text-gray-500">${product.name} - ₱${parseFloat(product.current_price).toFixed(2)}/${product.unit}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (products.length > 6) {
        suggestionsHtml += `
            <div class="p-3 text-center bg-gray-50 cursor-pointer" onclick="searchAll('${query}')">
                <div class="text-sm text-blue-600 font-medium">View all ${products.length} results</div>
            </div>
        `;
    }
    
    suggestionsDiv.innerHTML = suggestionsHtml;
    suggestionsDiv.classList.remove('hidden');
}

// Select a product from suggestions without page reload
function selectProduct(productName) {
    const searchInput = document.getElementById('product-search');
    searchInput.value = productName;
    hideSuggestions();
    
    // Perform search without page reload to avoid animations
    performSearch(productName);
}

// Search all products without page reload
function searchAll(query) {
    const searchInput = document.getElementById('product-search');
    searchInput.value = query;
    hideSuggestions();
    
    // Perform search without page reload to avoid animations
    performSearch(query);
}

// Perform search and update results without page reload
function performSearch(query) {
    if (!query || query.trim().length < 1) {
        // If empty query, reload to show default state
        window.location.href = 'shopping-list.php';
        return;
    }
    
    // Fetch search results via AJAX
    fetch(`shopping-list.php?ajax=1&search=${encodeURIComponent(query.trim())}`)
        .then(response => response.json())
        .then(products => {
            updateSearchResults(products, query.trim());
            // Update URL without reload
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('search', query.trim());
            window.history.pushState({}, '', newUrl);
        })
        .catch(error => {
            console.error('Search error:', error);
            // Fallback to page reload if AJAX fails
            searchInput.form.submit();
        });
}

// Global variable to store accumulated search results
let accumulatedProducts = [];
let searchQueries = [];

// Update search results in the DOM without page reload
function updateSearchResults(products, query, additive = false) {
    const resultsContainer = document.getElementById('search-results');
    
    if (products.length === 0) {
        // No results found for this search
        if (accumulatedProducts.length > 0) {
            // Show message but keep existing results
            const noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'bg-warning-50 border border-warning-200 rounded-lg p-3 mb-4';
            noResultsMsg.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-sm text-warning-700">
                        No additional results found for "${query}" (keeping previous results)
                    </span>
                    <button onclick="dismissNoResultsMessage(this)" class="text-warning-600 hover:text-warning-800 text-xs">
                        Dismiss
                    </button>
                </div>
            `;
            resultsContainer.insertBefore(noResultsMsg, resultsContainer.firstChild);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                if (noResultsMsg.parentNode) {
                    noResultsMsg.remove();
                }
            }, 3000);
        } else {
            // No results and no previous results
            resultsContainer.innerHTML = `
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-lg font-semibold text-warning-700 mb-2">No products found for "${query}"</p>
                    <p class="text-sm text-warning-600 mb-4">Try searching with different keywords or browse by category</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="clearAllSearches()" class="btn-secondary text-sm">Clear Search</button>
                        <button onclick="document.getElementById('product-search').focus()" class="btn-accent text-sm">Try Again</button>
                    </div>
                </div>
            `;
        }
        return;
    }
    
    // Add new products to accumulated results (avoid duplicates)
    products.forEach(product => {
        const existingProduct = accumulatedProducts.find(p => p.id === product.id);
        if (!existingProduct) {
            accumulatedProducts.push(product);
        }
    });
    
    // Add query to search history
    if (!searchQueries.includes(query)) {
        searchQueries.push(query);
    }
    
    // Build results HTML with all accumulated products
    let resultsHtml = `
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-3 mb-4">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-primary-700">
                    Showing ${accumulatedProducts.length} result${accumulatedProducts.length !== 1 ? 's' : ''} for: ${searchQueries.map(q => `"${q}"`).join(', ')}
                </span>
                <div class="flex space-x-2">
                    <button onclick="clearAllSearches()" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                        Clear All
                    </button>
                    <button onclick="addMoreProducts()" class="text-success-600 hover:text-success-800 text-sm font-medium">
                        + Add More
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Use accumulated products instead of just current search products
    accumulatedProducts.forEach((product, index) => {
        const delay = 0.1 + (index * 0.05);
        resultsHtml += `
            <div 
                class="product-item bg-surface-50 rounded-lg p-4 border border-surface-200 hover:border-primary-300 hover:shadow-md transition-all duration-200 cursor-grab" 
                draggable="true"
                data-product-id="${product.id}"
                data-product-name="${product.filipino_name.replace(/'/g, '\\&#39;')}"
                data-product-price="${product.current_price}"
                data-product-unit="${product.unit.replace(/'/g, '\\&#39;')}"
                data-product-image="${product.image_url.replace(/'/g, '\\&#39;')}"
                style="opacity: 1; transform: translateY(0); transition: none;"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img 
                            src="${product.image_url}" 
                            alt="${product.name}" 
                            class="w-12 h-12 rounded-lg object-cover mr-3"
                            onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop'; this.onerror=null;"
                        >
                        <div>
                            <h4 class="font-semibold text-text-primary">${product.filipino_name}</h4>
                            <p class="text-sm text-text-secondary">${product.name}</p>
                            <p class="text-lg font-bold text-primary">₱${parseFloat(product.current_price).toFixed(2)} 
                                <span class="text-sm font-normal text-text-secondary">per ${product.unit}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button 
                            onclick="addToListModal(${product.id}, '${product.filipino_name.replace(/'/g, '\\&#39;')}', ${product.current_price}, '${product.unit.replace(/'/g, '\\&#39;')}')"
                            class="btn-accent text-sm flex items-center justify-center px-3 py-2"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add
                        </button>
                        <div class="drag-handle cursor-grab text-text-secondary hover:text-primary" title="Drag to shopping list">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: block;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = resultsHtml;
    
    // Reinitialize drag and drop for new elements
    reinitializeInteractivity();
}

// Hide suggestions dropdown
function hideSuggestions() {
    const suggestionsDiv = document.getElementById('search-suggestions');
    suggestionsDiv.classList.add('hidden');
}

// Clear all accumulated searches and reset to default state
function clearAllSearches() {
    const searchInput = document.getElementById('product-search');
    searchInput.value = '';
    hideSuggestions();
    
    // Reset accumulated data
    accumulatedProducts = [];
    searchQueries = [];
    
    // Reset search results to default empty state
    const resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = `
        <div class="text-center py-8 text-text-secondary">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-lg mb-2">Search for products to add to your list</p>
            <p class="text-sm">Try searching for "kamatis", "bangus", or browse by category</p>
        </div>
    `;
    
    // Update URL to remove search parameter
    const newUrl = new URL(window.location);
    newUrl.searchParams.delete('search');
    window.history.pushState({}, '', newUrl);
}

// Legacy function for compatibility
function clearSearch() {
    clearAllSearches();
}

// Focus search input for adding more products
function addMoreProducts() {
    const searchInput = document.getElementById('product-search');
    searchInput.value = '';
    searchInput.focus();
    searchInput.placeholder = 'Search for more products to add...';
}

// Dismiss no results message
function dismissNoResultsMessage(button) {
    button.closest('div').remove();
}

// Initialize search autocomplete functionality
function initSearchAutocomplete() {
    const searchInput = document.getElementById('product-search');
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        const suggestionsDiv = document.getElementById('search-suggestions');
        const searchContainer = document.querySelector('.search-container');
        
        if (!searchContainer.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            hideSuggestions();
        }
    });
    
    // Add keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideSuggestions();
        }
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            const query = this.value.trim();
            if (query) {
                hideSuggestions();
                performSearch(query);
            } else {
                clearSearch();
            }
        }
    });
    
    // Override form submission to use AJAX
    const searchForm = searchInput.form;
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                performSearch(query);
            } else {
                clearSearch();
            }
        });
    }
}

// Add to list modal functions
function addToListModal(productId, productName, productPrice, productUnit) {
    document.getElementById('modal-product-id').value = productId;
    document.getElementById('modal-product-name').textContent = productName;
    document.getElementById('modal-product-price').textContent = formatCurrency(productPrice) + ' per ' + productUnit;
    document.getElementById('modal-quantity').value = 1;
    document.getElementById('modal-notes').value = '';
    document.getElementById('add-modal').classList.remove('hidden');
    document.getElementById('add-modal').classList.add('flex');
}

function closeAddModal() {
    document.getElementById('add-modal').classList.add('hidden');
    document.getElementById('add-modal').classList.remove('flex');
}

// Quantity update function with AJAX (no page reload)
function updateQuantity(listId, newQuantity) {
    console.log('updateQuantity called with:', { listId, newQuantity });
    
    if (newQuantity < 1) {
        console.warn('Invalid quantity:', newQuantity);
        return;
    }
    
    updateQuantityAjax(listId, newQuantity);
}

// AJAX quantity update function
function updateQuantityAjax(listId, newQuantity) {
    console.log('updateQuantityAjax called with:', { listId, newQuantity });
    
    // Get CSRF token
    const existingToken = document.querySelector('input[name="csrf_token"]');
    const csrfToken = existingToken ? existingToken.value : '';
    
    if (!csrfToken) {
        console.error('No CSRF token found for update!');
        showTemporaryMessage('❌ Security error. Please refresh the page.', 'error');
        return;
    }
    
    // Show loading message
    showTemporaryMessage('Updating quantity...', 'info');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'update_quantity');
    formData.append('csrf_token', csrfToken);
    formData.append('list_id', listId);
    formData.append('quantity', newQuantity);
    
    // Send AJAX request
    fetch('shopping-list.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Update quantity response status:', response.status);
        if (response.ok) {
            return response.text().then(text => {
                console.log('Update quantity response text:', text);
                showTemporaryMessage('Quantity updated!', 'success');
                // Update shopping list display
                updateShoppingListDisplay();
            });
        } else {
            return response.text().then(text => {
                console.error('Update quantity error response:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        showTemporaryMessage('❌ Failed to update quantity. Please try again.', 'error');
    });
}

// Global variable to store pending action
let pendingAction = null;

// Show confirmation modal
function showConfirmModal(title, message, buttonText, action) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('confirm-button').textContent = buttonText;
    
    // Store the action to be executed
    pendingAction = action;
    
    // Show modal with animation
    const modal = document.getElementById('confirm-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Add scale animation
    setTimeout(() => {
        modal.querySelector('div > div').classList.remove('scale-95');
        modal.querySelector('div > div').classList.add('scale-100');
    }, 10);
}

// Close confirmation modal
function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    const innerDiv = modal.querySelector('div > div');
    
    // Animate out
    innerDiv.classList.remove('scale-100');
    innerDiv.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingAction = null;
    }, 200);
}

// Execute confirmed action
function confirmAction() {
    if (pendingAction) {
        pendingAction();
        pendingAction = null;
    }
    closeConfirmModal();
}

// Remove item function with AJAX (no page reload)
function removeItem(listId) {
    console.log('removeItem called with listId:', listId);
    showConfirmModal(
        'Remove Item',
        'Are you sure you want to remove this item from your shopping list?',
        'Remove',
        () => {
            removeItemAjax(listId);
        }
    );
}

// AJAX remove item function
function removeItemAjax(listId) {
    console.log('removeItemAjax called with listId:', listId);
    
    // Get CSRF token
    const existingToken = document.querySelector('input[name="csrf_token"]');
    const csrfToken = existingToken ? existingToken.value : '';
    
    if (!csrfToken) {
        console.error('No CSRF token found for remove!');
        showTemporaryMessage('❌ Security error. Please refresh the page.', 'error');
        return;
    }
    
    // Show loading message
    showTemporaryMessage('Removing item from shopping list...', 'info');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'remove_item');
    formData.append('csrf_token', csrfToken);
    formData.append('list_id', listId);
    
    // Send AJAX request
    fetch('shopping-list.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Remove item response status:', response.status);
        if (response.ok) {
            return response.text().then(text => {
                console.log('Remove item response text:', text);
                showTemporaryMessage('Item removed from shopping list!', 'success');
                // Update shopping list display
                updateShoppingListDisplay();
            });
        } else {
            return response.text().then(text => {
                console.error('Remove item error response:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showTemporaryMessage('❌ Failed to remove item. Please try again.', 'error');
    });
}

// Clear list function with modern modal
function clearListConfirm() {
    showConfirmModal(
        'Clear Shopping List',
        'Are you sure you want to clear your entire shopping list? This action cannot be undone.',
        'Clear All',
        () => {
            document.getElementById('clear-form').submit();
        }
    );
}

// Share list function - simple alert for now
function shareList() {
    alert('Share functionality coming soon!');
}

// Print list function - simple print
function printList() {
    window.print();
}

// Utility function
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2);
}

// Auto-dismiss message alert
function dismissAlert() {
    const alert = document.getElementById('message-alert');
    if (alert) {
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 300);
    }
}

// Auto-dismiss after 5 seconds
setTimeout(dismissAlert, 5000);

// Add smooth animation for new shopping list items
function animateNewItem() {
    const newItems = document.querySelectorAll('.shopping-item:not(.item-animated)');
    newItems.forEach((item, index) => {
        item.classList.add('item-animated');
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        item.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 50 + 100);
    });
}

// Check for newly added items and animate them
function checkForNewItems() {
    if (window.location.search.includes('added=1')) {
        setTimeout(animateNewItem, 200);
    }
}

// Scroll-triggered animations - with smart animation management
function initScrollAnimations() {
    const animateElements = document.querySelectorAll('.scroll-animate, .scroll-animate-card');
    
    // Skip animations for specific form actions and search results
    const isItemAddedAction = window.location.search.includes('added=1');
    const isSearchAction = window.location.search.includes('search=') && window.location.search.length > 8; // Has actual search term
    
    // If this is adding an item or searching, show elements immediately to avoid repetitive animations
    if (isItemAddedAction || isSearchAction) {
        animateElements.forEach(element => {
            element.classList.add('animate-in');
            element.style.opacity = '1';
            element.style.transform = 'translateY(0) scale(1)';
            element.style.transition = 'none';
        });
        return;
    }
    
    // For all other visits (including first visit and searches), show animations
    const observerOptions = {
        threshold: 0.05,  // Lower threshold to catch elements earlier
        rootMargin: '20px 0px -20px 0px'  // Trigger slightly before elements are visible
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseFloat(entry.target.dataset.delay || 0) * 500; // Halved the delay multiplier
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                    // Stop observing this element once animated
                    observer.unobserve(entry.target);
                }, delay);
            }
        });
    }, observerOptions);
    
    // Small delay before starting observer to ensure page is ready
    setTimeout(() => {
        animateElements.forEach(element => {
            // If element is already in viewport, animate it immediately
            const rect = element.getBoundingClientRect();
            const isInViewport = rect.top >= 0 && rect.top <= window.innerHeight;
            
            if (isInViewport) {
                const delay = parseFloat(element.dataset.delay || 0) * 500; // Halved the delay multiplier
                setTimeout(() => {
                    element.classList.add('animate-in');
                }, delay);
            } else {
                observer.observe(element);
            }
        });
    }, 100);
}

// Global variables for drag and drop
let draggedElement = null;
let dropZone = null;

// JavaScript version of formatCurrency function
function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return '₱' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Function to initialize drag and drop for product items
function initDragAndDrop() {
    console.log('🚀 Initializing drag and drop...');
    
    // Check if elements exist first
    const allProductItems = document.querySelectorAll('.product-item');
    console.log('📊 Total .product-item elements found:', allProductItems.length);
    
    const productItems = document.querySelectorAll('.product-item:not([data-drag-initialized])');
    console.log('📊 Product items without drag initialization:', productItems.length);
    
    if (productItems.length === 0) {
        console.log('⚠️ No product items to initialize - might be no search results');
        return;
    }
    
    productItems.forEach((item, index) => {
        const productId = item.dataset.productId;
        const productName = item.dataset.productName;
        const productPrice = item.dataset.productPrice;
        const productUnit = item.dataset.productUnit;
        
        console.log(`Initializing drag for item ${index}:`, {
            id: productId, 
            name: productName, 
            price: productPrice, 
            unit: productUnit
        });
        
        if (!productId || !productName) {
            console.warn(`Item ${index} missing required data, skipping drag initialization`);
            return;
        }
        
        item.setAttribute('data-drag-initialized', 'true');
        
        item.addEventListener('dragstart', function(e) {
            console.log('Drag started for:', this.dataset.productName);
            draggedElement = this;
            this.classList.add('dragging');
            
            // Highlight drop zone during drag
            if (dropZone) {
                dropZone.classList.add('border-primary-500', 'bg-primary-50');
                console.log('Drop zone highlighted');
            } else {
                console.warn('Drop zone not found!');
            }
            
            // Set drag data
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', 'product-item');
        });

        item.addEventListener('dragend', function(e) {
            console.log('Drag ended for:', this.dataset.productName);
            this.classList.remove('dragging');
            draggedElement = null;
            
            // Reset drop zone appearance but keep it visible
            if (dropZone) {
                dropZone.classList.remove('border-primary-500', 'bg-primary-50', 'border-success-500', 'bg-success-50');
                console.log('Drop zone appearance reset');
            }
        });
    });
}

// Function to reinitialize animations and drag/drop for new content
function reinitializeInteractivity() {
    console.log('Reinitializing interactivity...');
    initDragAndDrop();
}

// Initialize drop zone events
function initDropZone() {
    console.log('🎯 Attempting to find drop zone...');
    dropZone = document.getElementById('drop-zone');
    
    if (!dropZone) {
        console.error('❌ Drop zone element not found!');
        console.log('🔍 Available elements with IDs:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));
        return;
    }
    
    console.log('✅ Drop zone found:', dropZone);
    console.log('🎯 Initializing drop zone events...');
    
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-success-500', 'bg-success-50');
        this.classList.remove('border-primary-500', 'bg-primary-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        this.classList.remove('border-success-500', 'bg-success-50');
        this.classList.add('border-primary-500', 'bg-primary-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        console.log('Item dropped!', draggedElement);
        
        if (!draggedElement) {
            console.error('No dragged element found!');
            return;
        }
        
        if (draggedElement) {
            const productId = draggedElement.dataset.productId;
            const productName = draggedElement.dataset.productName;
            const productPrice = draggedElement.dataset.productPrice;
            const productUnit = draggedElement.dataset.productUnit;
            
            console.log('Adding to list:', { productId, productName, productPrice, productUnit });
            
            // Auto-add with quantity 1
            addToListQuick(productId, productName, productPrice, productUnit);
        }
    });
}

// Function to debug session info
function debugSessionInfo() {
    console.log('🔍 Session Debug Info:');
    console.log('📍 Current URL:', window.location.href);
    console.log('🍪 Document.cookie:', document.cookie);
    
    // Look for PHPSESSID in cookies
    const cookies = document.cookie.split(';');
    let sessionId = null;
    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'PHPSESSID') {
            sessionId = value;
            break;
        }
    }
    console.log('🔑 PHPSESSID from cookies:', sessionId || 'Not found');
}

// Drag and Drop Implementation
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM loaded, initializing everything...');
    console.log('📍 Current URL:', window.location.href);
    
    // Debug session info
    debugSessionInfo();
    
    // Initialize animations
    initScrollAnimations();
    
    // Check for new items to animate
    checkForNewItems();
    
    // Initialize search autocomplete
    initSearchAutocomplete();
    
    // Initialize drop zone
    console.log('🎯 Initializing drop zone...');
    initDropZone();
    
    // Initialize drag and drop for existing items
    console.log('🎯 Initializing drag and drop for existing items...');
    initDragAndDrop();
    
    // Also try again after a short delay to catch any dynamically loaded content
    setTimeout(function() {
        console.log('🔄 Re-initializing drag and drop after delay...');
        initDragAndDrop();
    }, 1000);
    
    console.log('✅ All initialization complete!');
});

// Quick add function for drag & drop - using AJAX to avoid page reload
function addToListQuick(productId, productName, productPrice, productUnit) {
    console.log('addToListQuick called with:', { productId, productName, productPrice, productUnit });
    
    // Debug session info before AJAX call
    debugSessionInfo();
    
    // Show loading feedback
    showTemporaryMessage('Adding ' + productName + ' to shopping list...', 'info');
    
    // Get CSRF token from existing form
    const existingToken = document.querySelector('input[name="csrf_token"]');
    const csrfToken = existingToken ? existingToken.value : '';
    console.log('CSRF token found:', csrfToken ? 'Yes' : 'No', csrfToken ? csrfToken.substring(0, 10) + '...' : 'N/A');
    
    if (!csrfToken) {
        console.error('No CSRF token found!');
        showTemporaryMessage('❌ Security error. Please refresh the page.', 'error');
        return;
    }
    
    if (!productId || !productName) {
        console.error('Missing required product data:', { productId, productName });
        showTemporaryMessage('❌ Invalid product data.', 'error');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'add_to_list');
    formData.append('csrf_token', csrfToken);
    formData.append('product_id', productId);
    formData.append('quantity', '1');
    formData.append('notes', '');
    
    console.log('Form data prepared:', {
        action: 'add_to_list',
        product_id: productId,
        quantity: '1',
        csrf_present: !!csrfToken
    });
    
    // Send AJAX request
    fetch('shopping-list.php', {
        method: 'POST',
        credentials: 'same-origin', // Ensure cookies (including session) are sent
        body: formData
    })
    .then(response => {
        console.log('Add to list response status:', response.status);
        console.log('Add to list response headers:', response.headers.get('content-type'));
        if (response.ok) {
            // Try to get response text to see what the server returned
            return response.text().then(text => {
                console.log('Add to list response text:', text);
                // Successfully added - show success message
                showTemporaryMessage(productName + ' added to shopping list!', 'success');
                
                // Also test with a simple alert to make sure notifications work
                // alert('✅ ' + productName + ' added to shopping list!');
                
                // Update shopping list totals and items display without page reload
                updateShoppingListDisplay();
            });
        } else {
            return response.text().then(text => {
                console.error('HTTP error:', response.status, response.statusText);
                console.error('Error response text:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
    })
    .catch(error => {
        console.error('Error adding item:', error);
        showTemporaryMessage('❌ Failed to add ' + productName + '. Please try again.', 'error');
    });
}

// Update shopping list display without page reload
function updateShoppingListDisplay() {
    console.log('Fetching shopping list data...');
    // Fetch updated shopping list data
    fetch('shopping-list.php?ajax=1&get_shopping_list=1&debug=1', {
        credentials: 'same-origin' // Ensure cookies (including session) are sent
    })
        .then(response => {
            console.log('Shopping list data response status:', response.status);
            console.log('Shopping list data response headers:', response.headers.get('content-type'));
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Shopping list data error response:', text);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.text().then(text => {
                console.log('Shopping list raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    console.error('Raw response text:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            console.log('Shopping list data received:', data);
            // Update the shopping list totals
            updateShoppingListTotals(data.total_items, data.total_amount);
            
            // Update the shopping list items
            updateShoppingListItems(data.items);
            
            // Update shopping list header and actions
            updateShoppingListHeader(data.items.length > 0);
            
            // Update navigation badge
            updateNavigationBadge(data.total_items);
        })
        .catch(error => {
            console.error('Error fetching shopping list:', error);
            // Fallback: show message that list was updated but display might be outdated
            showTemporaryMessage('ℹ️ Item added! Refresh page to see updated list.', 'info');
        });
}

// Update shopping list totals
function updateShoppingListTotals(totalItems, totalAmount) {
    console.log('Updating totals:', { totalItems, totalAmount });
    
    // Find and update the total items display
    const totalItemsElement = document.querySelector('.text-2xl.font-bold.text-primary');
    if (totalItemsElement) {
        totalItemsElement.textContent = totalItems || 0;
        console.log('Updated total items to:', totalItems || 0);
    }
    
    // Find and update the total amount display  
    const totalAmountElement = document.querySelector('.text-2xl.font-bold.text-success');
    if (totalAmountElement) {
        // Ensure totalAmount is a valid number
        const amount = parseFloat(totalAmount) || 0;
        totalAmountElement.textContent = formatCurrency(amount);
        console.log('Updated total amount to:', formatCurrency(amount));
    }
}

// Update shopping list items (rebuild the shopping list HTML)
function updateShoppingListItems(items) {
    console.log('Updating shopping list with', items.length, 'items');
    
    const shoppingListContainer = document.getElementById('shopping-list-items');
    if (!shoppingListContainer) {
        console.error('Shopping list container not found!');
        return;
    }
    
    if (items.length === 0) {
        // Show empty state
        shoppingListContainer.innerHTML = `
            <div class="text-center py-8 text-text-secondary">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 8H6L5 9z"/>
                </svg>
                <p class="text-lg mb-2">Your shopping list is empty</p>
                <p class="text-sm">Search for products and drag them here or click the Add button</p>
            </div>
        `;
        return;
    }
    
    // Build HTML for shopping list items
    let itemsHtml = '';
    let listDelay = 0.1;
    
    items.forEach(item => {
        const itemTotal = (parseFloat(item.current_price) * parseInt(item.quantity)).toFixed(2);
        itemsHtml += `
            <div class="shopping-item bg-surface-50 rounded-lg p-4 border border-surface-200 scroll-animate-card" data-delay="${listDelay}"> 
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img 
                            src="${item.image_url}" 
                            alt="${item.name}" 
                            class="w-12 h-12 rounded-lg object-cover mr-3"
                            onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop'; this.onerror=null;"
                        >
                        <div>
                            <h4 class="font-semibold text-text-primary">${item.filipino_name}</h4>
                            <p class="text-sm text-text-secondary">${item.name}</p>
                            <p class="text-sm text-text-secondary">${item.notes || ''}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="font-semibold text-primary">₱${itemTotal}</p>
                            <p class="text-sm text-text-secondary">₱${parseFloat(item.current_price).toFixed(2)} per ${item.unit}</p>
                        </div>
                        <div class="flex items-center space-x-1">
                            <button 
                                onclick="updateQuantity(${item.id}, ${parseInt(item.quantity) - 1})" 
                                class="w-8 h-8 rounded-full bg-surface-200 hover:bg-surface-300 flex items-center justify-center text-text-secondary hover:text-primary transition-colors"
                                ${parseInt(item.quantity) <= 1 ? 'disabled' : ''}
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span class="w-8 text-center font-semibold text-primary">${item.quantity}</span>
                            <button 
                                onclick="updateQuantity(${item.id}, ${parseInt(item.quantity) + 1})" 
                                class="w-8 h-8 rounded-full bg-surface-200 hover:bg-surface-300 flex items-center justify-center text-text-secondary hover:text-primary transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                        <button 
                            onclick="removeItem(${item.id})" 
                            class="text-error hover:text-error-700 p-2 rounded-lg hover:bg-error-50 transition-colors"
                            title="Remove item"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        listDelay += 0.05;
    });
    
    shoppingListContainer.innerHTML = itemsHtml;
    
    // Trigger animations for new items
    setTimeout(() => {
        const newItems = shoppingListContainer.querySelectorAll('.shopping-item');
        newItems.forEach(item => {
            item.classList.add('animate-in');
        });
    }, 50);
}

// Update shopping list header and actions visibility
function updateShoppingListHeader(hasItems) {
    console.log('Updating shopping list header, hasItems:', hasItems);
    
    // Find the shopping list header
    const shoppingListHeaders = document.querySelectorAll('h2');
    let header = null;
    shoppingListHeaders.forEach(h => {
        if (h.textContent.includes('Your Shopping List')) {
            header = h;
        }
    });
    
    if (header) {
        const headerContainer = header.parentElement;
        console.log('Found header container:', headerContainer);
        
        // Remove existing clear button if any
        const existingClearBtn = headerContainer.querySelector('button');
        if (existingClearBtn && existingClearBtn.textContent.includes('Clear')) {
            console.log('Removing existing clear button');
            existingClearBtn.remove();
        }
        
        // Add clear button if there are items
        if (hasItems) {
            console.log('Adding clear button');
            const clearButton = document.createElement('button');
            clearButton.onclick = function() { clearListConfirm(); };
            clearButton.className = 'text-error hover:text-error-700 text-sm font-medium';
            clearButton.textContent = 'Clear All';
            headerContainer.appendChild(clearButton);
        }
    } else {
        console.warn('Shopping list header not found');
    }
    
    // Handle action buttons section
    let actionButtonsSection = document.querySelector('.grid.grid-cols-2.gap-3');
    if (actionButtonsSection) {
        actionButtonsSection = actionButtonsSection.closest('.mt-6');
    }
    
    console.log('Action buttons section found:', !!actionButtonsSection);
    
    if (hasItems) {
        // Show action buttons if they exist, or create them if they don't
        if (actionButtonsSection) {
            console.log('Showing existing action buttons');
            actionButtonsSection.style.display = 'block';
        } else {
            console.log('Creating new action buttons section');
            // Create action buttons section
            const shoppingListContainer = document.getElementById('shopping-list-items');
            if (shoppingListContainer) {
                const actionsHtml = `
                    <div class="mt-6 pt-6 border-t border-surface-200" id="shopping-actions">
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="shareList()" class="btn-secondary flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                </svg>
                                Share List
                            </button>
                            <button onclick="printList()" class="btn-accent flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print List
                            </button>
                        </div>
                    </div>
                `;
                shoppingListContainer.insertAdjacentHTML('afterend', actionsHtml);
            }
        }
    } else {
        console.log('Hiding action buttons');
        // Hide action buttons
        if (actionButtonsSection) {
            actionButtonsSection.style.display = 'none';
        }
        // Also try to find and hide by ID if we created it dynamically
        const dynamicActions = document.getElementById('shopping-actions');
        if (dynamicActions) {
            dynamicActions.style.display = 'none';
        }
    }
}

// Update navigation badge in header
function updateNavigationBadge(totalItems) {
    console.log('Updating navigation badge with total items:', totalItems);
    
    // Find the shopping list link in navigation
    const shoppingListLink = document.querySelector('a[href="shopping-list.php"]');
    if (!shoppingListLink) {
        console.warn('Shopping list navigation link not found');
        return;
    }
    
    // Find existing badge
    let badge = shoppingListLink.querySelector('span.absolute');
    
    if (totalItems > 0) {
        if (badge) {
            // Update existing badge
            badge.textContent = Math.min(totalItems, 99);
            console.log('Updated existing badge to:', totalItems);
        } else {
            // Create new badge
            badge = document.createElement('span');
            badge.className = 'absolute -top-1 -right-1 bg-accent text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold';
            badge.textContent = Math.min(totalItems, 99);
            shoppingListLink.appendChild(badge);
            console.log('Created new badge with:', totalItems);
        }
    } else {
        if (badge) {
            // Remove badge if no items
            badge.remove();
            console.log('Removed badge (no items)');
        }
    }
}

// Show temporary message without page reload - Simple version that works
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

// Debug function to manually test drag and drop
function testDragDrop() {
    console.log('🔧 Manual drag & drop test triggered!');
    
    // Show current state
    const allProductItems = document.querySelectorAll('.product-item');
    const initializedItems = document.querySelectorAll('.product-item[data-drag-initialized="true"]');
    const dropZoneElement = document.getElementById('drop-zone');
    
    console.log('📊 Total product items:', allProductItems.length);
    console.log('📊 Initialized items:', initializedItems.length);
    console.log('🎯 Drop zone found:', !!dropZoneElement);
    
    if (allProductItems.length === 0) {
        alert('⚠️ No product items found! Search for products first.');
        return;
    }
    
    if (!dropZoneElement) {
        alert('❌ Drop zone not found!');
        return;
    }
    
    // Re-initialize drag and drop
    console.log('🔄 Re-initializing drag and drop...');
    initDragAndDrop();
    
    alert('✅ Drag & Drop reinitialized!\n\nTotal items: ' + allProductItems.length + '\nInitialized: ' + document.querySelectorAll('.product-item[data-drag-initialized="true"]').length + '\n\nTry dragging a product now!');
}

</script>

<!-- Enhanced search box styling -->
<style>
/* Enhanced search box styling */
#product-search {
    padding: 0.875rem 1rem 0.875rem 3rem !important; /* top/bottom: 14px, right: 16px, left: 48px */
    background-color: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    line-height: 1.5;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

#product-search:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
    outline: none;
    background-color: #ffffff;
}

#product-search:hover {
    border-color: #d1d5db;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

#product-search::placeholder {
    color: #9ca3af;
    font-weight: 400;
    font-size: 0.9rem;
}

/* Search icon positioning - perfectly centered */
.search-icon-container {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 3rem; /* 48px - same as left padding */
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 2;
}

.search-icon {
    width: 1.25rem; /* 20px */
    height: 1.25rem; /* 20px */
    color: #6b7280;
    transition: color 0.2s ease;
}

.search-container:focus-within .search-icon {
    color: #22c55e;
}

/* Search container hover effect */
.search-container:hover .search-icon {
    color: #4b5563;
}

/* Search suggestions dropdown styling - Fast and smooth */
#search-suggestions {
    border-top: none;
    margin-top: 2px;
    animation: slideDown 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Search suggestion item styling */
#search-suggestions .hover\:bg-gray-50:hover {
    background-color: #f9fafb;
    transition: background-color 0.15s ease;
}

/* Drag and drop enhancements - Fast and smooth */
.product-item {
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.shopping-item {
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.shopping-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Drag handle styling - ensure visibility */
.drag-handle {
    display: flex !important;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    min-height: 24px;
    opacity: 0.6;
    transition: all 0.15s ease;
}

.drag-handle:hover {
    opacity: 1;
    transform: scale(1.1);
}

.product-item:hover .drag-handle {
    opacity: 1;
    color: #22c55e; /* primary green */
}

.drag-handle svg {
    width: 20px !important;
    height: 20px !important;
    stroke-width: 2.5;
    display: block !important;
}

/* Drop zone animation */
#drop-zone {
    transition: all 0.3s ease;
}

#drop-zone.border-success-500 {
    animation: pulse-success 1s infinite;
}

@keyframes pulse-success {
    0%, 100% { 
        border-color: #22c55e;
        background-color: #f0fdf4;
    }
    50% { 
        border-color: #16a34a;
        background-color: #dcfce7;
    }
}

/* Drag states */
.product-item.dragging {
    opacity: 0.5 !important;
    transform: rotate(5deg) scale(0.95);
    z-index: 1000;
}

.product-item:not(.dragging):hover .drag-handle {
    animation: wiggle 0.5s ease-in-out;
}

@keyframes wiggle {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-2deg); }
    75% { transform: rotate(2deg); }
}

/* Scroll-triggered Animation Styles - Fast and Modern */
.scroll-animate {
    opacity: 0 !important;
    transform: translateY(30px) !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, opacity;
}

.scroll-animate.animate-in {
    opacity: 1 !important;
    transform: translateY(0) !important;
}

.scroll-animate-card {
    opacity: 0 !important;
    transform: translateY(20px) scale(0.98) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, opacity;
}

.scroll-animate-card.animate-in {
    opacity: 1 !important;
    transform: translateY(0) scale(1) !important;
}

/* Force initial hidden state for reliable animations */
.scroll-animate:not(.animate-in),
.scroll-animate-card:not(.animate-in) {
    visibility: hidden;
}

.scroll-animate.animate-in,
.scroll-animate-card.animate-in {
    visibility: visible;
}

/* Stagger animation delays - Faster and more responsive */
.scroll-animate[data-delay="0.1"] { transition-delay: 0.05s; }
.scroll-animate[data-delay="0.2"] { transition-delay: 0.1s; }
.scroll-animate[data-delay="0.3"] { transition-delay: 0.15s; }
.scroll-animate[data-delay="0.4"] { transition-delay: 0.2s; }
.scroll-animate[data-delay="0.5"] { transition-delay: 0.25s; }

.scroll-animate-card[data-delay="0.05"] { transition-delay: 0.02s; }
.scroll-animate-card[data-delay="0.1"] { transition-delay: 0.05s; }
.scroll-animate-card[data-delay="0.15"] { transition-delay: 0.08s; }
.scroll-animate-card[data-delay="0.2"] { transition-delay: 0.1s; }
.scroll-animate-card[data-delay="0.25"] { transition-delay: 0.13s; }
.scroll-animate-card[data-delay="0.3"] { transition-delay: 0.15s; }
.scroll-animate-card[data-delay="0.35"] { transition-delay: 0.18s; }
.scroll-animate-card[data-delay="0.4"] { transition-delay: 0.2s; }
.scroll-animate-card[data-delay="0.45"] { transition-delay: 0.23s; }
.scroll-animate-card[data-delay="0.5"] { transition-delay: 0.25s; }

/* Simple title animation - clean and fast */
.title-animate {
    opacity: 0;
    transform: translateY(20px);
    animation: titleFadeIn 0.4s ease-out forwards;
    animation-delay: 0.1s;
}

@keyframes titleFadeIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced button animations - Fast and responsive */
.product-item .btn-accent {
    transform: scale(0.95);
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-item:hover .btn-accent {
    transform: scale(1);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Floating animation for empty states */
.text-center svg {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { 
        transform: translateY(0px);
    }
    50% { 
        transform: translateY(-10px);
    }
}

/* Smooth item animations */
.shopping-item {
    transition: all 0.2s ease;
}

.shopping-item:not(.item-animated) {
    opacity: 1;
    transform: translateX(0);
}

/* Disable initial animations on form submissions */
.no-initial-animation .scroll-animate,
.no-initial-animation .scroll-animate-card {
    opacity: 1 !important;
    transform: translateY(0) scale(1) !important;
    transition: none !important;
}

/* Confirmation Modal Styles - Matching Website Theme */
#confirm-modal {
    backdrop-filter: blur(4px);
    background-color: rgba(0, 0, 0, 0.5);
}

#confirm-modal > div {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.scale-95 {
    transform: scale(0.95);
}

.scale-100 {
    transform: scale(1);
}

/* Modal specific styles using website theme colors */
#confirm-modal .bg-error {
    background-color: #dc2626;
}

#confirm-modal .bg-error:hover {
    background-color: #b91c1c;
}

#confirm-modal .bg-error-700 {
    background-color: #b91c1c;
}

#confirm-modal .bg-error-100 {
    background-color: #fee2e2;
}

#confirm-modal .text-error {
    color: #dc2626;
}

#confirm-modal .shadow-card {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

#confirm-modal .bg-surface-100 {
    background-color: #f1f5f9;
}

#confirm-modal .bg-surface-200 {
    background-color: #e2e8f0;
}

#confirm-modal .text-text-primary {
    color: #1e293b;
}

#confirm-modal .text-text-secondary {
    color: #64748b;
}

#confirm-modal .hover\:text-text-primary:hover {
    color: #1e293b;
}

/* Compact button styling */
#confirm-modal button {
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

#confirm-modal button:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>

<?php include 'includes/footer.php'; ?>