<?php
/**
 * Email Configuration
 * Configure your SMTP settings here
 */

$config = [
    // Set to true to use SMTP, false to use PHP mail() function
    "use_smtp" => true,
    
    // SMTP Configuration (for Gmail)
    "smtp_host" => "smtp.gmail.com",
    "smtp_port" => 587,
    "smtp_username" => "wompskiwompwomp@gmail.com", // Your Gmail address
    "smtp_password" => "jijn rjxx jijp lobh", // Your Gmail App Password (not regular password)
    
    // From address configuration
    "from_email" => "noreply@farmscout-online.com",
    "from_name" => "FarmScout Online",
    
    // Testing mode (set to false for production)
    "test_mode" => false
];

// Instructions for setting up Gmail App Password:
/*
1. Go to your Google Account settings
2. Navigate to Security
3. Enable 2-Step Verification (if not already enabled)
4. Go to App Passwords
5. Generate an app password for "Mail"
6. Use that 16-character password in smtp_password above
7. Use your full Gmail address in smtp_username

Example:
$config["smtp_username"] = "your.email@gmail.com";
$config["smtp_password"] = "abcd efgh ijkl mnop"; // 16-character app password
*/

?>