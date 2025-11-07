# Setup Network Access untuk Content Marketing System
# Run as Administrator

Write-Host "=== Content Marketing System - Network Access Setup ===" -ForegroundColor Green
Write-Host ""

# Get current IP Address
$ipAddress = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi" | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or $_.IPAddress -like "172.*"}).IPAddress
if (-not $ipAddress) {
    $ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or $_.IPAddress -like "172.*"} | Select-Object -First 1).IPAddress
}

Write-Host "Detected IP Address: $ipAddress" -ForegroundColor Yellow
Write-Host ""

# Check if running as Administrator
$currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
$isAdmin = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "Warning: Not running as Administrator. Some firewall rules may not be applied." -ForegroundColor Red
    Write-Host "To run as Administrator: Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Write-Host ""
}

# Function to add firewall rule
function Add-FirewallRule {
    param($ruleName, $port)
    
    try {
        # Check if rule already exists
        $existingRule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
        if ($existingRule) {
            Write-Host "Firewall rule '$ruleName' already exists" -ForegroundColor Yellow
        } else {
            New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -Protocol TCP -LocalPort $port -Action Allow -ErrorAction Stop
            Write-Host "✓ Added firewall rule for port $port" -ForegroundColor Green
        }
    } catch {
        Write-Host "✗ Failed to add firewall rule for port $port" -ForegroundColor Red
        Write-Host "  Error: $_" -ForegroundColor Red
    }
}

# Add firewall rules (requires Administrator)
if ($isAdmin) {
    Write-Host "Adding Firewall Rules..." -ForegroundColor Cyan
    Add-FirewallRule "Laravel Development Server" 8000
    Add-FirewallRule "Apache HTTP Server" 80
    Write-Host ""
} else {
    Write-Host "Skipping firewall rules (requires Administrator access)" -ForegroundColor Yellow
    Write-Host ""
}

# Update .env file
Write-Host "Updating .env configuration..." -ForegroundColor Cyan
$envPath = "C:\laragon\www\sivm_konten\.env"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath
    $newEnvContent = @()
    
    foreach ($line in $envContent) {
        if ($line -like "APP_URL=*") {
            $newEnvContent += "APP_URL=http://$ipAddress:8000"
            Write-Host "✓ Updated APP_URL to http://$ipAddress:8000" -ForegroundColor Green
        } else {
            $newEnvContent += $line
        }
    }
    
    $newEnvContent | Set-Content $envPath
    Write-Host ""
} else {
    Write-Host "✗ .env file not found at $envPath" -ForegroundColor Red
    Write-Host ""
}

# Test network connectivity
Write-Host "Testing Network Connectivity..." -ForegroundColor Cyan
try {
    $testResult = Test-NetConnection -ComputerName $ipAddress -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue
    if ($testResult) {
        Write-Host "✓ Port 8000 is accessible on $ipAddress" -ForegroundColor Green
    } else {
        Write-Host "✗ Port 8000 is not accessible on $ipAddress" -ForegroundColor Red
        Write-Host "  Make sure Laravel server is running: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
    }
} catch {
    Write-Host "✗ Network test failed: $_" -ForegroundColor Red
}
Write-Host ""

# Display access information
Write-Host "=== Access Information ===" -ForegroundColor Green
Write-Host "Your application can be accessed from other devices using:" -ForegroundColor White
Write-Host ""
Write-Host "Main Application:" -ForegroundColor Cyan
Write-Host "  http://$ipAddress:8000" -ForegroundColor White
Write-Host ""
Write-Host "Content Marketing Dashboard:" -ForegroundColor Cyan  
Write-Host "  http://$ipAddress:8000/konten-marketing" -ForegroundColor White
Write-Host ""
Write-Host "Content Calendar:" -ForegroundColor Cyan
Write-Host "  http://$ipAddress:8000/content-calendar" -ForegroundColor White
Write-Host ""
Write-Host "Social Media Integration:" -ForegroundColor Cyan
Write-Host "  http://$ipAddress:8000/social-integration" -ForegroundColor White
Write-Host ""

# Instructions for starting server
Write-Host "=== Server Commands ===" -ForegroundColor Green
Write-Host "To start the Laravel server for network access:" -ForegroundColor White
Write-Host "  cd C:\laragon\www\sivm_konten" -ForegroundColor Yellow
Write-Host "  php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
Write-Host ""

# Mobile access instructions
Write-Host "=== Mobile Access ===" -ForegroundColor Green
Write-Host "1. Ensure your mobile device is connected to the same WiFi network" -ForegroundColor White
Write-Host "2. Open browser on mobile device" -ForegroundColor White
Write-Host "3. Navigate to: http://$ipAddress:8000" -ForegroundColor White
Write-Host ""

# QR Code suggestion
Write-Host "=== Quick Access ===" -ForegroundColor Green
Write-Host "For easy mobile access, you can create a QR code for:" -ForegroundColor White
Write-Host "http://$ipAddress:8000" -ForegroundColor Yellow
Write-Host ""

# Troubleshooting
Write-Host "=== Troubleshooting ===" -ForegroundColor Green
Write-Host "If you can't access from other devices:" -ForegroundColor White
Write-Host "1. Check Windows Firewall settings" -ForegroundColor White
Write-Host "2. Verify all devices are on the same network" -ForegroundColor White
Write-Host "3. Try disabling antivirus temporarily" -ForegroundColor White
Write-Host "4. Restart router if necessary" -ForegroundColor White
Write-Host ""

Write-Host "Setup completed! Press any key to continue..." -ForegroundColor Green
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")