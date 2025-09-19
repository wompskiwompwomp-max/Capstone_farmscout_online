<?php
require_once 'includes/enhanced_functions.php';

echo "=== Setting up Price Alerts System ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

// Create price_alerts table
$sql1 = "
CREATE TABLE IF NOT EXISTS price_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    alert_type ENUM('below', 'above', 'change') NOT NULL DEFAULT 'below',
    target_price DECIMAL(10, 2) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_sent TIMESTAMP NULL,
    
    INDEX idx_user_email (user_email),
    INDEX idx_product_id (product_id),
    INDEX idx_active_alerts (is_active, product_id),
    INDEX idx_last_sent (last_sent),
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create price_alert_logs table
$sql2 = "
CREATE TABLE IF NOT EXISTS price_alert_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    triggered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    old_price DECIMAL(10, 2) NOT NULL,
    new_price DECIMAL(10, 2) NOT NULL,
    email_sent BOOLEAN NOT NULL DEFAULT FALSE,
    
    INDEX idx_alert_id (alert_id),
    INDEX idx_triggered_at (triggered_at),
    
    FOREIGN KEY (alert_id) REFERENCES price_alerts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    // Create price_alerts table
    echo "Creating price_alerts table...\n";
    $conn->exec($sql1);
    echo "✅ price_alerts table created successfully!\n";
    
    // Create price_alert_logs table
    echo "Creating price_alert_logs table...\n";
    $conn->exec($sql2);
    echo "✅ price_alert_logs table created successfully!\n";
    
    // Test by inserting a sample alert
    echo "\nTesting with sample data...\n";
    $sample_sql = "
    INSERT INTO price_alerts (user_email, product_id, alert_type, target_price) 
    VALUES ('test@example.com', 1, 'below', 45.00)";
    
    $conn->exec($sample_sql);
    echo "✅ Sample price alert created!\n";
    
    // Verify the tables exist
    $result = $conn->query("SHOW TABLES LIKE 'price_alert%'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n=== Database Setup Complete ===\n";
    echo "Tables created:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\n🎉 Price Alerts System database setup completed successfully!\n";
    echo "Next: Create the user interface for setting up alerts.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>