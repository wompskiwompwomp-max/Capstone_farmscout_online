-- Enhanced FarmScout Online Database Schema
-- Improved version with better performance, security, and features

CREATE DATABASE IF NOT EXISTS farmscout_online;
USE farmscout_online;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'vendor', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Enhanced categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    filipino_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon_path TEXT,
    price_range VARCHAR(50),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sort_order (sort_order),
    INDEX idx_active (is_active)
);

-- Enhanced products table with better indexing
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    filipino_name VARCHAR(150) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    current_price DECIMAL(10, 2) NOT NULL,
    previous_price DECIMAL(10, 2) DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'kg',
    image_url TEXT,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_price (current_price),
    FULLTEXT idx_search (name, filipino_name, description)
);

-- Enhanced vendors table
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    stall_number VARCHAR(20),
    address TEXT,
    specialties TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stall (stall_number),
    INDEX idx_active (is_active)
);

-- Enhanced price history with better tracking
CREATE TABLE price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    change_type ENUM('increase', 'decrease', 'stable') DEFAULT 'stable',
    change_percentage DECIMAL(5, 2) DEFAULT 0,
    recorded_by INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product_date (product_id, recorded_at),
    INDEX idx_change_type (change_type)
);

-- Price alerts table
CREATE TABLE price_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    target_price DECIMAL(10, 2) NOT NULL,
    alert_type ENUM('below', 'above', 'change') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_email (user_email),
    INDEX idx_product (product_id),
    INDEX idx_active (is_active)
);

-- Shopping lists table
CREATE TABLE shopping_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_session VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_session (user_session),
    INDEX idx_product (product_id)
);

-- Market statistics table
CREATE TABLE market_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_products INT DEFAULT 0,
    active_vendors INT DEFAULT 0,
    avg_price_change DECIMAL(5, 2) DEFAULT 0,
    most_searched_product VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date),
    INDEX idx_date (date)
);

-- User sessions for analytics
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_views INT DEFAULT 0,
    search_queries INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@farmscout.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert default categories with improved data
INSERT INTO categories (name, filipino_name, description, icon_path, price_range, sort_order) VALUES
('Vegetables', 'Gulay', 'Fresh vegetables and leafy greens from local farms', 'M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z', '₱25-₱120/kg', 1),
('Fruits', 'Prutas', 'Fresh fruits and seasonal produce from local orchards', 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z', '₱40-₱200/kg', 2),
('Meat', 'Karne', 'Fresh meat products from local butchers', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', '₱280-₱450/kg', 3),
('Fish', 'Isda', 'Fresh fish and seafood from local fishermen', 'M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z', '₱150-₱350/kg', 4),
('Processed Goods', 'Processed', 'Packaged and processed food items', 'M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z', '₱35-₱180/pack', 5);

-- Insert enhanced sample products
INSERT INTO products (name, filipino_name, description, category_id, current_price, previous_price, unit, image_url, is_featured, created_by) VALUES
('Tomatoes', 'Kamatis', 'Fresh red tomatoes from local farms', 1, 45.00, 50.00, 'kg', 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400', TRUE, 1),
('Regular Rice', 'Bigas', 'Premium quality white rice', 5, 52.00, 52.00, 'kg', 'https://images.pixabay.com/photo/2016/08/11/08/04/rice-1584109_960_720.jpg', TRUE, 1),
('Milkfish', 'Bangus', 'Fresh milkfish from local fishermen', 4, 180.00, 170.00, 'kg', 'https://images.unsplash.com/photo-1544943910-4c1dc44aab44?auto=format&fit=crop&w=400&q=80', TRUE, 1),
('Red Onions', 'Sibuyas Pula', 'Fresh red onions from local farms', 1, 85.00, 100.00, 'kg', 'https://images.pexels.com/photos/1435735/pexels-photo-1435735.jpeg?auto=compress&cs=tinysrgb&w=400', TRUE, 1),
('Pork Shoulder', 'Baboy Kasim', 'Fresh pork shoulder from local butchers', 3, 320.00, 315.00, 'kg', 'https://images.pexels.com/photos/1435735/pexels-photo-1435735.jpeg?auto=compress&cs=tinysrgb&w=400', FALSE, 1),
('Cabbage', 'Repolyo', 'Fresh cabbage from local farms', 1, 35.00, 30.00, 'kg', 'https://images.unsplash.com/photo-1594282486552-05b4d80fbb9f?auto=format&fit=crop&w=400&q=80', FALSE, 1),
('Bananas', 'Saging', 'Fresh bananas from local plantations', 2, 60.00, 55.00, 'kg', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=400&q=80', FALSE, 1),
('Chicken', 'Manok', 'Fresh chicken from local poultry farms', 3, 180.00, 175.00, 'kg', 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=400&q=80', FALSE, 1),
('Tilapia', 'Tilapia', 'Fresh tilapia fish from local fish farms', 4, 120.00, 115.00, 'kg', 'https://images.unsplash.com/photo-1544943910-4c1dc44aab44?auto=format&fit=crop&w=400&q=80', FALSE, 1),
('Cooking Oil', 'Mantika', 'Premium cooking oil 1L bottle', 5, 85.00, 80.00, 'piece', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&q=80', FALSE, 1);

-- Insert enhanced sample vendors
INSERT INTO vendors (name, contact_person, phone, email, stall_number, address, specialties) VALUES
('Aling Maria\'s Vegetables', 'Maria Santos', '09123456789', 'maria@example.com', 'A-12', 'Stall A-12, Baloan Public Market', 'Fresh vegetables, leafy greens'),
('Kuya Ben\'s Fish Stall', 'Benjamin Cruz', '09234567890', 'ben@example.com', 'B-05', 'Stall B-05, Baloan Public Market', 'Fresh fish, seafood'),
('Tita Rosa\'s Meat Shop', 'Rosa Garcia', '09345678901', 'rosa@example.com', 'C-18', 'Stall C-18, Baloan Public Market', 'Fresh meat, poultry'),
('Mang Pedro\'s Fruits', 'Pedro Reyes', '09456789012', 'pedro@example.com', 'A-22', 'Stall A-22, Baloan Public Market', 'Fresh fruits, seasonal produce'),
('Sari-Sari Store ni Aling Carmen', 'Carmen Lopez', '09567890123', 'carmen@example.com', 'D-08', 'Stall D-08, Baloan Public Market', 'Processed goods, pantry items');

-- Insert sample price history with enhanced tracking
INSERT INTO price_history (product_id, price, change_type, change_percentage, recorded_by) VALUES
(1, 50.00, 'decrease', -10.00, 1),
(1, 48.00, 'decrease', -4.00, 1),
(1, 45.00, 'decrease', -6.25, 1),
(2, 52.00, 'stable', 0.00, 1),
(3, 170.00, 'increase', 5.88, 1),
(3, 175.00, 'increase', 2.94, 1),
(3, 180.00, 'increase', 2.86, 1);

-- Insert initial market statistics
INSERT INTO market_stats (date, total_products, active_vendors, avg_price_change, most_searched_product) VALUES
(CURDATE(), 10, 5, -2.5, 'Kamatis');

-- Create views for better performance
CREATE VIEW product_summary AS
SELECT 
    p.id,
    p.name,
    p.filipino_name,
    p.current_price,
    p.previous_price,
    p.unit,
    p.is_featured,
    c.name as category_name,
    c.filipino_name as category_filipino,
    CASE 
        WHEN p.previous_price = 0 THEN 0
        ELSE ROUND(((p.current_price - p.previous_price) / p.previous_price) * 100, 2)
    END as price_change_percentage
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.is_active = 1;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE UpdateProductPrice(
    IN p_product_id INT,
    IN p_new_price DECIMAL(10,2),
    IN p_user_id INT
)
BEGIN
    DECLARE old_price DECIMAL(10,2);
    DECLARE change_type VARCHAR(10);
    DECLARE change_percentage DECIMAL(5,2);
    
    -- Get current price
    SELECT current_price INTO old_price FROM products WHERE id = p_product_id;
    
    -- Calculate change
    IF p_new_price > old_price THEN
        SET change_type = 'increase';
        SET change_percentage = ROUND(((p_new_price - old_price) / old_price) * 100, 2);
    ELSEIF p_new_price < old_price THEN
        SET change_type = 'decrease';
        SET change_percentage = ROUND(((old_price - p_new_price) / old_price) * 100, 2);
    ELSE
        SET change_type = 'stable';
        SET change_percentage = 0;
    END IF;
    
    -- Update product
    UPDATE products 
    SET previous_price = current_price, 
        current_price = p_new_price,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_product_id;
    
    -- Insert price history
    INSERT INTO price_history (product_id, price, change_type, change_percentage, recorded_by)
    VALUES (p_product_id, p_new_price, change_type, change_percentage, p_user_id);
END //

DELIMITER ;

-- Create triggers for automatic updates
DELIMITER //

CREATE TRIGGER update_price_history_trigger
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.current_price != NEW.current_price THEN
        INSERT INTO price_history (product_id, price, recorded_at)
        VALUES (NEW.id, NEW.current_price, NOW());
    END IF;
END //

DELIMITER ;
