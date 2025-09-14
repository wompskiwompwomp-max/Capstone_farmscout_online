<?php
require_once 'includes/enhanced_functions.php';

$page_title = 'Quick Price Check - FarmScout Online';
$page_description = 'Quick mobile price checker for Baloan Public Market';

// Handle AJAX search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = sanitizeInput($_GET['search']);
        $results = searchProducts($search_term);
        
        $formatted_results = [];
        foreach ($results as $product) {
            $price_change = formatPriceChange($product['current_price'], $product['previous_price']);
            $formatted_results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'filipino_name' => $product['filipino_name'],
                'category' => $product['category_filipino'],
                'current_price' => $product['current_price'],
                'formatted_price' => formatCurrency($product['current_price']),
                'unit' => $product['unit'],
                'image_url' => $product['image_url'],
                'price_change' => $price_change
            ];
        }
        
        echo json_encode($formatted_results);
    } else {
        echo json_encode([]);
    }
    exit;
}

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-50 to-surface-100 py-8 md:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4 font-accent">
                Quick Price Check
            </h1>
            <p class="text-lg text-text-secondary mb-8">
                Instantly search and check current prices from Baloan Public Market
            </p>
            
            <!-- Quick Search -->
            <div class="relative max-w-lg mx-auto">
                <input 
                    type="text" 
                    id="quick-search" 
                    placeholder="Type product name (e.g., kamatis, bangus)..." 
                    class="input-field pl-12 pr-4 text-lg"
                    autocomplete="off"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <section class="py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Loading indicator -->
            <div id="loading" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <p class="text-text-secondary mt-2">Searching products...</p>
            </div>
            
            <!-- No results message -->
            <div id="no-results" class="hidden text-center py-12">
                <svg class="w-16 h-16 text-text-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-text-primary mb-2">No products found</h3>
                <p class="text-text-secondary">Try searching for a different product name or check spelling.</p>
            </div>
            
            <!-- Results container -->
            <div id="search-results" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Results will be populated here -->
            </div>
            
            <!-- Initial help text -->
            <div id="help-text" class="text-center py-12">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-text-primary mb-2">Start searching for products</h3>
                <p class="text-text-secondary mb-4">Type in the search box above to find current prices from Baloan Public Market.</p>
                
                <div class="max-w-md mx-auto">
                    <h4 class="font-semibold text-text-primary mb-2">Popular searches:</h4>
                    <div class="flex flex-wrap gap-2 justify-center">
                        <button onclick="searchProduct('kamatis')" class="bg-primary-100 text-primary px-3 py-1 rounded-full text-sm hover:bg-primary-200 transition-colors">Kamatis</button>
                        <button onclick="searchProduct('bangus')" class="bg-primary-100 text-primary px-3 py-1 rounded-full text-sm hover:bg-primary-200 transition-colors">Bangus</button>
                        <button onclick="searchProduct('bigas')" class="bg-primary-100 text-primary px-3 py-1 rounded-full text-sm hover:bg-primary-200 transition-colors">Bigas</button>
                        <button onclick="searchProduct('sibuyas')" class="bg-primary-100 text-primary px-3 py-1 rounded-full text-sm hover:bg-primary-200 transition-colors">Sibuyas</button>
                        <button onclick="searchProduct('manok')" class="bg-primary-100 text-primary px-3 py-1 rounded-full text-sm hover:bg-primary-200 transition-colors">Manok</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Tips -->
    <section class="py-8 bg-surface-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-primary mb-6 text-center">Quick Tips</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-text-primary mb-2">Use Filipino Names</h3>
                    <p class="text-sm text-text-secondary">Search using common Filipino names like "kamatis" instead of "tomatoes" for better results.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-text-primary mb-2">Real-Time Prices</h3>
                    <p class="text-sm text-text-secondary">All prices are updated throughout the day by market vendors and administrators.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-success" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-text-primary mb-2">Verified Prices</h3>
                    <p class="text-sm text-text-secondary">All prices are verified by our market administrators for accuracy and transparency.</p>
                </div>
            </div>
        </div>
    </section>

<script>
let searchTimeout;

// Search functionality
document.getElementById('quick-search').addEventListener('input', function() {
    const searchTerm = this.value.trim();
    
    // Clear existing timeout
    clearTimeout(searchTimeout);
    
    if (searchTerm.length < 2) {
        showHelpText();
        return;
    }
    
    // Debounce search
    searchTimeout = setTimeout(() => {
        performSearch(searchTerm);
    }, 300);
});

function performSearch(searchTerm) {
    showLoading();
    
    fetch(`quick-check.php?ajax=1&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            displayResults(data);
        })
        .catch(error => {
            hideLoading();
            console.error('Search error:', error);
            showNoResults();
        });
}

function displayResults(products) {
    const resultsContainer = document.getElementById('search-results');
    const noResults = document.getElementById('no-results');
    const helpText = document.getElementById('help-text');
    
    helpText.classList.add('hidden');
    
    if (products.length === 0) {
        showNoResults();
        return;
    }
    
    noResults.classList.add('hidden');
    resultsContainer.classList.remove('hidden');
    
    resultsContainer.innerHTML = products.map(product => `
        <div class="card hover:shadow-elevated transition-shadow">
            <div class="flex items-start space-x-4">
                <img src="${product.image_url}" 
                     alt="${product.name}" 
                     class="w-20 h-20 rounded-lg object-cover flex-shrink-0"
                     onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-text-primary text-lg">${product.filipino_name}</h3>
                            <p class="text-sm text-text-secondary">${product.name}</p>
                            <p class="text-xs text-primary font-medium">${product.category}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-2xl font-bold text-primary">${product.formatted_price}</p>
                            <p class="text-sm text-text-secondary">per ${product.unit}</p>
                        </div>
                        
                        <div class="text-right">
                            <div class="flex items-center ${product.price_change.class} text-sm">
                                ${getPriceChangeIcon(product.price_change.icon)}
                                ${product.price_change.text}
                            </div>
                            <p class="text-xs text-text-muted">from yesterday</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function getPriceChangeIcon(iconType) {
    if (iconType === 'up') {
        return `<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 15.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>`;
    } else if (iconType === 'down') {
        return `<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>`;
    } else {
        return `<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                </svg>`;
    }
}

function showLoading() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('search-results').classList.add('hidden');
    document.getElementById('no-results').classList.add('hidden');
    document.getElementById('help-text').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loading').classList.add('hidden');
}

function showNoResults() {
    document.getElementById('no-results').classList.remove('hidden');
    document.getElementById('search-results').classList.add('hidden');
    document.getElementById('help-text').classList.add('hidden');
}

function showHelpText() {
    document.getElementById('help-text').classList.remove('hidden');
    document.getElementById('search-results').classList.add('hidden');
    document.getElementById('no-results').classList.add('hidden');
}

function searchProduct(term) {
    document.getElementById('quick-search').value = term;
    performSearch(term);
}
</script>

<?php include 'includes/footer.php'; ?>