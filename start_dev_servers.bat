@echo off
REM Blood Donation System - PHP Built-in Server (Development Mode) for Windows
REM Quick setup for development and testing

echo 🩸 Blood Donation System - Development Server
echo ==============================================

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed
    echo Please install PHP 7.4+ first
    pause
    exit /b 1
)

echo ✅ PHP found

REM Check if MySQL is available
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  MySQL not found - you'll need to set up the database manually
)

REM Create development configuration
echo ⚙️  Setting up development configuration...

REM Backend config for development
echo ^<?php > "backend\config_dev.php"
echo // Development configuration >> "backend\config_dev.php"
echo define('DB_HOST', 'localhost'); >> "backend\config_dev.php"
echo define('DB_NAME', 'blood_donation'); >> "backend\config_dev.php"
echo define('DB_USER', 'root'); >> "backend\config_dev.php"
echo define('DB_PASS', ''); >> "backend\config_dev.php"
echo define('DB_PORT', '3306'); >> "backend\config_dev.php"
echo. >> "backend\config_dev.php"
echo // Development settings >> "backend\config_dev.php"
echo define('APP_ENV', 'development'); >> "backend\config_dev.php"
echo define('DEBUG_MODE', true); >> "backend\config_dev.php"
echo define('LOG_ERRORS', true); >> "backend\config_dev.php"
echo. >> "backend\config_dev.php"
echo // Error reporting for development >> "backend\config_dev.php"
echo error_reporting(E_ALL); >> "backend\config_dev.php"
echo ini_set('display_errors', 1); >> "backend\config_dev.php"
echo ?^> >> "backend\config_dev.php"

REM Frontend config for development
echo const CONFIG = { > "frontend\config_dev.js"
echo     API_BASE_URL: 'http://localhost:8081', >> "frontend\config_dev.js"
echo     ENDPOINTS: { >> "frontend\config_dev.js"
echo         REGISTER: '/register.php', >> "frontend\config_dev.js"
echo         LOGIN: '/login.php', >> "frontend\config_dev.js"
echo         REQUEST: '/request.php', >> "frontend\config_dev.js"
echo         SEARCH: '/search.php', >> "frontend\config_dev.js"
echo         INVENTORY: '/inventory.php', >> "frontend\config_dev.js"
echo         DONATIONS: '/donations.php', >> "frontend\config_dev.js"
echo         ADMIN: '/admin.php' >> "frontend\config_dev.js"
echo     }, >> "frontend\config_dev.js"
echo     BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], >> "frontend\config_dev.js"
echo     URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'], >> "frontend\config_dev.js"
echo     APP_NAME: 'Blood Donation System (Dev)', >> "frontend\config_dev.js"
echo     VERSION: '1.0.0-dev', >> "frontend\config_dev.js"
echo     DEBUG: true >> "frontend\config_dev.js"
echo }; >> "frontend\config_dev.js"

REM Use development configs
copy "backend\config_dev.php" "backend\config.php" >nul
copy "frontend\config_dev.js" "frontend\config.js" >nul

REM Setup database (if MySQL is available)
mysql --version >nul 2>&1
if %errorlevel% equ 0 (
    echo 🗄️  Setting up development database...
    set /p MYSQL_PASS="Enter MySQL root password (press Enter if no password): "

    if "%MYSQL_PASS%"=="" (
        set MYSQL_CMD=mysql -u root
    ) else (
        set MYSQL_CMD=mysql -u root -p%MYSQL_PASS%
    )

    REM Create database
    %MYSQL_CMD% -e "CREATE DATABASE IF NOT EXISTS blood_donation;" 2>nul

    REM Import schema if available
    if exist "database\schema.sql" (
        echo 📥 Importing database schema...
        %MYSQL_CMD% blood_donation < "database\schema.sql" 2>nul
    )

    if exist "database\sample_data.sql" (
        echo 📥 Importing sample data...
        %MYSQL_CMD% blood_donation < "database\sample_data.sql" 2>nul
    )
)

REM Start servers
echo 🚀 Starting development servers...

REM Kill any existing servers on these ports
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8080') do taskkill /f /pid %%a >nul 2>&1
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8081') do taskkill /f /pid %%a >nul 2>&1

REM Start frontend server
echo Starting frontend server on http://localhost:8080...
start "Frontend Server" /min cmd /c "cd frontend && php -S localhost:8080"

REM Start backend server
echo Starting backend server on http://localhost:8081...
start "Backend Server" /min cmd /c "cd backend && php -S localhost:8081"

REM Wait a moment for servers to start
timeout /t 3 /nobreak >nul

echo ✅ Development servers started!
echo.
echo 🌐 Application URLs:
echo    - Frontend: http://localhost:8080
echo    - Backend API: http://localhost:8081
echo    - Admin Panel: http://localhost:8081/admin.php
echo.
echo 🗄️  Database: blood_donation (localhost:3306)
echo 👤 Default Login: john.doe@email.com / password123
echo.
echo 📝 Development Features:
echo    - Debug mode enabled
echo    - Error reporting on
echo    - CORS headers for API testing
echo.
echo 🛑 To stop servers: stop_dev_servers.bat
echo 📊 Check server windows for logs
echo.
pause
