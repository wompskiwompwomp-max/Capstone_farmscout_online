# FarmScout Online - Dependency Setup Script for Windows
# This script checks if dependencies are installed and installs them if missing

Write-Host "FarmScout Online - Dependency Checker" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""

# Get current directory
$currentDir = Get-Location
Write-Host "Current directory: $currentDir" -ForegroundColor Yellow
Write-Host ""

# Check if we're in the right directory
if (!(Test-Path "composer.json")) {
    Write-Host "ERROR: composer.json not found!" -ForegroundColor Red
    Write-Host "Please run this script from the farmscout_online directory" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Found composer.json - we're in the right directory" -ForegroundColor Green
Write-Host ""

# Check vendor folder
Write-Host "Checking PHP dependencies (vendor folder)..." -ForegroundColor Cyan
if (Test-Path "vendor") {
    Write-Host "✅ vendor/ folder exists" -ForegroundColor Green
    
    # Check if PHPMailer is there
    if (Test-Path "vendor/phpmailer") {
        Write-Host "✅ PHPMailer is installed" -ForegroundColor Green
        Write-Host "   📦 PHP dependencies are ready!" -ForegroundColor Green
    } else {
        Write-Host "⚠️ PHPMailer missing - will install dependencies" -ForegroundColor Yellow
        $needComposer = $true
    }
} else {
    Write-Host "❌ vendor/ folder is missing" -ForegroundColor Red
    Write-Host "   📥 Will install PHP dependencies..." -ForegroundColor Yellow
    $needComposer = $true
}
Write-Host ""

# Check node_modules folder
Write-Host "Checking CSS dependencies (node_modules folder)..." -ForegroundColor Cyan
if (Test-Path "node_modules") {
    Write-Host "✅ node_modules/ folder exists" -ForegroundColor Green
    
    # Check if TailwindCSS is there
    if (Test-Path "node_modules/tailwindcss") {
        Write-Host "✅ TailwindCSS is installed" -ForegroundColor Green
        Write-Host "   🎨 CSS dependencies are ready!" -ForegroundColor Green
    } else {
        Write-Host "⚠️ TailwindCSS missing - will install dependencies" -ForegroundColor Yellow
        $needNpm = $true
    }
} else {
    Write-Host "❌ node_modules/ folder is missing" -ForegroundColor Red
    Write-Host "   📥 Will install CSS dependencies..." -ForegroundColor Yellow
    $needNpm = $true
}
Write-Host ""

# Install Composer dependencies if needed
if ($needComposer) {
    Write-Host "Installing PHP dependencies with Composer..." -ForegroundColor Cyan
    Write-Host "============================================" -ForegroundColor Cyan
    
    # Check if composer is available
    try {
        $composerVersion = & composer --version 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Composer found: $composerVersion" -ForegroundColor Green
            Write-Host "⏳ Running: composer install..." -ForegroundColor Yellow
            & composer install
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Composer install completed successfully!" -ForegroundColor Green
            } else {
                Write-Host "❌ Composer install failed!" -ForegroundColor Red
            }
        }
    } catch {
        Write-Host "❌ Composer not found in PATH!" -ForegroundColor Red
        Write-Host "📥 Please install Composer first:" -ForegroundColor Yellow
        Write-Host "   1. Go to: https://getcomposer.org/download/" -ForegroundColor White
        Write-Host "   2. Download and install Composer for Windows" -ForegroundColor White
        Write-Host "   3. Restart this script" -ForegroundColor White
    }
    Write-Host ""
} else {
    Write-Host "✅ PHP dependencies are already installed - skipping Composer" -ForegroundColor Green
    Write-Host ""
}

# Install NPM dependencies if needed  
if ($needNpm) {
    Write-Host "Installing CSS dependencies with NPM..." -ForegroundColor Cyan
    Write-Host "======================================" -ForegroundColor Cyan
    
    # Check if npm is available
    try {
        $npmVersion = & npm --version 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ NPM found: $npmVersion" -ForegroundColor Green
            Write-Host "⏳ Running: npm install..." -ForegroundColor Yellow
            & npm install
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ NPM install completed successfully!" -ForegroundColor Green
                
                # Build CSS
                Write-Host "⏳ Building CSS: npm run build:css..." -ForegroundColor Yellow
                & npm run build:css
                if ($LASTEXITCODE -eq 0) {
                    Write-Host "✅ CSS build completed successfully!" -ForegroundColor Green
                } else {
                    Write-Host "⚠️ CSS build had issues - but dependencies are installed" -ForegroundColor Yellow
                }
            } else {
                Write-Host "❌ NPM install failed!" -ForegroundColor Red
            }
        }
    } catch {
        Write-Host "❌ NPM not found in PATH!" -ForegroundColor Red
        Write-Host "📥 Please install Node.js first:" -ForegroundColor Yellow
        Write-Host "   1. Go to: https://nodejs.org/" -ForegroundColor White
        Write-Host "   2. Download and install Node.js LTS for Windows" -ForegroundColor White
        Write-Host "   3. Restart this script" -ForegroundColor White
    }
    Write-Host ""
} else {
    Write-Host "✅ CSS dependencies are already installed - skipping NPM" -ForegroundColor Green
    Write-Host ""
}

# Final status check
Write-Host "Final Status Check" -ForegroundColor Green
Write-Host "==================" -ForegroundColor Green

$allGood = $true

if (Test-Path "vendor/phpmailer") {
    Write-Host "✅ PHP Dependencies: READY" -ForegroundColor Green
} else {
    Write-Host "❌ PHP Dependencies: MISSING" -ForegroundColor Red
    $allGood = $false
}

if (Test-Path "node_modules/tailwindcss") {
    Write-Host "✅ CSS Dependencies: READY" -ForegroundColor Green  
} else {
    Write-Host "❌ CSS Dependencies: MISSING" -ForegroundColor Red
    $allGood = $false
}

if (Test-Path "css/main.css") {
    Write-Host "✅ CSS Build: READY" -ForegroundColor Green
} else {
    Write-Host "⚠️ CSS Build: May need to run 'npm run build:css'" -ForegroundColor Yellow
}

Write-Host ""
if ($allGood) {
    Write-Host "🎉 ALL DEPENDENCIES ARE READY!" -ForegroundColor Green
    Write-Host "✅ Your FarmScout Online should work perfectly!" -ForegroundColor Green
    Write-Host "🌐 Test at: http://localhost/farmscout_online/" -ForegroundColor Cyan
} else {
    Write-Host "⚠️ Some dependencies are still missing" -ForegroundColor Yellow
    Write-Host "📋 Please install missing software and run this script again" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")