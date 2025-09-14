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
        }
    }
}

$categories = getCategories();
$products = getAllProducts();

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary mb-4">Admin Panel</h1>
        <p class="text-text-secondary">Manage products and prices for FarmScout Online</p>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700' : 'bg-error-100 text-error-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

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
                        <th class="text-center py-3 px-4">Actions</th>
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
                            <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="btn-secondary text-xs px-3 py-1 mr-2">Edit</button>
                            <form method="POST" action="admin.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn-accent text-xs px-3 py-1">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>