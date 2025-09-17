-- Remove price alerts for specific email addresses
-- This will stop notifications for these users

-- Method 1: Delete alerts for a specific email
-- Replace 'user@example.com' with the actual email you want to remove
DELETE FROM price_alerts WHERE user_email = 'user@example.com';

-- Method 2: Delete alerts for multiple specific emails
DELETE FROM price_alerts WHERE user_email IN (
    'user1@example.com',
    'user2@example.com', 
    'user3@example.com'
);

-- Method 3: Deactivate alerts instead of deleting (keeps history)
-- This stops notifications but keeps the alert records
UPDATE price_alerts SET is_active = 0 WHERE user_email = 'user@example.com';

-- Method 4: Delete all alerts (removes ALL user emails - BE CAREFUL!)
-- DELETE FROM price_alerts;

-- Method 5: View all current email addresses before removing
-- Run this first to see what emails exist:
-- SELECT DISTINCT user_email, COUNT(*) as alert_count FROM price_alerts GROUP BY user_email;