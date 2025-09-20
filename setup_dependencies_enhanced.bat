@echo off
REM FarmScout Online - Enhanced Dependency Setup for Windows
REM This script will check, download, and install everything automatically

echo ===============================================
echo FarmScout Online - Complete Setup Assistant
echo ===============================================
echo.

REM Check if we're in the right directory
if not exist "composer.json" (
    echo [ERROR] composer.json not found!
    echo Please run this from the farmscout_online directory
    pause
    exit /b 1
)

echo [OK] Found composer.json - we're in the correct directory
echo.

REM ===========================================
REM STEP 1: Check Current Status
REM ===========================================
echo STEP 1: Checking current dependency status...
echo -------------------------------------------

set COMPOSER_OK=0
set NPM_OK=0
set COMPOSER_INSTALLED=0
set NODE_INSTALLED=0

REM Check PHP dependencies
if exist "vendor\phpmailer" (
    echo [OK] PHP dependencies are ready
    set COMPOSER_OK=1
) else (
    echo [MISSING] PHP dependencies need installation
)

REM Check CSS dependencies
if exist "node_modules\tailwindcss" (
    echo [OK] CSS dependencies are ready
    set NPM_OK=1
) else (
    echo [MISSING] CSS dependencies need installation
)

echo.

REM ===========================================
REM STEP 2: Check Required Software
REM ===========================================
echo STEP 2: Checking required software...
echo ------------------------------------

REM Check if Composer is available
composer --version >nul 2>&1
if %errorlevel%==0 (
    echo [OK] Composer is installed
    set COMPOSER_INSTALLED=1
) else (
    echo [MISSING] Composer is not installed
)

REM Check if Node.js/NPM is available
npm --version >nul 2>&1
if %errorlevel%==0 (
    echo [OK] Node.js/NPM is installed
    set NODE_INSTALLED=1
) else (
    echo [MISSING] Node.js/NPM is not installed
)

echo.

REM ===========================================
REM STEP 3: Auto-Install Missing Software
REM ===========================================
echo STEP 3: Installing missing software...
echo ------------------------------------

REM Install Composer if missing
if %COMPOSER_INSTALLED%==0 if %COMPOSER_OK%==0 (
    echo Installing Composer...
    echo.
    echo [INFO] Composer is required for PHP dependencies
    echo [ACTION] Opening Composer download page...
    echo.
    echo Please:
    echo 1. Download Composer-Setup.exe
    echo 2. Install it (keep default settings)
    echo 3. Restart this script
    echo.
    start https://getcomposer.org/download/
    echo [WAITING] Press any key after installing Composer...
    pause >nul
    echo.
    
    REM Check again after installation
    composer --version >nul 2>&1
    if %errorlevel%==0 (
        echo [SUCCESS] Composer is now installed!
        set COMPOSER_INSTALLED=1
    ) else (
        echo [ERROR] Composer still not found. Please restart this script after installation.
        pause
        exit /b 1
    )
)

REM Install Node.js if missing
if %NODE_INSTALLED%==0 if %NPM_OK%==0 (
    echo Installing Node.js...
    echo.
    echo [INFO] Node.js is required for CSS compilation
    echo [ACTION] Opening Node.js download page...
    echo.
    echo Please:
    echo 1. Download the LTS version
    echo 2. Install it (keep default settings)
    echo 3. Restart this script
    echo.
    start https://nodejs.org/
    echo [WAITING] Press any key after installing Node.js...
    pause >nul
    echo.
    
    REM Check again after installation
    npm --version >nul 2>&1
    if %errorlevel%==0 (
        echo [SUCCESS] Node.js is now installed!
        set NODE_INSTALLED=1
    ) else (
        echo [ERROR] Node.js still not found. Please restart this script after installation.
        pause
        exit /b 1
    )
)

echo.

REM ===========================================
REM STEP 4: Install Dependencies
REM ===========================================
echo STEP 4: Installing project dependencies...
echo ----------------------------------------

REM Install PHP dependencies if needed
if %COMPOSER_OK%==0 (
    if %COMPOSER_INSTALLED%==1 (
        echo [RUNNING] Installing PHP dependencies...
        composer install --no-dev --optimize-autoloader
        if %errorlevel%==0 (
            echo [SUCCESS] PHP dependencies installed successfully!
            set COMPOSER_OK=1
        ) else (
            echo [ERROR] Failed to install PHP dependencies
        )
    ) else (
        echo [SKIP] Composer not available - cannot install PHP dependencies
    )
) else (
    echo [SKIP] PHP dependencies already installed
)

echo.

REM Install CSS dependencies if needed
if %NPM_OK%==0 (
    if %NODE_INSTALLED%==1 (
        echo [RUNNING] Installing CSS dependencies...
        npm install
        if %errorlevel%==0 (
            echo [SUCCESS] CSS dependencies installed successfully!
            echo [RUNNING] Building CSS files...
            npm run build:css
            if %errorlevel%==0 (
                echo [SUCCESS] CSS build completed!
            ) else (
                echo [WARNING] CSS build had issues, but dependencies are installed
            )
            set NPM_OK=1
        ) else (
            echo [ERROR] Failed to install CSS dependencies
        )
    ) else (
        echo [SKIP] Node.js not available - cannot install CSS dependencies
    )
) else (
    echo [SKIP] CSS dependencies already installed
)

echo.

REM ===========================================
REM STEP 5: Final Status Report
REM ===========================================
echo ===============================================
echo FINAL SETUP STATUS
echo ===============================================

set ALL_READY=1

REM Check PHP dependencies
if exist "vendor\phpmailer" (
    echo [OK] PHP Dependencies: READY
) else (
    echo [X] PHP Dependencies: NOT READY
    set ALL_READY=0
)

REM Check CSS dependencies
if exist "node_modules\tailwindcss" (
    echo [OK] CSS Dependencies: READY
) else (
    echo [X] CSS Dependencies: NOT READY
    set ALL_READY=0
)

REM Check CSS build
if exist "css\main.css" (
    echo [OK] CSS Build: READY
) else (
    echo [!] CSS Build: May need manual 'npm run build:css'
)

echo.

REM Final result
if %ALL_READY%==1 (
    echo ===============================================
    echo SUCCESS! Your FarmScout Online is ready!
    echo ===============================================
    echo.
    echo Next steps:
    echo 1. Start XAMPP (Apache + MySQL)
    echo 2. Import your database backup
    echo 3. Test: http://localhost/farmscout_online/
    echo.
    echo Your website should work perfectly!
) else (
    echo ===============================================
    echo INCOMPLETE SETUP
    echo ===============================================
    echo.
    echo Some dependencies are still missing.
    echo Please install the missing software and run this script again.
    echo.
    echo Need help? Check DEPENDENCY_CHECK.md for manual instructions.
)

echo.
echo Press any key to exit...
pause >nul