<?php
require_once 'config/database.php';
require_once 'config/email.php';

// Get database connection with error handling
if (!function_exists('getDB')) {
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
}

// Now include config manager after getDB is defined
require_once __DIR__ . '/config_manager.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize configuration table
ensureConfigTable();

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
if (!function_exists('getAllProducts')) {
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
}

if (!function_exists('getFeaturedProducts')) {
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
}

if (!function_exists('getCategories')) {
    function getCategories() {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getProductsByCategory')) {
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
}

// Enhanced search function
if (!function_exists('searchProducts')) {
    function searchProducts($search_term, $category_id = null, $sort_by = 'relevance') {
    $conn = getDB();
    if (!$conn) return [];
    
    $search_term = '%' . $search_term . '%';
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino
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
            $query .= " ORDER BY p.is_featured DESC, p.filipino_name ASC";
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
}

// Enhanced product management
if (!function_exists('addProduct')) {
    function addProduct($data) {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        $conn->beginTransaction();
        
        $query = "INSERT INTO products (name, filipino_name, description, category_id, current_price, previous_price, unit, image_url, is_featured, is_active) 
                  VALUES (:name, :filipino_name, :description, :category_id, :current_price, :previous_price, :unit, :image_url, :is_featured, 1)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':filipino_name', $data['filipino_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':current_price', $data['current_price']);
        $stmt->bindParam(':previous_price', $data['previous_price']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':is_featured', $data['is_featured'], PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            $product_id = $conn->lastInsertId();
            
            // Insert initial price history
            $history_query = "INSERT INTO price_history (product_id, price) VALUES (:product_id, :price)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $history_stmt->bindParam(':price', $data['current_price']);
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
}

if (!function_exists('updateProduct')) {
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
        
        // If price changed, add to history and process alerts
        if ($result && $current_product && $current_product['current_price'] != $data['current_price']) {
            $history_query = "INSERT INTO price_history (product_id, price) VALUES (:product_id, :price)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bindParam(':product_id', $id, PDO::PARAM_INT);
            $history_stmt->bindParam(':price', $data['current_price']);
            $history_stmt->execute();
            
            // Process price alerts for this product
            processPriceAlerts($id, $current_product['current_price'], $data['current_price']);
        }
        
        $conn->commit();
        return $result;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
    }
}

if (!function_exists('deleteProduct')) {
    function deleteProduct($id) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "UPDATE products SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    return $stmt->execute();
    }
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

// Get products grouped by category for admin management
function getProductsByCategories() {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT c.id as category_id, c.name as category_name, c.filipino_name as category_filipino, 
                     c.description as category_description, c.icon_path, c.price_range,
                     COUNT(p.id) as product_count
              FROM categories c 
              LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
              WHERE c.is_active = 1 
              GROUP BY c.id, c.name, c.filipino_name, c.description, c.icon_path, c.price_range
              ORDER BY c.sort_order ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsByCategoryForAdmin($category_id) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT p.*, c.name as category_name, c.filipino_name as category_filipino 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = :category_id AND p.is_active = 1 
              ORDER BY p.is_featured DESC, p.filipino_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($category_id) {
    $conn = getDB();
    if (!$conn) return null;
    
    $query = "SELECT * FROM categories WHERE id = :id AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addCategory($data) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "INSERT INTO categories (name, filipino_name, description, icon_path, price_range, sort_order) 
              VALUES (:name, :filipino_name, :description, :icon_path, :price_range, :sort_order)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':filipino_name', $data['filipino_name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':icon_path', $data['icon_path']);
    $stmt->bindParam(':price_range', $data['price_range']);
    $stmt->bindParam(':sort_order', $data['sort_order'], PDO::PARAM_INT);
    
    return $stmt->execute();
}

// Enhanced Email Functions
require_once 'email.php';
require_once 'enhanced_email.php';

/**
 * Send price alert email with enhanced template
 */
function sendPriceAlertEmail($email, $product, $alert_type, $target_price) {
    try {
        $alert_data = [
            'email' => $email,
            'alert_type' => $alert_type,
            'target_price' => $target_price,
            'product' => $product
        ];
        
        // Try enhanced mailer first
        if (function_exists('sendEnhancedPriceAlert')) {
            return sendEnhancedPriceAlert($alert_data);
        }
        
        // Fall back to simple email
        $mailer = getMailer();
        $content = createPriceAlertEmail($product, $alert_type, $target_price, $product['current_price']);
        $html = $mailer->wrapTemplate($content, 'Price Alert - FarmScout Online');
        $subject = "🔔 Price Alert: " . $product['filipino_name'] . " - FarmScout Online";
        
        return $mailer->send($email, $subject, $html, true);
        
    } catch (Exception $e) {
        error_log("Error sending price alert: " . $e->getMessage());
        return false;
    }
}

/**
 * Send welcome email to new user
 */
function sendWelcomeEmailToUser($email, $user_name) {
    try {
        // Try enhanced mailer first
        if (function_exists('sendEnhancedWelcomeEmail')) {
            return sendEnhancedWelcomeEmail($email, $user_name);
        }
        
        // Fall back to simple email
        return sendWelcomeEmail($email, $user_name);
        
    } catch (Exception $e) {
        error_log("Error sending welcome email: " . $e->getMessage());
        return false;
    }
}

/**
 * Process price alerts when products are updated
 */
function processPriceAlerts($product_id, $old_price, $new_price) {
    $conn = getDB();
    if (!$conn) return false;
    
    try {
        // Get all active alerts for this product
        $query = "SELECT pa.*, p.name, p.filipino_name, p.current_price, p.unit, p.image_url, 
                         c.filipino_name as category_filipino
                  FROM price_alerts pa
                  LEFT JOIN products p ON pa.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE pa.product_id = :product_id AND pa.is_active = 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($alerts as $alert) {
            $should_send = false;
            
            // Check if alert conditions are met
            switch ($alert['alert_type']) {
                case 'below':
                    $should_send = $new_price <= $alert['target_price'] && $old_price > $alert['target_price'];
                    break;
                case 'above':
                    $should_send = $new_price >= $alert['target_price'] && $old_price < $alert['target_price'];
                    break;
                case 'change':
                    $should_send = $new_price != $old_price;
                    break;
            }
            
            if ($should_send) {
                // Prepare product data with price history
                $product_data = [
                    'id' => $alert['product_id'],
                    'name' => $alert['name'],
                    'filipino_name' => $alert['filipino_name'],
                    'current_price' => $new_price,
                    'previous_price' => $old_price,
                    'unit' => $alert['unit'],
                    'image_url' => $alert['image_url'],
                    'category_filipino' => $alert['category_filipino']
                ];
                
                // Send the alert
                $result = sendPriceAlertEmail(
                    $alert['user_email'],
                    $product_data,
                    $alert['alert_type'],
                    $alert['target_price']
                );
                
                if ($result) {
                    // Log successful alert
                    logEmailActivity(
                        $alert['user_email'],
                        "Price Alert: " . $alert['filipino_name'],
                        'ALERT_SENT'
                    );
                    
                    // Update alert last_sent timestamp
                    $update_query = "UPDATE price_alerts SET last_sent = NOW() WHERE id = :alert_id";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bindParam(':alert_id', $alert['id'], PDO::PARAM_INT);
                    $update_stmt->execute();
                }
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error processing price alerts: " . $e->getMessage());
        return false;
    }
}

/**
 * Test email system functionality
 */
function testEmailSystem($test_email = null) {
    $results = [];
    
    // Test 1: Basic configuration
    $config = getEmailConfig();
    $results['config'] = [
        'success' => !empty($config['from_email']),
        'message' => !empty($config['from_email']) ? 'Email configuration loaded' : 'Email configuration missing'
    ];
    
    // Test 2: Send test email
    try {
        if (function_exists('sendEnhancedTestEmail')) {
            $result = sendEnhancedTestEmail($test_email);
            $results['test_email'] = [
                'success' => $result,
                'message' => $result ? 'Test email sent successfully' : 'Failed to send test email'
            ];
        } else {
            $result = sendTestEmail($test_email);
            $results['test_email'] = [
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }
    } catch (Exception $e) {
        $results['test_email'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    
    return $results;
}

function updateCategory($id, $data) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "UPDATE categories SET 
              name = :name, 
              filipino_name = :filipino_name, 
              description = :description, 
              icon_path = :icon_path, 
              price_range = :price_range, 
              sort_order = :sort_order,
              updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':filipino_name', $data['filipino_name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':icon_path', $data['icon_path']);
    $stmt->bindParam(':price_range', $data['price_range']);
    $stmt->bindParam(':sort_order', $data['sort_order'], PDO::PARAM_INT);
    
    return $stmt->execute();
}

// Check and trigger price alerts
function checkPriceAlerts($product_id, $old_price, $new_price) {
    $conn = getDB();
    if (!$conn) return false;
    
    // Get all active alerts for this product
    $query = "SELECT pa.*, p.name, p.filipino_name, p.unit, p.image_url, p.category_id
              FROM price_alerts pa
              LEFT JOIN products p ON pa.product_id = p.id
              WHERE pa.product_id = :product_id AND pa.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($alerts as $alert) {
        $shouldTrigger = false;
        $alertMessage = '';
        
        switch ($alert['alert_type']) {
            case 'below':
                if ($new_price <= $alert['target_price'] && $old_price > $alert['target_price']) {
                    $shouldTrigger = true;
                    $alertMessage = "Price dropped below your target of ₱" . number_format($alert['target_price'], 2);
                }
                break;
                
            case 'above':
                if ($new_price >= $alert['target_price'] && $old_price < $alert['target_price']) {
                    $shouldTrigger = true;
                    $alertMessage = "Price rose above your target of ₱" . number_format($alert['target_price'], 2);
                }
                break;
                
            case 'change':
                if ($old_price != $new_price) {
                    $shouldTrigger = true;
                    $change = $new_price - $old_price;
                    $alertMessage = "Price changed by " . ($change > 0 ? "+" : "") . "₱" . number_format($change, 2);
                }
                break;
        }
        
        if ($shouldTrigger) {
            // Send email notification
            $emailSent = sendDetailedPriceAlertEmail(
                $alert['user_email'],
                $alert['name'],
                $alert['filipino_name'],
                $old_price,
                $new_price,
                $alert['unit'],
                $alertMessage,
                $alert['image_url']
            );
            
            // Log the alert trigger
            if ($emailSent) {
                $log_query = "INSERT INTO price_alert_logs (alert_id, triggered_at, old_price, new_price, email_sent) 
                              VALUES (:alert_id, NOW(), :old_price, :new_price, 1)";
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->bindParam(':alert_id', $alert['id'], PDO::PARAM_INT);
                $log_stmt->bindParam(':old_price', $old_price);
                $log_stmt->bindParam(':new_price', $new_price);
                $log_stmt->execute();
            }
        }
    }
    
    return true;
}

// Send price alert email (detailed version)
function sendDetailedPriceAlertEmail($email, $product_name, $filipino_name, $old_price, $new_price, $unit, $alert_message, $image_url = '') {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/email_config.php';
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '🔔 Price Alert: ' . $filipino_name . ' (' . $product_name . ')';
        
        // Price change details
        $price_change = $new_price - $old_price;
        $price_change_percent = $old_price > 0 ? round(($price_change / $old_price) * 100, 2) : 0;
        $price_change_icon = $price_change > 0 ? '📈' : ($price_change < 0 ? '📉' : '➡️');
        $price_change_color = $price_change > 0 ? '#ef4444' : ($price_change < 0 ? '#22c55e' : '#6b7280');
        
        $html_body = file_get_contents(__DIR__ . '/email_templates/price_alert_template.html');
        $html_body = str_replace('{{PRODUCT_NAME}}', htmlspecialchars($product_name), $html_body);
        $html_body = str_replace('{{FILIPINO_NAME}}', htmlspecialchars($filipino_name), $html_body);
        $html_body = str_replace('{{OLD_PRICE}}', formatCurrency($old_price), $html_body);
        $html_body = str_replace('{{NEW_PRICE}}', formatCurrency($new_price), $html_body);
        $html_body = str_replace('{{UNIT}}', htmlspecialchars($unit), $html_body);
        $html_body = str_replace('{{ALERT_MESSAGE}}', htmlspecialchars($alert_message), $html_body);
        $html_body = str_replace('{{PRICE_CHANGE_ICON}}', $price_change_icon, $html_body);
        $html_body = str_replace('{{PRICE_CHANGE_COLOR}}', $price_change_color, $html_body);
        $html_body = str_replace('{{PRICE_CHANGE}}', formatCurrency(abs($price_change)), $html_body);
        $html_body = str_replace('{{PRICE_CHANGE_PERCENT}}', abs($price_change_percent), $html_body);
        $html_body = str_replace('{{CURRENT_YEAR}}', date('Y'), $html_body);
        $html_body = str_replace('{{EMAIL}}', htmlspecialchars($email), $html_body);
        
        $default_image = 'https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=400&auto=format&fit=crop';
        $html_body = str_replace('{{PRODUCT_IMAGE}}', $image_url ?: $default_image, $html_body);
        
        $mail->Body = $html_body;
        
        // Plain text alternative
        $text_body = "FarmScout Price Alert\n\n";
        $text_body .= "Product: {$filipino_name} ({$product_name})\n";
        $text_body .= "Previous Price: " . formatCurrency($old_price) . " per {$unit}\n";
        $text_body .= "New Price: " . formatCurrency($new_price) . " per {$unit}\n";
        $text_body .= "Change: " . ($price_change > 0 ? '+' : '') . formatCurrency($price_change) . " ({$price_change_percent}%)\n\n";
        $text_body .= "{$alert_message}\n\n";
        $text_body .= "Visit FarmScout Online to see more details and manage your price alerts.\n\n";
        $text_body .= "Happy Shopping!\nThe FarmScout Team";
        
        $mail->AltBody = $text_body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Price Alert Email Error: {$mail->ErrorInfo}");
        return false;
    }
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

// Update shopping list quantity
function updateShoppingListQuantity($session_id, $list_id, $quantity) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "UPDATE shopping_lists SET quantity = :quantity WHERE id = :id AND user_session = :session_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':id', $list_id, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $session_id);
    
    return $stmt->execute();
}

// Remove item from shopping list
function removeFromShoppingList($session_id, $list_id) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "DELETE FROM shopping_lists WHERE id = :id AND user_session = :session_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $list_id, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $session_id);
    
    return $stmt->execute();
}

// Clear entire shopping list
function clearShoppingList($session_id) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "DELETE FROM shopping_lists WHERE user_session = :session_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':session_id', $session_id);
    
    return $stmt->execute();
}

// Get shopping list with price calculations
function getShoppingListWithTotals($session_id) {
    $items = getShoppingList($session_id);
    $total_amount = 0;
    $total_items = 0;
    
    foreach ($items as &$item) {
        $item_total = $item['current_price'] * $item['quantity'];
        $item['item_total'] = $item_total;
        $total_amount += $item_total;
        $total_items += $item['quantity'];
    }
    
    return [
        'items' => $items,
        'total_amount' => $total_amount,
        'total_items' => $total_items
    ];
}

// Quick add to shopping list (for integration in other pages)
function quickAddToShoppingList($product_id, $quantity = 1) {
    $session_id = session_id();
    return addToShoppingList($session_id, $product_id, $quantity, '');
}

// Get shopping list item count (for navigation badge)
function getShoppingListCount($session_id) {
    $conn = getDB();
    if (!$conn) return 0;
    
    $query = "SELECT SUM(quantity) as total FROM shopping_lists WHERE user_session = :session_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
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
if (!function_exists('getPriceChange')) {
    function getPriceChange($current_price, $previous_price) {
        if ($previous_price == 0) return 0;
        return $current_price - $previous_price;
    }
}

if (!function_exists('formatPriceChange')) {
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
}

if (!function_exists('getMarketStatus')) {
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
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
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
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₱' . number_format($amount, 2);
    }
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

// Price Alert Functions

/**
 * Create a new price alert
 */
function createPriceAlert($user_email, $product_id, $alert_type, $target_price) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "INSERT INTO price_alerts (user_email, product_id, alert_type, target_price) 
              VALUES (:user_email, :product_id, :alert_type, :target_price)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_email', $user_email);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':alert_type', $alert_type);
    $stmt->bindParam(':target_price', $target_price);
    
    return $stmt->execute();
}

/**
 * Get all price alerts for a user
 */
function getUserPriceAlerts($user_email) {
    $conn = getDB();
    if (!$conn) return [];
    
    $query = "SELECT pa.*, p.name, p.filipino_name, p.current_price, p.unit, p.image_url 
              FROM price_alerts pa 
              JOIN products p ON pa.product_id = p.id 
              WHERE pa.user_email = :user_email AND pa.is_active = 1 
              ORDER BY pa.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_email', $user_email);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Delete a price alert
 */
function deletePriceAlert($alert_id, $user_email) {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "UPDATE price_alerts SET is_active = 0 
              WHERE id = :alert_id AND user_email = :user_email";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':alert_id', $alert_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $user_email);
    
    return $stmt->execute();
}

/**
 * Check and trigger price alerts when prices change
 */
function checkAndTriggerPriceAlerts($product_id, $old_price, $new_price) {
    $conn = getDB();
    if (!$conn) return false;
    
    // Get all active alerts for this product
    $query = "SELECT pa.*, p.name, p.filipino_name, p.unit, p.image_url, p.category_id 
              FROM price_alerts pa 
              JOIN products p ON pa.product_id = p.id 
              WHERE pa.product_id = :product_id AND pa.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($alerts as $alert) {
        $should_trigger = false;
        
        // Check alert conditions
        switch ($alert['alert_type']) {
            case 'below':
                $should_trigger = $new_price <= $alert['target_price'] && $old_price > $alert['target_price'];
                break;
            case 'above':
                $should_trigger = $new_price >= $alert['target_price'] && $old_price < $alert['target_price'];
                break;
            case 'change':
                $should_trigger = $new_price != $old_price;
                break;
        }
        
        if ($should_trigger) {
            // Send price alert email
            $alert_data = [
                'email' => $alert['user_email'],
                'alert_type' => $alert['alert_type'],
                'target_price' => $alert['target_price'],
                'product' => [
                    'name' => $alert['name'],
                    'filipino_name' => $alert['filipino_name'],
                    'previous_price' => $old_price,
                    'current_price' => $new_price,
                    'unit' => $alert['unit'],
                    'image_url' => $alert['image_url']
                ]
            ];
            
            // Send the email using our enhanced email system
            require_once __DIR__ . '/enhanced_email.php';
            $mailer = getEnhancedMailer();
            $result = $mailer->sendPriceAlert($alert_data);
            
            if ($result) {
                // Update last_sent timestamp
                $update_query = "UPDATE price_alerts SET last_sent = NOW() WHERE id = :alert_id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':alert_id', $alert['id'], PDO::PARAM_INT);
                $update_stmt->execute();
                
                // Log the alert
                $log_query = "INSERT INTO price_alert_logs (alert_id, old_price, new_price, email_sent) 
                              VALUES (:alert_id, :old_price, :new_price, 1)";
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->bindParam(':alert_id', $alert['id'], PDO::PARAM_INT);
                $log_stmt->bindParam(':old_price', $old_price);
                $log_stmt->bindParam(':new_price', $new_price);
                $log_stmt->execute();
            }
        }
    }
    
    return true;
}
?>
