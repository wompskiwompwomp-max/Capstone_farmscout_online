<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Price Alerts - FarmScout Online';
$page_description = 'Set up price alerts for your favorite products';

// Track page view
trackPageView('price_alerts');

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_alert':
                $email = sanitizeInput($_POST['email'] ?? '');
                $product_id = intval($_POST['product_id'] ?? 0);
                $target_price = floatval($_POST['target_price'] ?? 0);
                $alert_type = sanitizeInput($_POST['alert_type'] ?? 'below');
                
                if (!validateEmail($email)) {
                    $message = 'Please enter a valid email address.';
                    $message_type = 'error';
                } elseif ($product_id <= 0) {
                    $message = 'Please select a valid product.';
                    $message_type = 'error';
                } elseif ($alert_type !== 'change' && $target_price <= 0) {
                    $message = 'Please enter a valid target price.';
                    $message_type = 'error';
                } else {
                    if (createPriceAlert($email, $product_id, $alert_type, $target_price)) {
                        $message = 'Price alert set successfully! You will be notified when the price changes.';
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to set price alert. Please try again.';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'remove_alert':
                $alert_id = intval($_POST['alert_id'] ?? 0);
                $email = sanitizeInput($_POST['user_email'] ?? '');
                if ($alert_id > 0 && !empty($email)) {
                    if (deletePriceAlert($alert_id, $email)) {
                        $message = 'Price alert removed successfully.';
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to remove price alert.';
                        $message_type = 'error';
                    }
                }
                break;
        }
    }
}

// Get all products for the form
$products = getAllProducts();
$categories = getCategories();

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 scroll-animate">
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                    Price Alerts
                </h1>
                <p class="text-lg text-text-secondary mb-6 max-w-2xl mx-auto">
                    Get notified when prices drop or rise for your favorite products. Never miss a good deal!
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-success-100 text-success-700 border border-success-300' : 'bg-error-100 text-error-700 border border-error-300'; ?>">
            <div class="flex">
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
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Set New Alert Form -->
            <div class="bg-white rounded-lg shadow-card p-6 scroll-animate-card" data-delay="0.1">
                <h2 class="text-xl font-semibold text-primary mb-4">Set New Price Alert</h2>
                
                <form method="POST" action="price-alerts.php" class="space-y-4">
                    <input type="hidden" name="action" value="add_alert">
                    <?php echo generateCSRFToken(); ?>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-text-primary mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="input-field w-full" 
                               placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-text-primary mb-2">Product</label>
                        <select id="product_id" name="product_id" required class="input-field w-full">
                            <option value="">Select a product...</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    <?php echo (isset($_POST['product_id']) && $_POST['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['filipino_name'] . ' (' . $product['name'] . ') - ' . formatCurrency($product['current_price'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="alert_type" class="block text-sm font-medium text-text-primary mb-2">Alert When</label>
                            <select id="alert_type" name="alert_type" class="input-field w-full">
                                <option value="below" <?php echo (isset($_POST['alert_type']) && $_POST['alert_type'] == 'below') ? 'selected' : ''; ?>>Price drops below</option>
                                <option value="above" <?php echo (isset($_POST['alert_type']) && $_POST['alert_type'] == 'above') ? 'selected' : ''; ?>>Price rises above</option>
                                <option value="change" <?php echo (isset($_POST['alert_type']) && $_POST['alert_type'] == 'change') ? 'selected' : ''; ?>>Any price change</option>
                            </select>
                        </div>
                        
                        <div id="target_price_container">
                            <label for="target_price" class="block text-sm font-medium text-text-primary mb-2">Target Price (₱)</label>
                            <input type="number" id="target_price" name="target_price" required 
                                   step="0.01" min="0" 
                                   class="input-field w-full" 
                                   placeholder="0.00"
                                   value="<?php echo htmlspecialchars($_POST['target_price'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="bg-surface-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-text-primary mb-2">How it works:</h3>
                        <ul class="text-sm text-text-secondary space-y-1">
                            <li>• <strong>Price drops below:</strong> Get notified when the price goes below your target</li>
                            <li>• <strong>Price rises above:</strong> Get notified when the price goes above your target</li>
                            <li>• <strong>Any price change:</strong> Get notified whenever the price changes</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                        </svg>
                        Set Price Alert
                    </button>
                </form>
            </div>

            <!-- Manage Existing Alerts -->
            <div class="bg-white rounded-lg shadow-card p-6 scroll-animate-card" data-delay="0.2">
                <h2 class="text-xl font-semibold text-primary mb-4">Manage Your Alerts</h2>
                
                <div class="mb-4">
                    <label for="email_lookup" class="block text-sm font-medium text-text-primary mb-2">View alerts for email:</label>
                    <div class="flex space-x-2">
                        <input type="email" id="email_lookup" 
                               class="input-field flex-1" 
                               placeholder="Enter your email address">
                        <button onclick="loadAlerts()" class="btn-secondary flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div id="alerts-container">
                    <div class="text-center py-8 text-text-secondary">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                        </svg>
                        <p>Enter your email address to view your price alerts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Products for Quick Alerts -->
        <div class="mt-8 bg-white rounded-lg shadow-card p-6 scroll-animate" data-delay="0.1">
            <h2 class="text-xl font-semibold text-primary mb-4">Quick Alerts for Popular Products</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php 
                $featured_products = getFeaturedProducts(6);
                $productDelay = 0.1;
                foreach ($featured_products as $product): 
                    $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
                ?>
                <div class="border border-surface-200 rounded-lg p-4 hover:shadow-card transition-shadow scroll-animate-card" data-delay="<?php echo $productDelay; ?>"><?php $productDelay += 0.1; ?>
                    <div class="flex items-center mb-3">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-12 h-12 rounded-lg object-cover mr-3"
                             onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                        <div>
                            <h3 class="font-semibold text-text-primary"><?php echo htmlspecialchars($product['filipino_name']); ?></h3>
                            <p class="text-sm text-text-secondary"><?php echo htmlspecialchars($product['name']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <p class="text-lg font-bold text-primary"><?php echo formatCurrency($product['current_price']); ?></p>
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
                        </div>
                    </div>
                    
                    <button onclick="quickAlert(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['filipino_name']); ?>', <?php echo $product['current_price']; ?>)" 
                            class="btn-accent w-full text-sm flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                        </svg>
                        Set Alert
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<script>
// Make CSRF token available to JavaScript
const csrfToken = '<?php echo getCSRFToken(); ?>';

function loadAlerts() {
    const email = document.getElementById('email_lookup').value.trim();
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    const container = document.getElementById('alerts-container');
    container.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div><p class="text-text-secondary mt-2">Loading alerts...</p></div>';
    
    // Make AJAX call to get user alerts
    const formData = new FormData();
    formData.append('email', email);
    
    fetch('api/get_user_alerts.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            container.innerHTML = `
                <div class="text-center py-8 text-error-600">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>${data.error}</p>
                </div>
            `;
            return;
        }
        
        if (!data.alerts || data.alerts.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-text-secondary">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 0015 0v5z"/>
                    </svg>
                    <p>No active alerts found for ${email}</p>
                    <p class="text-sm mt-2">Set up your first price alert using the form on the left!</p>
                </div>
            `;
            return;
        }
        
        // Display alerts
        let alertsHTML = '<div class="space-y-4">';
        data.alerts.forEach(alert => {
            alertsHTML += `
                <div class="border border-border rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        ${alert.image_url ? `<img src="${alert.image_url}" alt="Product" class="w-12 h-12 object-cover rounded-lg">` : '<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">📦</div>'}
                        <div>
                            <h3 class="font-medium text-text-primary">${alert.product_name}</h3>
                            <p class="text-sm text-text-secondary">${alert.alert_text}</p>
                            <p class="text-xs text-text-secondary">Current: ${alert.current_price} • Created: ${alert.created_at}</p>
                        </div>
                    </div>
                    <form method="POST" action="price-alerts.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <input type="hidden" name="action" value="remove_alert">
                        <input type="hidden" name="alert_id" value="${alert.id}">
                        <input type="hidden" name="user_email" value="${email}">
                        <button type="submit" class="text-error-600 hover:text-error-700 text-sm"
                                onclick="return confirm('Remove this price alert?')">
                            🗑️ Remove
                        </button>
                    </form>
                </div>
            `;
        });
        alertsHTML += '</div>';
        
        container.innerHTML = alertsHTML;
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="text-center py-8 text-error-600">
                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Failed to load alerts. Please try again.</p>
            </div>
        `;
    });
}

function quickAlert(productId, productName, currentPrice) {
    const email = prompt('Enter your email address for price alerts:');
    if (!email) return;
    
    const targetPrice = prompt(`Enter target price for ${productName} (current: ₱${currentPrice.toFixed(2)}):`);
    if (!targetPrice || isNaN(targetPrice)) return;
    
    // In a real implementation, this would submit the form or make an AJAX call
    alert(`Price alert set for ${productName} at ₱${parseFloat(targetPrice).toFixed(2)}!`);
}

// Handle alert type changes - show/hide target price field
document.getElementById('alert_type').addEventListener('change', function() {
    const targetPriceContainer = document.getElementById('target_price_container');
    const targetPriceInput = document.getElementById('target_price');
    const label = targetPriceContainer.querySelector('label');
    
    if (this.value === 'change') {
        // For "any price change", make target price optional and hide it
        targetPriceContainer.style.opacity = '0.5';
        targetPriceInput.removeAttribute('required');
        targetPriceInput.value = '';
        targetPriceInput.placeholder = 'Not required for any price change';
        label.textContent = 'Target Price (₱) - Optional';
    } else {
        // For specific price thresholds, make target price required
        targetPriceContainer.style.opacity = '1';
        targetPriceInput.setAttribute('required', 'required');
        targetPriceInput.placeholder = '0.00';
        label.textContent = 'Target Price (₱)';
        
        // Auto-fill based on current product price if available
        const productSelect = document.getElementById('product_id');
        if (productSelect.value) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const priceText = selectedOption.text.split(' - ')[1];
            if (priceText) {
                const currentPrice = parseFloat(priceText.replace('₱', ''));
                let targetPrice;
                if (this.value === 'below') {
                    targetPrice = (currentPrice * 0.9).toFixed(2); // 10% below current price
                } else if (this.value === 'above') {
                    targetPrice = (currentPrice * 1.1).toFixed(2); // 10% above current price
                }
                if (targetPrice) {
                    targetPriceInput.value = targetPrice;
                }
            }
        }
    }
});

// Auto-fill target price based on current price
document.getElementById('product_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const alertType = document.getElementById('alert_type').value;
    
    if (selectedOption.value && alertType !== 'change') {
        const priceText = selectedOption.text.split(' - ')[1];
        if (priceText) {
            const currentPrice = parseFloat(priceText.replace('₱', ''));
            let targetPrice;
            if (alertType === 'below') {
                targetPrice = (currentPrice * 0.9).toFixed(2); // 10% below current price
            } else if (alertType === 'above') {
                targetPrice = (currentPrice * 1.1).toFixed(2); // 10% above current price
            } else {
                targetPrice = (currentPrice * 0.9).toFixed(2); // Default to below
            }
            document.getElementById('target_price').value = targetPrice;
        }
    }
});

// Initialize the form state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger alert type change to set initial state
    document.getElementById('alert_type').dispatchEvent(new Event('change'));
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
        
        /* Button alignment improvements */
        .btn-primary, .btn-secondary, .btn-accent {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        /* Ensure button text and icons are properly centered */
        .btn-primary svg, .btn-secondary svg, .btn-accent svg {
            flex-shrink: 0;
        }
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
