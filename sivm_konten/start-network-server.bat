@echo off
title Content Marketing System - Network Server
color 0A

echo ================================================================
echo   Content Marketing Management System
echo   Network Access Server Launcher
echo ================================================================
echo.

REM Get current directory
cd /d "%~dp0"

REM Check if we're in the right directory
if not exist "artisan" (
    echo Error: artisan file not found. Make sure you're in the Laravel project directory.
    echo Current directory: %cd%
    pause
    exit /b 1
)

REM Display network information
echo Getting network information...
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr "IPv4"') do (
    set "ip=%%i"
    setlocal enabledelayedexpansion
    set "ip=!ip:~1!"
    echo Local IP Address: !ip!
    echo.
)

echo Starting Laravel development server for network access...
echo.
echo Server will be accessible from other devices at:
echo   http://!ip!:8000
echo.
echo Content Marketing Dashboard:
echo   http://!ip!:8000/konten-marketing
echo.
echo Social Media Integration:
echo   http://!ip!:8000/social-integration
echo.
echo ================================================================
echo Press Ctrl+C to stop the server
echo ================================================================
echo.

REM Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000

pause