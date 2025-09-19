<?php
require_once 'includes/enhanced_functions.php';

echo "=== Checking Price Alerts Table Structure ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

try {
    // Show current table structure
    echo "Current price_alerts table structure:\n";
    $describe_query = "DESCRIBE price_alerts";
    $result = $conn->query($describe_query);
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }
    
    echo "\n=== Missing Columns Check ===\n";
    
    $expected_columns = [
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        'last_sent' => 'TIMESTAMP NULL'
    ];
    
    $existing_columns = array_column($columns, 'Field');
    
    foreach ($expected_columns as $column_name => $column_definition) {
        if (!in_array($column_name, $existing_columns)) {
            echo "Adding missing column: {$column_name}\n";
            $conn->exec("ALTER TABLE price_alerts ADD COLUMN {$column_name} {$column_definition}");
            echo "✅ {$column_name} column added successfully!\n";
        } else {
            echo "✅ {$column_name} column already exists\n";
        }
    }
    
    echo "\n=== Final Table Structure ===\n";
    $result = $conn->query($describe_query);
    $final_columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($final_columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>