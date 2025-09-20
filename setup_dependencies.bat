@echo off
REM FarmScout Online - Simple Dependency Checker for Windows
REM Double-click this file to check and install missing dependencies

echo FarmScout Online - Dependency Checker
echo =====================================
echo.

REM Check if we're in the right directory
if not exist "composer.json" (
    echo ERROR: composer.json not found!
    echo Please run this from the farmscout_online directory
    pause
    exit /b 1
)

echo Found composer.json - we're in the right directory
echo.

REM Check vendor folder
echo Checking PHP dependencies...
if exist "vendor" (
    if exist "vendor\phpmailer" (
        echo [OK] PHP dependencies are ready!
        set COMPOSER_OK=1
    ) else (
        echo [MISSING] PHPMailer not found - need to install
        set COMPOSER_OK=0
    )
) else (
    echo [MISSING] vendor folder not found - need to install
    set COMPOSER_OK=0
)

REM Check node_modules folder
echo Checking CSS dependencies...
if exist "node_modules" (
    if exist "node_modules\tailwindcss" (
        echo [OK] CSS dependencies are ready!
        set NPM_OK=1
    ) else (
        echo [MISSING] TailwindCSS not found - need to install
        set NPM_OK=0
    )
) else (
    echo [MISSING] node_modules folder not found - need to install
    set NPM_OK=0
)

echo.
echo ==========================================

REM Install Composer dependencies if needed
if %COMPOSER_OK%==0 (
    echo Installing PHP dependencies...
    composer --version >nul 2>&1
    if %errorlevel%==0 (
        echo Running: composer install
        composer install
        if %errorlevel%==0 (
            echo [SUCCESS] Composer install completed!
        ) else (
            echo [ERROR] Composer install failed!
        )
    ) else (
        echo [ERROR] Composer not found!
        echo Please install Composer from: https://getcomposer.org/download/
    )
    echo.
) else (
    echo [SKIP] PHP dependencies already installed
    echo.
)

REM Install NPM dependencies if needed
if %NPM_OK%==0 (
    echo Installing CSS dependencies...
    npm --version >nul 2>&1
    if %errorlevel%==0 (
        echo Running: npm install
        npm install
        if %errorlevel%==0 (
            echo [SUCCESS] NPM install completed!
            echo Building CSS...
            npm run build:css
        ) else (
            echo [ERROR] NPM install failed!
        )
    ) else (
        echo [ERROR] NPM not found!
        echo Please install Node.js from: https://nodejs.org/
    )
    echo.
) else (
    echo [SKIP] CSS dependencies already installed
    echo.
)

REM Final check
echo Final Status:
echo =============
if exist "vendor\phpmailer" (
    echo [OK] PHP Dependencies: READY
) else (
    echo [X] PHP Dependencies: MISSING
)

if exist "node_modules\tailwindcss" (
    echo [OK] CSS Dependencies: READY
) else (
    echo [X] CSS Dependencies: MISSING
)

if exist "css\main.css" (
    echo [OK] CSS Build: READY
) else (
    echo [!] CSS Build: May need 'npm run build:css'
)

echo.
echo Done! Test your website at: http://localhost/farmscout_online/
echo.
pause