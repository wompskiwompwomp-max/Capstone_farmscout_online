-- Price Alerts Table for FarmScout Online
-- This table stores user price alerts and preferences

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
    
    -- Indexes for performance
    INDEX idx_user_email (user_email),
    INDEX idx_product_id (product_id),
    INDEX idx_active_alerts (is_active, product_id),
    INDEX idx_last_sent (last_sent),
    
    -- Foreign key constraint
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Price Alert Logs Table (optional - for tracking alert history)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;