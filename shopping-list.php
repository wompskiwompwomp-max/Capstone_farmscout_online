<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Shopping List - FarmScout Online';
$page_description = 'Create and manage your shopping lists with real-time prices from Baloan Public Market';

// Track page view
trackPageView('shopping_list');

$session_id = session_id();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
<section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                🛒 Smart Shopping List
            </h1>
            <p class="text-lg text-text-secondary mb-6 max-w-2xl mx-auto">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left Panel: Product Search & Add -->
        <div class="bg-white rounded-lg shadow-card p-6">
            <h2 class="text-xl font-semibold text-primary mb-4">🔍 Add Products to List</h2>
            
            <!-- Search Bar -->
            <div class="mb-6">
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
                    >
                </form>
            </div>

            <!-- Category Quick Filters -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-text-primary mb-3">Quick Categories:</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <button 
                        onclick="searchCategory('<?php echo htmlspecialchars($category['name']); ?>')"
                        class="px-3 py-1 text-sm bg-surface-100 hover:bg-primary-100 text-text-secondary hover:text-primary rounded-full transition-all duration-200"
                    >
                        <?php echo htmlspecialchars($category['filipino_name']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Results / Product Browse -->
            <div id="search-results" class="space-y-3">
                <?php if (!empty($search_results)): ?>
                    <h3 class="font-semibold text-text-primary mb-3">
                        Search Results (<?php echo count($search_results); ?> found)
                    </h3>
                    <?php foreach ($search_results as $product): ?>
                    <div 
                        class="product-item bg-surface-50 rounded-lg p-4 border border-surface-200 hover:border-primary-300 hover:shadow-md transition-all duration-200 cursor-grab"
                        draggable="true"
                        data-product-id="<?php echo $product['id']; ?>"
                        data-product-name="<?php echo htmlspecialchars($product['filipino_name']); ?>"
                        data-product-price="<?php echo $product['current_price']; ?>"
                        data-product-unit="<?php echo htmlspecialchars($product['unit']); ?>"
                        data-product-image="<?php echo htmlspecialchars($product['image_url']); ?>"
                    >
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
                                <div class="drag-handle cursor-grab text-text-secondary hover:text-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
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
        <div class="bg-white rounded-lg shadow-card p-6">
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
                class="border-2 border-dashed border-surface-300 rounded-lg p-8 mb-6 text-center transition-all duration-200 hidden"
            >
                <svg class="w-12 h-12 mx-auto mb-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-text-secondary">Drop products here to add to your list</p>
            </div>

            <!-- Shopping List Items -->
            <div id="shopping-list-items" class="space-y-3">
                <?php if (!empty($shopping_list)): ?>
                    <?php foreach ($shopping_list as $item): ?>
                    <div class="shopping-item bg-surface-50 rounded-lg p-4 border border-surface-200">
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
                                <div class="flex items-center space-x-2">
                                    <button 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)"
                                        class="w-8 h-8 flex items-center justify-center bg-surface-200 hover:bg-surface-300 rounded-full text-text-secondary hover:text-text-primary transition-colors"
                                        <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-semibold"><?php echo $item['quantity']; ?></span>
                                    <button 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                        class="w-8 h-8 flex items-center justify-center bg-surface-200 hover:bg-surface-300 rounded-full text-text-secondary hover:text-text-primary transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
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
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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

<!-- Hidden forms for actions -->
<form id="update-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="update_quantity">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="list_id" id="update-list-id">
    <input type="hidden" name="quantity" id="update-quantity">
</form>

<form id="remove-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="remove_item">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="list_id" id="remove-list-id">
</form>

<form id="clear-form" method="POST" action="shopping-list.php" style="display: none;">
    <input type="hidden" name="action" value="clear_list">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
</form>

<script>
// Search category function
function searchCategory(categoryName) {
    document.getElementById('product-search').value = categoryName;
    document.getElementById('product-search').form.submit();
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

// Quantity update function
function updateQuantity(listId, newQuantity) {
    if (newQuantity < 1) return;
    
    document.getElementById('update-list-id').value = listId;
    document.getElementById('update-quantity').value = newQuantity;
    document.getElementById('update-form').submit();
}

// Remove item function
function removeItem(listId) {
    if (confirm('Are you sure you want to remove this item?')) {
        document.getElementById('remove-list-id').value = listId;
        document.getElementById('remove-form').submit();
    }
}

// Clear list function
function clearListConfirm() {
    if (confirm('Are you sure you want to clear your entire shopping list?')) {
        document.getElementById('clear-form').submit();
    }
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

// Drag and Drop Implementation
document.addEventListener('DOMContentLoaded', function() {
    const productItems = document.querySelectorAll('.product-item');
    const dropZone = document.getElementById('drop-zone');
    let draggedElement = null;

    productItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
            
            // Show drop zone
            dropZone.classList.remove('hidden');
            dropZone.classList.add('border-primary-500', 'bg-primary-50');
        });

        item.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
            draggedElement = null;
            
            // Hide drop zone
            dropZone.classList.add('hidden');
            dropZone.classList.remove('border-primary-500', 'bg-primary-50');
        });
    });

    // Drop zone events
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
        
        if (draggedElement) {
            const productId = draggedElement.dataset.productId;
            const productName = draggedElement.dataset.productName;
            const productPrice = draggedElement.dataset.productPrice;
            const productUnit = draggedElement.dataset.productUnit;
            
            // Auto-add with quantity 1
            addToListQuick(productId, productName, productPrice, productUnit);
        }
    });
});

// Quick add function for drag & drop
function addToListQuick(productId, productName, productPrice, productUnit) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'shopping-list.php';
    form.style.display = 'none';
    
    // Get current search term to preserve it after form submission
    const currentSearch = document.getElementById('product-search').value;
    
    form.innerHTML = `
        <input type="hidden" name="action" value="add_to_list">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="notes" value="">
        <input type="hidden" name="search" value="${currentSearch}">
    `;
    
    document.body.appendChild(form);
    form.submit();
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

/* Drag and drop enhancements */
.product-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.shopping-item {
    transition: all 0.2s ease;
}

.shopping-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
</style>

<?php include 'includes/footer.php'; ?>