# 📧 Email System Setup Guide
**FarmScout Online - Email Configuration & Price Alerts**

## 🚀 Quick Start

### Step 1: Install Email System
1. Open your browser and go to: `http://localhost/farmscout_online/install-email.php`
2. Click "Install Email System" button
3. Verify all green checkmarks appear

### Step 2: Configure SMTP Settings
1. Go to: `http://localhost/farmscout_online/email-config.php`
2. Fill in your email settings (see options below)
3. Test the configuration

### Step 3: Test Email Delivery
1. Send a test email to verify it's working
2. Send a sample price alert to see the beautiful email template
3. Configure any remaining settings

---

## 📧 SMTP Configuration Options

### Option 1: Gmail SMTP (Recommended)
```
SMTP Host: smtp.gmail.com
SMTP Port: 587
Encryption: TLS
Username: your-gmail@gmail.com
Password: your-app-password (not regular password!)
```

**Important:** For Gmail, you need to:
1. Enable 2-Factor Authentication
2. Generate an "App Password" in your Google Account settings
3. Use the App Password (16 characters) instead of your regular password

### Option 2: Outlook/Hotmail SMTP
```
SMTP Host: smtp-mail.outlook.com
SMTP Port: 587
Encryption: TLS
Username: your-email@outlook.com
Password: your-password
```

### Option 3: Custom Domain SMTP
```
SMTP Host: mail.yourdomain.com
SMTP Port: 587 (or 465 for SSL)
Encryption: TLS or SSL
Username: your-email@yourdomain.com
Password: your-password
```

---

## ⚙️ Configuration Settings Explained

### Basic Settings
- **From Email**: The email address that will appear as sender
- **From Name**: Display name for emails (e.g., "FarmScout Online")
- **Site URL**: Your website URL for links in emails
- **Support Email**: Where users can contact you for help

### SMTP Settings
- **Enable SMTP**: Turn this ON for production use
- **Test Mode**: Keep ON during setup, turn OFF when ready to send real emails

### Testing Settings
- **Test Email**: Email address to receive test emails during setup

---

## 🔧 Common SMTP Providers

| Provider | SMTP Host | Port | Encryption |
|----------|-----------|------|------------|
| Gmail | smtp.gmail.com | 587 | TLS |
| Outlook | smtp-mail.outlook.com | 587 | TLS |
| Yahoo | smtp.mail.yahoo.com | 587 | TLS |
| cPanel/WHM | mail.yourdomain.com | 587 | TLS |

---

## 🧪 Testing Your Setup

### Test 1: SMTP Connection
- Click "Test Connection" to verify SMTP settings
- Should show "SMTP connection successful"

### Test 2: Basic Email
- Enter your email address
- Click "Send Test Email"
- Check your inbox for a test message

### Test 3: Price Alert Email
- Enter your email address
- Click "Send Sample Alert"
- Check your inbox for a beautiful price alert email

---

## 🛠️ Troubleshooting

### Common Issues

**"Authentication failed"**
- Check username/password
- For Gmail, use App Password, not regular password
- Verify SMTP host and port are correct

**"Connection failed"**
- Check SMTP host spelling
- Verify port number (587 for TLS, 465 for SSL)
- Ensure firewall allows outgoing SMTP connections

**"Test mode emails not appearing"**
- This is normal! Test mode logs emails instead of sending them
- Turn off Test Mode to send real emails
- Check server error logs for test mode messages

**No emails received**
- Check spam/junk folder
- Verify recipient email address is correct
- Turn off Test Mode if it's enabled
- Check email logs in `/logs/email.log`

### Debug Steps
1. Check PHP error logs
2. Check email logs in `/logs/email.log`
3. Verify database connection
4. Test with a different email provider

---

## 📋 Features After Setup

### For Users
- ✅ Set price alerts for favorite products
- ✅ Receive beautiful email notifications when prices change
- ✅ Manage alert preferences
- ✅ Welcome emails for new users

### For Admins
- ✅ Send test emails anytime
- ✅ Monitor email delivery logs
- ✅ Configure all email settings easily
- ✅ View email system status

---

## 🔐 Security Best Practices

1. **Use App Passwords**: Never use your main email password for SMTP
2. **Enable HTTPS**: Always use SSL/TLS encryption for SMTP
3. **Regular Updates**: Keep email settings up to date
4. **Monitor Logs**: Check email logs regularly for issues
5. **Limit Access**: Only admins should access email configuration

---

## 📞 Need Help?

If you encounter issues:

1. **Check the logs**: Look in `/logs/email.log` for detailed error messages
2. **Test step by step**: Use the built-in testing tools to isolate problems
3. **Verify credentials**: Double-check all SMTP settings
4. **Contact provider**: Some email providers have specific requirements

---

## 🎯 Production Checklist

Before going live:

- [ ] SMTP settings configured and tested
- [ ] Test Mode turned OFF
- [ ] Real emails sending successfully
- [ ] Price alerts working when products are updated
- [ ] Email logs are being created
- [ ] Spam folder checked (emails not going to spam)
- [ ] From email address is professional
- [ ] All email links work correctly

---

**🎉 Congratulations!** Your FarmScout Online email system is now ready to send beautiful price alerts to your users!