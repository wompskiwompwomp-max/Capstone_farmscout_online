# 🔧 How to Check and Install Dependencies on Windows

## 🎯 **Quick Answer**

Use one of these methods to check if dependencies need to be installed:

---

## 🚀 **Method 1: Automatic Script (Easiest)**

### **Just Double-Click:**
1. **Double-click**: `setup_dependencies.bat`
2. **Let it run** - it will check everything automatically
3. **Follow any instructions** if something is missing

### **Or Run in PowerShell:**
```powershell
.\setup_dependencies.ps1
```

---

## 👀 **Method 2: Manual Visual Check**

### **Step 1: Check PHP Dependencies**
Look for these folders:
```
C:\xampp\htdocs\farmscout_online\
├── vendor/                    ← Should exist
│   └── phpmailer/            ← Should exist
│       └── phpmailer/        ← Should exist
```

**If missing:** Run `composer install`

### **Step 2: Check CSS Dependencies**
Look for these folders:
```
C:\xampp\htdocs\farmscout_online\
├── node_modules/              ← Should exist
│   ├── tailwindcss/          ← Should exist
│   └── @tailwindcss/         ← Should exist
```

**If missing:** Run `npm install`

### **Step 3: Check CSS Build**
Look for this file:
```
C:\xampp\htdocs\farmscout_online\
└── css/
    └── main.css               ← Should exist and be recent
```

**If missing:** Run `npm run build:css`

---

## 💻 **Method 3: Command Line Check**

### **Open Command Prompt or PowerShell:**
```cmd
cd C:\xampp\htdocs\farmscout_online
```

### **Check PHP Dependencies:**
```cmd
# Check if vendor folder exists
dir vendor

# Check if PHPMailer exists
dir vendor\phpmailer
```

### **Check CSS Dependencies:**
```cmd
# Check if node_modules exists
dir node_modules

# Check if TailwindCSS exists  
dir node_modules\tailwindcss
```

---

## 🔧 **Installation Commands**

### **If PHP Dependencies Missing:**
```cmd
# Install Composer first (if not installed):
# Download from: https://getcomposer.org/download/

# Then install PHP packages:
composer install
```

### **If CSS Dependencies Missing:**
```cmd
# Install Node.js first (if not installed):
# Download from: https://nodejs.org/

# Then install CSS packages:
npm install

# Build the CSS:
npm run build:css
```

---

## ✅ **How to Tell if Everything is Ready**

### **All Good Signs:**
```
✅ vendor/phpmailer/ folder exists
✅ node_modules/tailwindcss/ folder exists  
✅ css/main.css file exists
✅ Website loads at http://localhost/farmscout_online/
✅ No missing file errors in browser console
```

### **Problem Signs:**
```
❌ "vendor" folder missing
❌ "node_modules" folder missing
❌ CSS looks unstyled (no colors/formatting)
❌ Browser shows 404 errors for CSS files
❌ PHP errors about missing classes
```

---

## 🚨 **Common Issues & Solutions**

### **1. "composer: command not found"**
**Solution:** Install Composer
- Go to: https://getcomposer.org/download/
- Download Composer-Setup.exe
- Install and restart command prompt

### **2. "npm: command not found"**
**Solution:** Install Node.js
- Go to: https://nodejs.org/
- Download LTS version
- Install and restart command prompt

### **3. CSS looks broken (no styling)**
**Solution:** Build CSS
```cmd
npm run build:css
```

### **4. PHPMailer errors**
**Solution:** Install PHP dependencies
```cmd
composer install
```

### **5. Permission denied errors**
**Solution:** Run as Administrator
- Right-click Command Prompt
- Select "Run as administrator"
- Navigate to project and try again

---

## 📋 **Migration Checklist**

When setting up on a **new computer**, use this checklist:

### **Before Copying Files:**
```
□ Install XAMPP
□ Install Composer  
□ Install Node.js
□ Start Apache and MySQL
```

### **After Copying Files:**
```
□ Run: setup_dependencies.bat
□ Import database backup
□ Update config/database.php
□ Test: http://localhost/farmscout_online/
□ Verify: No CSS/PHP errors
```

---

## 🎯 **Quick Test Commands**

### **Test if Dependencies Work:**
```cmd
# Test Composer
composer --version

# Test NPM
npm --version  

# Test PHP
C:\xampp\php\php.exe -v

# Test if website loads
# Open browser: http://localhost/farmscout_online/
```

---

## 📞 **Need Help?**

### **If Automatic Script Doesn't Work:**
1. **Check the output** - it tells you what's missing
2. **Install missing software** (Composer/Node.js)  
3. **Run the script again**

### **If Manual Method Confusing:**
1. **Just use the automatic script** - it's easier!
2. **Double-click**: `setup_dependencies.bat`

### **If Website Still Doesn't Work:**
1. **Check XAMPP is running** (Apache + MySQL)
2. **Import your database backup**  
3. **Update database configuration**

---

**✅ The automatic script handles everything for you - just double-click `setup_dependencies.bat`!**