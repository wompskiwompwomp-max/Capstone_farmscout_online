<?php
require_once 'config/database.php';

// Session management
session_start();

// Get database connection with error handling
function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            if (!$conn) {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
    return $conn;
}

// Enhanced authentication functions
function authenticateUser($username, $password) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "SELECT id, username, email, password_hash, full_name, role, is_active FROM users WHERE username = :username AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Update last login
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':id', $user['id']);
        $update_stmt->execute();
        
        return $user;
    }
    
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        exit('Access denied');
    }
}

// Enhanced product functions with caching
function getAllProducts($limit = null, $offset = 0) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1 
              ORDER BY p.is_featured DESC, p.updated_at DESC";
    
    if ($limit) {
        $query .= " LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $conn->prepare($query);
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFeaturedProducts($limit = 4) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1 AND p.is_featured = 1 
              ORDER BY p.updated_at DESC 
              LIMIT :limit";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories() {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsByCategory($category_id, $limit = null) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = :category_id AND p.is_active = 1 
              ORDER BY p.is_featured DESC, p.name ASC";
    
    if ($limit) {
        $query .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enhanced search with full-text search
function searchProducts($search_term, $category_id = null, $sort_by = 'relevance') {
    $conn = getDB();
    if (!$conn) return [];
    
    $search_term = '%' . $search_term . '%';
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino,
              MATCH(p.name, p.filipino_name, p.description) AGAINST(:search_term IN NATURAL LANGUAGE MODE) as relevance
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE (p.name LIKE :search_term OR p.filipino_name LIKE :search_term OR p.description LIKE :search_term) 
              AND p.is_active = 1";
    
    if ($category_id) {
        $query .= " AND p.category_id = :category_id";
    }
    
    // Add sorting
    switch ($sort_by) {
        case 'price_low':
            $query .= " ORDER BY p.current_price ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY p.current_price DESC";
            break;
        case 'name':
            $query .= " ORDER BY p.filipino_name ASC";
            break;
        case 'relevance':
        default:
            $query .= " ORDER BY relevance DESC, p.is_featured DESC";
            break;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':search_term', $search_term);
    if ($category_id) {
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enhanced product management
function addProduct($data) {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        $conn->beginTransaction();
        
        $query = "INSERT INTO products (name, filipino_name, description, category_id, current_price, previous_price, unit, image_url, is_featured, is_active, created_by) 
                  VALUES (:name, :filipino_name, :description, :category_id, :current_price, :previous_price, :unit, :image_url, :is_featured, 1, :created_by)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':filipino_name', $data['filipino_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':current_price', $data['current_price']);
        $stmt->bindParam(':previous_price', $data['previous_price']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':is_featured', $data['is_featured'], PDO::PARAM_BOOL);
        $stmt->bindParam(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            $product_id = $conn->lastInsertId();
            
            // Insert initial price history
            $history_query = "INSERT INTO price_history (product_id, price, recorded_by) VALUES (:product_id, :price, :recorded_by)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $history_stmt->bindParam(':price', $data['current_price']);
            $history_stmt->bindParam(':recorded_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
            $history_stmt->execute();
        }
        
        $conn->commit();
        return $result;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

function updateProduct($id, $data) {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        $conn->beginTransaction();
        
        // Get current price for comparison
        $current_query = "SELECT current_price FROM products WHERE id = :id";
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $current_stmt->execute();
        $current_product = $current_stmt->fetch(PDO::FETCH_ASSOC);
        
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
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':filipino_name', $data['filipino_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':current_price', $data['current_price']);
        $stmt->bindParam(':previous_price', $data['previous_price']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':is_featured', $data['is_featured'], PDO::PARAM_BOOL);
        
        $result = $stmt->execute();
        
        // If price changed, add to history
        if ($result && $current_product && $current_product['current_price'] != $data['current_price']) {
            $history_query = "INSERT INTO price_history (product_id, price, recorded_by) VALUES (:product_id, :price, :recorded_by)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bindParam(':product_id', $id, PDO::PARAM_INT);
            $history_stmt->bindParam(':price', $data['current_price']);
            $history_stmt->bindParam(':recorded_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
            $history_stmt->execute();
        }
        
        $conn->commit();
        return $result;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

function deleteProduct($id) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "UPDATE products SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

// Price alert functions
function addPriceAlert($email, $product_id, $target_price, $alert_type) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "INSERT INTO price_alerts (user_email, product_id, target_price, alert_type) 
              VALUES (:email, :product_id, :target_price, :alert_type)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':target_price', $target_price);
    $stmt->bindParam(':alert_type', $alert_type);
    
    return $stmt->execute();
}

function getPriceAlerts($email) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT pa.*, p.name, p.filipino_name, p.current_price, p.unit 
              FROM price_alerts pa 
              LEFT JOIN products p ON pa.product_id = p.id 
              WHERE pa.user_email = :email AND pa.is_active = 1 
              ORDER BY pa.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Shopping list functions
function addToShoppingList($session_id, $product_id, $quantity = 1, $notes = '') {
    $conn = getDB();
    if (!$conn) return false;
    
    // Check if item already exists
    $check_query = "SELECT id FROM shopping_lists WHERE user_session = :session_id AND product_id = :product_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':session_id', $session_id);
    $check_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->fetch()) {
        // Update quantity
        $update_query = "UPDATE shopping_lists SET quantity = quantity + :quantity WHERE user_session = :session_id AND product_id = :product_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':session_id', $session_id);
        $update_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $update_stmt->bindParam(':quantity', $quantity);
        return $update_stmt->execute();
    } else {
        // Insert new item
        $query = "INSERT INTO shopping_lists (user_session, product_id, quantity, notes) 
                  VALUES (:session_id, :product_id, :quantity, :notes)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':notes', $notes);
        
        return $stmt->execute();
    }
}

function getShoppingList($session_id) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT sl.*, p.name, p.filipino_name, p.current_price, p.unit, p.image_url, c.filipino_name as category_filipino
              FROM shopping_lists sl 
              LEFT JOIN products p ON sl.product_id = p.id 
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE sl.user_session = :session_id 
              ORDER BY sl.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Analytics functions
function trackPageView($page) {
    $conn = getDB();
    if (!$conn) return false;
    
    $session_id = session_id();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Check if session exists
    $check_query = "SELECT id FROM user_sessions WHERE session_id = :session_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':session_id', $session_id);
    $check_stmt->execute();
    
    if ($check_stmt->fetch()) {
        // Update existing session
        $update_query = "UPDATE user_sessions SET page_views = page_views + 1, last_activity = NOW() WHERE session_id = :session_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':session_id', $session_id);
        $update_stmt->execute();
    } else {
        // Create new session
        $insert_query = "INSERT INTO user_sessions (session_id, ip_address, user_agent, page_views) 
                         VALUES (:session_id, :ip_address, :user_agent, 1)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':session_id', $session_id);
        $insert_stmt->bindParam(':ip_address', $ip_address);
        $insert_stmt->bindParam(':user_agent', $user_agent);
        $insert_stmt->execute();
    }
}

function trackSearch($search_term) {
    $conn = getDB();
    if (!$conn) return false;
    
    $session_id = session_id();
    
    $query = "UPDATE user_sessions SET search_queries = search_queries + 1 WHERE session_id = :session_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
}

// Enhanced utility functions
function getPriceChange($current_price, $previous_price) {
    if ($previous_price == 0) return 0;
    return $current_price - $previous_price;
}

function formatPriceChange($current_price, $previous_price) {
    $change = getPriceChange($current_price, $previous_price);
    $percentage = $previous_price > 0 ? round(($change / $previous_price) * 100, 2) : 0;
    
    if ($change == 0) {
        return ['class' => 'text-text-muted', 'icon' => 'neutral', 'text' => 'No change', 'percentage' => 0];
    } elseif ($change > 0) {
        return ['class' => 'text-error', 'icon' => 'up', 'text' => '+₱' . number_format($change, 2), 'percentage' => $percentage];
    } else {
        return ['class' => 'text-success', 'icon' => 'down', 'text' => '-₱' . number_format(abs($change), 2), 'percentage' => abs($percentage)];
    }
}

function getMarketStatus() {
    $conn = getDB();
    if (!$conn) return ['is_open' => false, 'active_vendors' => 0, 'last_updated' => 'Unknown'];
    
    $query = "SELECT COUNT(*) as active_vendors FROM vendors WHERE is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'is_open' => true,
        'active_vendors' => $result['active_vendors'] ?? 0,
        'last_updated' => date('g:i A'),
        'total_products' => getTotalProductsCount()
    ];
}

function getTotalProductsCount() {
    $conn = getDB();
    if (!$conn) return 0;
    
    $query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'] ?? 0;
}

function getPriceHistory($product_id, $days = 7) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT price, recorded_at FROM price_history 
              WHERE product_id = :product_id 
              AND recorded_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
              ORDER BY recorded_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enhanced security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePrice($price) {
    return is_numeric($price) && $price >= 0;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatNumber($number) {
    return number_format($number);
}

// Rate limiting
function checkRateLimit($action, $limit = 10, $window = 300) {
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    $window_start = $now - $window;
    
    // Clean old entries
    if (isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = array_filter(
            $_SESSION['rate_limits'][$key],
            function($timestamp) use ($window_start) {
                return $timestamp > $window_start;
            }
        );
    } else {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        return false;
    }
    
    // Add current request
    $_SESSION['rate_limits'][$key][] = $now;
    
    return true;
}

// Error handling
function handleError($message, $code = 500) {
    error_log("FarmScout Error: " . $message);
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Success response
function sendSuccess($data = null, $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}
?>
