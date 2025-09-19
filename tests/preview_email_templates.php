<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmScout Email Template Preview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .preview-header {
            background: linear-gradient(135deg, #2D5016, #75A347);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .template-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }
        .template-preview {
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        .template-title {
            background: #2D5016;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }
        .email-content {
            transform: scale(0.6);
            transform-origin: top left;
            width: 166.67%;
            height: 600px;
            overflow: hidden;
        }
        @media (max-width: 768px) {
            .template-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h1>🎉 FarmScout Email Template Preview</h1>
            <p>See how your new special event email templates look without sending actual emails</p>
        </div>

        <div class="template-grid">
<?php
require_once 'includes/enhanced_functions.php';

// Sample product data
$sample_alert_data = [
    'email' => 'preview@farmscout.com',
    'alert_type' => 'below',
    'target_price' => 40.00,
    'product' => [
        'name' => 'Premium Tomatoes',
        'filipino_name' => 'Premium na Kamatis',
        'previous_price' => 65.00,
        'current_price' => 35.00,
        'unit' => 'kg',
        'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400'
    ]
];

$templates = [
    'special_event_v2' => 'Special Event V2 (Email Optimized)',
    'special_event' => 'Special Event (Original)',
    'standard' => 'Standard FarmScout',
    'professional' => 'Professional Clean'
];

foreach ($templates as $template_key => $template_name) {
    echo "<div class='template-preview'>\n";
    echo "<div class='template-title'>{$template_name}</div>\n";
    echo "<div class='email-content'>\n";
    
    try {
        // Get the mailer and generate email content without sending
        $mailer = getEnhancedMailer();
        
        // Get template configuration
        $template_config_file = __DIR__ . '/config/email_templates.php';
        $template_config = file_exists($template_config_file) ? include $template_config_file : [];
        
        $template_options = [
            'special_event' => 'price_alert_special_event.html',
            'special_event_v2' => 'price_alert_special_event_v2.html',
            'standard' => 'price_alert_template.html',
            'professional' => 'price_alert_professional.html',
            'dark' => 'price_alert_dark_theme.html'
        ];
        
        $template_name_file = $template_options[$template_key] ?? $template_options['special_event_v2'];
        $template_path = __DIR__ . '/includes/email_templates/' . $template_name_file;
        
        if (file_exists($template_path)) {
            $template_content = file_get_contents($template_path);
            
            // Replace template variables
            $variables = [
                'ALERT_MESSAGE' => "Great news! The price has dropped below your target price of ₱40.00.",
                'FILIPINO_NAME' => $sample_alert_data['product']['filipino_name'],
                'PRODUCT_NAME' => $sample_alert_data['product']['name'],
                'PRODUCT_IMAGE' => $sample_alert_data['product']['image_url'],
                'OLD_PRICE' => '₱' . number_format($sample_alert_data['product']['previous_price'], 2),
                'NEW_PRICE' => '₱' . number_format($sample_alert_data['product']['current_price'], 2),
                'UNIT' => $sample_alert_data['product']['unit'],
                'PRICE_CHANGE' => '₱' . number_format(abs($sample_alert_data['product']['previous_price'] - $sample_alert_data['product']['current_price']), 2),
                'PRICE_CHANGE_PERCENT' => number_format((($sample_alert_data['product']['previous_price'] - $sample_alert_data['product']['current_price']) / $sample_alert_data['product']['previous_price']) * 100, 1),
                'PRICE_CHANGE_ICON' => '💰',
                'PRICE_CHANGE_COLOR' => '#059669',
                'EMAIL' => $sample_alert_data['email'],
                'CURRENT_YEAR' => date('Y')
            ];
            
            foreach ($variables as $key => $value) {
                $template_content = str_replace('{{' . $key . '}}', $value, $template_content);
            }
            
            // Remove the HTML/body tags for embedding
            $template_content = preg_replace('/<html[^>]*>|<\/html>|<body[^>]*>|<\/body>|<head>.*?<\/head>/is', '', $template_content);
            
            echo $template_content;
        } else {
            echo "<p style='padding: 20px; color: red;'>Template file not found: {$template_name_file}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='padding: 20px; color: red;'>Error loading template: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>\n"; // email-content
    echo "</div>\n"; // template-preview
}
?>
        </div>
        
        <div style="padding: 20px; text-align: center; background: #f8f9fa; border-top: 1px solid #ddd;">
            <h3>🎯 Template Features</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">
                <div>✅ Bold "SPECIAL PRICE EVENT" header</div>
                <div>✅ Red "PRICE DROP ALERT!" badge</div>
                <div>✅ Product image with elegant framing</div>
                <div>✅ Clear before/after price comparison</div>
                <div>✅ Prominent "YOU SAVE" messaging</div>
                <div>✅ Professional FarmScout branding</div>
                <div>✅ "BROWSE MORE DEALS" call-to-action</div>
                <div>✅ Mobile responsive design</div>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 5px;">
                <strong>💡 Note:</strong> These previews show how your emails will look in users' inboxes. 
                The templates are fully functional and ready to use!
            </div>
        </div>
    </div>
</body>
</html>