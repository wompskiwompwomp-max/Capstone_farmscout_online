<?php
require_once 'config/database.php';

// Get database connection
if (!function_exists('getDB')) {
    function getDB() {
        $database = new Database();
        return $database->getConnection();
    }
}

// Get all products with category information
if (!function_exists('getAllProducts')) {
    function getAllProducts() {
    $conn = getDB();
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1 
              ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get featured products (limit 4)
if (!function_exists('getFeaturedProducts')) {
    function getFeaturedProducts() {
    $conn = getDB();
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1 AND p.is_featured = 1 
              ORDER BY p.updated_at DESC 
              LIMIT 4";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get all categories
if (!function_exists('getCategories')) {
    function getCategories() {
    $conn = getDB();
    $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get products by category
if (!function_exists('getProductsByCategory')) {
    function getProductsByCategory($category_id) {
    $conn = getDB();
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = :category_id AND p.is_active = 1 
              ORDER BY p.name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Search products
if (!function_exists('searchProducts')) {
    function searchProducts($search_term) {
    $conn = getDB();
    $search_term = '%' . $search_term . '%';
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE (p.name LIKE :search_term OR p.filipino_name LIKE :search_term OR p.description LIKE :search_term) 
              AND p.is_active = 1 
              ORDER BY p.name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':search_term', $search_term);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Add new product
if (!function_exists('addProduct')) {
    function addProduct($data) {
    $conn = getDB();
    $query = "INSERT INTO products (name, filipino_name, description, category_id, current_price, previous_price, unit, image_url, is_featured, is_active) 
              VALUES (:name, :filipino_name, :description, :category_id, :current_price, :previous_price, :unit, :image_url, :is_featured, 1)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':filipino_name', $data['filipino_name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':current_price', $data['current_price']);
    $stmt->bindParam(':previous_price', $data['previous_price']);
    $stmt->bindParam(':unit', $data['unit']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':is_featured', $data['is_featured']);
    
    return $stmt->execute();
    }
}

// Update product
if (!function_exists('updateProduct')) {
    function updateProduct($id, $data) {
    $conn = getDB();
    $query = "UPDATE products SET 
              name = :name, 
              filipino_name = :filipino_name, 
              description = :description, 
              category_id = :category_id, 
              current_price = :current_price, 
              previous_price = :previous_price, 
              unit = :unit, 
              image_url = :image_url, 
              is_featured = :is_featured,
              updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':filipino_name', $data['filipino_name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':category_id', $data['category_id']);
    $stmt->bindParam(':current_price', $data['current_price']);
    $stmt->bindParam(':previous_price', $data['previous_price']);
    $stmt->bindParam(':unit', $data['unit']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':is_featured', $data['is_featured']);
    
    return $stmt->execute();
    }
}

// Delete product (soft delete)
if (!function_exists('deleteProduct')) {
    function deleteProduct($id) {
    $conn = getDB();
    $query = "UPDATE products SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
    }
}

// Calculate price change
if (!function_exists('getPriceChange')) {
    function getPriceChange($current_price, $previous_price) {
    if ($previous_price == 0) return 0;
    return $current_price - $previous_price;
    }
}

// Format price change display
if (!function_exists('formatPriceChange')) {
    function formatPriceChange($current_price, $previous_price) {
    $change = getPriceChange($current_price, $previous_price);
    
    if ($change == 0) {
        return ['class' => 'text-text-muted', 'icon' => 'neutral', 'text' => 'No change'];
    } elseif ($change > 0) {
        return ['class' => 'text-error', 'icon' => 'up', 'text' => '+₱' . number_format($change, 2)];
    } else {
        return ['class' => 'text-success', 'icon' => 'down', 'text' => '-₱' . number_format(abs($change), 2)];
    }
    }
}

// Get market status
if (!function_exists('getMarketStatus')) {
    function getMarketStatus() {
    $conn = getDB();
    $query = "SELECT COUNT(*) as active_vendors FROM vendors WHERE is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'is_open' => true,
        'active_vendors' => $result['active_vendors'] ?? 42,
        'last_updated' => date('g:i A')
    ];
    }
}

// Sanitize input
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// Format currency
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₱' . number_format($amount, 2);
    }
}
?>