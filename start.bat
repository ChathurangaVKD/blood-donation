@echo off
REM BloodLink - Blood Donation System Startup Script for Windows
REM Author: Blood Donation System Team
REM Date: September 21, 2025

echo.
echo 🩸 BloodLink - Blood Donation Management System
echo ==============================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP is not installed. Please install PHP 7.4 or higher.
    echo.
    echo 📥 Download PHP from: https://windows.php.net/download/
    pause
    exit /b 1
)

REM Get PHP version
for /f "tokens=2 delims= " %%i in ('php --version ^| findstr /R "^PHP"') do (
    set PHP_VERSION=%%i
    goto :found_version
)
:found_version

echo ✅ PHP Version: %PHP_VERSION%

REM Check if MySQL is available (optional check)
mysql --version >nul 2>&1
if errorlevel 1 (
    echo ⚠️  MySQL command not found. Make sure MySQL server is running.
) else (
    echo ✅ MySQL is available
)

echo.
echo 🚀 Starting BloodLink Development Server...
echo 📍 Server will be available at: http://localhost:8080
echo.
echo 🌐 Available Pages:
echo    • Main Page:    http://localhost:8080/frontend/index.html
echo    • Search:       http://localhost:8080/frontend/search.html
echo    • Admin Panel:  http://localhost:8080/frontend/admin.html
echo    • Login:        http://localhost:8080/frontend/login.html
echo.
echo 👤 Default Admin Login:
echo    • Username: admin
echo    • Password: admin123
echo.
echo 🛑 Press Ctrl+C to stop the server
echo ==============================================
echo.

REM Start PHP built-in server
php -S localhost:8080 -t .
