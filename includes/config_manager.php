<?php
/**
 * Configuration Manager for FarmScout Online
 * Handles database-based configuration storage
 */

/**
 * Get configuration value from database
 */
function getConfig($key, $default = null) {
    $conn = getDB();
    if (!$conn) return $default;
    
    $query = "SELECT config_value FROM app_config WHERE config_key = :key";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Try to decode JSON, otherwise return as string
        $value = json_decode($result['config_value'], true);
        return $value !== null ? $value : $result['config_value'];
    }
    
    return $default;
}

/**
 * Set configuration value in database
 */
function setConfig($key, $value) {
    $conn = getDB();
    if (!$conn) return false;
    
    // Encode value as JSON if it's an array or object
    $config_value = is_array($value) || is_object($value) ? json_encode($value) : $value;
    
    $query = "INSERT INTO app_config (config_key, config_value, updated_at) 
              VALUES (:key, :value, NOW()) 
              ON DUPLICATE KEY UPDATE 
              config_value = :value, updated_at = NOW()";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':value', $config_value);
    
    return $stmt->execute();
}

/**
 * Get email configuration
 */
function getEmailConfig() {
    $default_config = [
        'from_email' => 'noreply@farmscout.com',
        'from_name' => 'FarmScout Online - Baloan Public Market',
        'use_smtp' => false,
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_secure' => 'tls',
        'site_url' => 'http://localhost/farmscout_online',
        'support_email' => 'support@farmscout.com',
        'test_mode' => true,
        'test_email' => 'test@example.com',
        'log_emails' => true,
        'email_log_file' => __DIR__ . '/../logs/email.log'
    ];
    
    $config = getConfig('email_config', $default_config);
    
    // Ensure all required keys exist
    return array_merge($default_config, $config);
}

/**
 * Update email configuration
 */
function updateEmailConfig($updates) {
    $current_config = getEmailConfig();
    $new_config = array_merge($current_config, $updates);
    
    return setConfig('email_config', $new_config);
}

/**
 * Create configuration table if it doesn't exist
 */
function ensureConfigTable() {
    $conn = getDB();
    if (!$conn) return false;
    
    $query = "CREATE TABLE IF NOT EXISTS app_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(255) NOT NULL UNIQUE,
        config_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_config_key (config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    return $conn->exec($query) !== false;
}
?>