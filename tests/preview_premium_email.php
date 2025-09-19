<?php
/**
 * Preview page for the new Premium Nitro Email Template
 */

// Read the template file
$template_path = 'includes/email_templates/price_alert_premium_nitro.html';
$template_content = file_get_contents($template_path);

// Sample data for preview
$sample_data = [
    'PRODUCT_IMAGE' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=300',
    'PRODUCT_NAME' => 'Tomatoes',
    'FILIPINO_NAME' => 'Kamatis',
    'OLD_PRICE' => '₱50.00',
    'NEW_PRICE' => '₱35.00',
    'UNIT' => 'kg',
    'PRICE_CHANGE' => '₱15.00',
    'PRICE_CHANGE_PERCENT' => '30',
    'PRICE_CHANGE_ICON' => '↓',
    'EMAIL' => 'kofi1567@gmail.com',
    'ALERT_MESSAGE' => 'Your price alert for Kamatis (Tomatoes) has been triggered! The price has dropped significantly.',
    'CURRENT_YEAR' => date('Y')
];

// Replace template variables
foreach ($sample_data as $key => $value) {
    $template_content = str_replace('{{' . $key . '}}', $value, $template_content);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmScout Premium Email Template Preview</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        
        .preview-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .preview-title {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .preview-subtitle {
            color: #718096;
            font-size: 16px;
        }
        
        .email-preview {
            max-width: 100%;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }
        
        .preview-controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .feature-list {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .feature-list h3 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 16px;
        }
        
        .feature-list ul {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            color: #4a5568;
        }
        
        .feature-list li::before {
            content: "✨";
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <div class="preview-title">FarmScout Premium Email Template</div>
        <div class="preview-subtitle">Discord Nitro Inspired Design - Beautiful gradients and modern aesthetics</div>
    </div>
    
    <div class="email-preview">
        <?php echo $template_content; ?>
    </div>
    
    <div class="preview-controls">
        <a href="admin.php" class="btn">← Back to Admin</a>
        <a href="preview_email_templates.php" class="btn">View All Templates</a>
        <a href="send_real_email_test.php" class="btn">Send Test Email</a>
    </div>
    
    <div class="feature-list">
        <h3>✨ Premium Nitro Template Features</h3>
        <ul>
            <li><strong>Beautiful Gradient Background:</strong> Purple to blue gradient inspired by Discord Nitro</li>
            <li><strong>Celebration Elements:</strong> Party emojis and celebration badges</li>
            <li><strong>Premium Typography:</strong> Modern font stack with perfect spacing</li>
            <li><strong>Glassmorphism Effects:</strong> Translucent elements with backdrop blur</li>
            <li><strong>Floating Product Cards:</strong> Elevated design with subtle shadows</li>
            <li><strong>Interactive Elements:</strong> Hover effects and micro-interactions</li>
            <li><strong>Mobile Optimized:</strong> Responsive design that works on all devices</li>
            <li><strong>Email Client Compatible:</strong> Tested across major email providers</li>
            <li><strong>Congratulatory Tone:</strong> "Thank you, kofi1567!" messaging style</li>
            <li><strong>Premium Branding:</strong> Elevated FarmScout branding presentation</li>
        </ul>
    </div>
</body>
</html>