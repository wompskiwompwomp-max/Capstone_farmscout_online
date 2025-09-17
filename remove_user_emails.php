<?php
require_once 'includes/enhanced_functions.php';

echo "=== Remove User Emails from Price Alerts ===\n";

$conn = getDB();
if (!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

// First, let's see what email addresses exist
echo "\n📧 Current email addresses in price alerts:\n";
try {
    $stmt = $conn->query("SELECT DISTINCT user_email, COUNT(*) as alert_count FROM price_alerts GROUP BY user_email");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($emails)) {
        echo "No email addresses found in price alerts table.\n";
        exit(0);
    }
    
    foreach ($emails as $row) {
        echo "- {$row['user_email']} ({$row['alert_count']} alerts)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error fetching emails: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Choose an option:\n";
echo "1. Remove specific email address\n";
echo "2. Remove multiple email addresses\n"; 
echo "3. Deactivate specific email (keeps records)\n";
echo "4. Remove ALL emails (⚠️  DANGEROUS!)\n";
echo "5. Exit without changes\n";
echo str_repeat("=", 50) . "\n";

$choice = readline("Enter your choice (1-5): ");

switch ($choice) {
    case '1':
        $email = readline("Enter email address to remove: ");
        $email = trim($email);
        
        if (empty($email)) {
            echo "❌ Email address cannot be empty.\n";
            break;
        }
        
        try {
            $stmt = $conn->prepare("DELETE FROM price_alerts WHERE user_email = ?");
            $stmt->execute([$email]);
            $deleted = $stmt->rowCount();
            
            if ($deleted > 0) {
                echo "✅ Removed $deleted price alert(s) for: $email\n";
            } else {
                echo "⚠️  No alerts found for: $email\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        break;
        
    case '2':
        echo "Enter email addresses to remove (one per line, empty line to finish):\n";
        $emails_to_remove = [];
        
        while (true) {
            $email = readline("Email: ");
            $email = trim($email);
            
            if (empty($email)) {
                break;
            }
            
            $emails_to_remove[] = $email;
        }
        
        if (empty($emails_to_remove)) {
            echo "❌ No email addresses entered.\n";
            break;
        }
        
        try {
            $placeholders = str_repeat('?,', count($emails_to_remove) - 1) . '?';
            $stmt = $conn->prepare("DELETE FROM price_alerts WHERE user_email IN ($placeholders)");
            $stmt->execute($emails_to_remove);
            $deleted = $stmt->rowCount();
            
            echo "✅ Removed $deleted price alert(s) for " . count($emails_to_remove) . " email address(es)\n";
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        break;
        
    case '3':
        $email = readline("Enter email address to deactivate: ");
        $email = trim($email);
        
        if (empty($email)) {
            echo "❌ Email address cannot be empty.\n";
            break;
        }
        
        try {
            $stmt = $conn->prepare("UPDATE price_alerts SET is_active = 0 WHERE user_email = ?");
            $stmt->execute([$email]);
            $updated = $stmt->rowCount();
            
            if ($updated > 0) {
                echo "✅ Deactivated $updated price alert(s) for: $email\n";
                echo "ℹ️  Alert records kept in database but notifications stopped.\n";
            } else {
                echo "⚠️  No active alerts found for: $email\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        break;
        
    case '4':
        $confirm = readline("⚠️  Are you SURE you want to delete ALL email alerts? This cannot be undone! (type 'YES' to confirm): ");
        
        if ($confirm === 'YES') {
            try {
                $stmt = $conn->query("DELETE FROM price_alerts");
                $deleted = $stmt->rowCount();
                echo "✅ Removed ALL $deleted price alerts from the system.\n";
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Operation cancelled.\n";
        }
        break;
        
    case '5':
        echo "👋 Exiting without making changes.\n";
        break;
        
    default:
        echo "❌ Invalid choice. Exiting.\n";
        break;
}

echo "\n🎉 Operation completed!\n";
?>