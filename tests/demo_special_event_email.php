<?php
require_once 'includes/enhanced_functions.php';

echo "🎉 FARMSCOUT SPECIAL EVENT EMAIL TEMPLATE DEMO 🎉\n";
echo str_repeat("=", 60) . "\n\n";

// Sample product data that matches your reference image style
$demo_products = [
    [
        'name' => 'Premium Tomatoes',
        'filipino_name' => 'Premium na Kamatis',
        'previous_price' => 65.00,
        'current_price' => 35.00,
        'unit' => 'kg',
        'image_url' => 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=400'
    ],
    [
        'name' => 'Fresh Cabbage',
        'filipino_name' => 'Sariwang Repolyo',
        'previous_price' => 45.00,
        'current_price' => 25.00,
        'unit' => 'kg',
        'image_url' => 'https://images.unsplash.com/photo-1594282486552-05b4d80fbb9f?auto=format&fit=crop&w=400&q=80'
    ],
    [
        'name' => 'Organic Bananas',
        'filipino_name' => 'Organic na Saging',
        'previous_price' => 80.00,
        'current_price' => 55.00,
        'unit' => 'kg',
        'image_url' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=400&q=80'
    ]
];

$test_email = 'demo@farmscout.com';

echo "📧 Sending Special Event Price Alerts...\n";
echo "Target Email: {$test_email}\n\n";

foreach ($demo_products as $index => $product) {
    $alert_data = [
        'email' => $test_email,
        'alert_type' => 'below',
        'target_price' => $product['previous_price'] - 5,
        'product' => $product
    ];
    
    // Calculate savings
    $savings = $product['previous_price'] - $product['current_price'];
    $percentage = round(($savings / $product['previous_price']) * 100, 1);
    
    echo "🔸 Product #" . ($index + 1) . ": {$product['filipino_name']}\n";
    echo "   💰 Price Drop: ₱{$product['previous_price']} → ₱{$product['current_price']}\n";
    echo "   💸 You Save: ₱{$savings} ({$percentage}% OFF!)\n";
    
    try {
        // Send using the new special event template
        $result = sendEnhancedPriceAlert($alert_data); // Uses default template from config
        
        if ($result) {
            echo "   ✅ Email sent successfully!\n";
        } else {
            echo "   ❌ Failed to send email\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo str_repeat("-", 60) . "\n";
echo "🎨 TEMPLATE FEATURES SHOWCASE:\n";
echo str_repeat("-", 60) . "\n";

$features = [
    "🎯 Bold 'SPECIAL PRICE EVENT' header (inspired by your reference)",
    "🔴 Red accent colors for urgency and attention",
    "📱 Mobile-responsive design for all devices",
    "🖼️  Product images with elegant borders",
    "💳 Clear before/after price comparison",
    "🏷️  Prominent savings badge with percentage",
    "🔗 'BROWSE MORE DEALS' call-to-action button",
    "🏪 FarmScout branding with 'Tapat na Presyo'",
    "📧 Email client optimized (works in Gmail, Outlook, etc.)",
    "⚡ Eye-catching design similar to promotional materials"
];

foreach ($features as $feature) {
    echo "{$feature}\n";
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "🔧 EASY TEMPLATE SWITCHING:\n";
echo str_repeat("-", 60) . "\n";

echo "To change the default template, edit:\n";
echo "📁 config/email_templates.php\n";
echo "🔧 Change 'default_price_alert_template' value\n\n";

echo "Available templates:\n";
$templates = [
    'special_event_v2' => 'Special Event V2 (Recommended)',
    'special_event' => 'Special Event Original',
    'standard' => 'Standard FarmScout',
    'professional' => 'Professional Clean',
    'dark' => 'Dark Theme'
];

foreach ($templates as $key => $name) {
    echo "  • {$key}: {$name}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 DEMO COMPLETE!\n";
echo "Check your email to see the new special event design in action!\n";
echo "The template captures the bold, promotional style of your reference image.\n";
echo str_repeat("=", 60) . "\n";
?>