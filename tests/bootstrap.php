<?php
/**
 * PHPUnit Bootstrap File for FarmScout Online
 * Sets up the testing environment
 */

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define testing constants
define('FARMSCOUT_TESTING', true);
define('FARMSCOUT_ROOT', dirname(__DIR__));

// Include Composer autoloader if available
$autoloader = FARMSCOUT_ROOT . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Include necessary files for testing (with error handling)
try {
    // First check if we can include required files
    if (!file_exists(FARMSCOUT_ROOT . '/config/database.php')) {
        // Create a temporary Database class for testing
        class Database {
            public function getConnection() {
                return getTestDB();
            }
        }
    }
    
    // Check for email config
    if (!file_exists(FARMSCOUT_ROOT . '/config/email.php')) {
        // Create dummy email config file path
        if (!is_dir(FARMSCOUT_ROOT . '/config')) {
            mkdir(FARMSCOUT_ROOT . '/config', 0755, true);
        }
        file_put_contents(FARMSCOUT_ROOT . '/config/email.php', '<?php // Dummy email config for testing ?>');
    }
    
    // Override the getDB function for testing
    if (!function_exists('getDB')) {
        function getDB() {
            return getTestDB();
        }
    }
    
    // Include enhanced functions with error handling
    if (file_exists(FARMSCOUT_ROOT . '/includes/enhanced_functions.php')) {
        // Suppress errors during include for testing
        $original_error_reporting = error_reporting(0);
        @include_once FARMSCOUT_ROOT . '/includes/enhanced_functions.php';
        error_reporting($original_error_reporting);
    }
    
} catch (Exception $e) {
    // If includes fail, we'll work with basic testing functions only
    echo "Note: Some functions may not be available for testing: " . $e->getMessage() . "\n";
}

/**
 * Simple test framework for environments without PHPUnit
 */
class SimpleTestFramework
{
    private static $tests = [];
    private static $passed = 0;
    private static $failed = 0;

    public static function test($name, $callback)
    {
        self::$tests[] = ['name' => $name, 'callback' => $callback];
    }

    public static function assertEquals($expected, $actual, $message = '')
    {
        if ($expected === $actual) {
            self::$passed++;
            echo "✓ PASS: $message\n";
            return true;
        } else {
            self::$failed++;
            echo "✗ FAIL: $message\n";
            echo "  Expected: " . var_export($expected, true) . "\n";
            echo "  Actual: " . var_export($actual, true) . "\n";
            return false;
        }
    }

    public static function assertTrue($condition, $message = '')
    {
        return self::assertEquals(true, $condition, $message);
    }

    public static function assertFalse($condition, $message = '')
    {
        return self::assertEquals(false, $condition, $message);
    }

    public static function assertNotEmpty($value, $message = '')
    {
        $result = !empty($value);
        return self::assertTrue($result, $message);
    }

    public static function assertEmpty($value, $message = '')
    {
        $result = empty($value);
        return self::assertTrue($result, $message);
    }

    public static function assertContains($needle, $haystack, $message = '')
    {
        $result = false;
        if (is_array($haystack)) {
            $result = in_array($needle, $haystack);
        } elseif (is_string($haystack)) {
            $result = strpos($haystack, $needle) !== false;
        }
        return self::assertTrue($result, $message);
    }

    public static function runAll()
    {
        echo "Running " . count(self::$tests) . " tests...\n\n";

        foreach (self::$tests as $test) {
            echo "Running: {$test['name']}\n";
            try {
                call_user_func($test['callback']);
            } catch (Exception $e) {
                self::$failed++;
                echo "✗ ERROR in {$test['name']}: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }

        echo "Results: " . self::$passed . " passed, " . self::$failed . " failed\n";
        return self::$failed === 0;
    }
}

/**
 * Mock database connection for testing
 */
function getTestDB()
{
    static $testConn = null;
    
    if ($testConn === null) {
        try {
            $testConn = new PDO(
                'sqlite::memory:', // Use in-memory SQLite for testing
                null,
                null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Create test tables
            $testConn->exec("
                CREATE TABLE products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    filipino_name TEXT NOT NULL,
                    current_price DECIMAL(10,2),
                    previous_price DECIMAL(10,2),
                    unit TEXT DEFAULT 'kg',
                    is_active INTEGER DEFAULT 1,
                    is_featured INTEGER DEFAULT 0
                );
            ");
            
            $testConn->exec("
                CREATE TABLE categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    filipino_name TEXT NOT NULL,
                    is_active INTEGER DEFAULT 1
                );
            ");
            
            // Insert test data
            $testConn->exec("
                INSERT INTO categories (name, filipino_name) VALUES 
                ('Vegetables', 'Gulay'),
                ('Fruits', 'Prutas');
            ");
            
            $testConn->exec("
                INSERT INTO products (name, filipino_name, current_price, previous_price, unit) VALUES 
                ('Tomato', 'Kamatis', 45.00, 50.00, 'kg'),
                ('Rice', 'Bigas', 52.00, 52.00, 'kg'),
                ('Banana', 'Saging', 60.00, 55.00, 'kg');
            ");
            
        } catch (Exception $e) {
            echo "Test database setup failed: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    return $testConn;
}

// Define basic functions that might be missing
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map('sanitizeInput', $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₱' . number_format((float)$amount, 2);
    }
}

if (!function_exists('formatPriceChange')) {
    function formatPriceChange($current, $previous) {
        if ($previous == 0) {
            return ['icon' => 'stable', 'text' => 'New', 'class' => 'text-gray-500'];
        }
        
        $change = $current - $previous;
        $percent = abs(($change / $previous) * 100);
        
        if ($change > 0) {
            return [
                'icon' => 'up',
                'text' => '+₱' . number_format($change, 2) . ' (+' . number_format($percent, 1) . '%)',
                'class' => 'text-red-500'
            ];
        } elseif ($change < 0) {
            return [
                'icon' => 'down', 
                'text' => '-₱' . number_format(abs($change), 2) . ' (-' . number_format($percent, 1) . '%)',
                'class' => 'text-green-500'
            ];
        } else {
            return ['icon' => 'stable', 'text' => 'No change', 'class' => 'text-gray-500'];
        }
    }
}

if (!function_exists('getCSRFToken')) {
    function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('validatePassword')) {
    function validatePassword($password) {
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
}

echo "FarmScout Online Test Bootstrap Loaded\n";
echo "Testing Environment: " . (defined('FARMSCOUT_TESTING') ? 'Active' : 'Inactive') . "\n";
echo "==========================================\n\n";
