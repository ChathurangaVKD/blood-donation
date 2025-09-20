@echo off
REM Blood Donation System - XAMPP Setup Script for Windows
REM This script sets up the application to run on XAMPP

echo 🩸 Blood Donation System - XAMPP Setup
echo ==========================================

REM Check if XAMPP is installed
if not exist "C:\xampp" (
    echo ❌ XAMPP not found in C:\xampp
    echo Please install XAMPP from: https://www.apachefriends.org/
    echo Or update the path in this script if installed elsewhere
    pause
    exit /b 1
)

echo ✅ XAMPP installation found

REM Check if Apache and MySQL are running
echo 🔍 Checking XAMPP services...

REM Create application directory in htdocs
set HTDOCS_PATH=C:\xampp\htdocs\BloodDonationSystem
if not exist "%HTDOCS_PATH%" (
    echo 📁 Creating application directory...
    mkdir "%HTDOCS_PATH%"
)

REM Copy application files
echo 📋 Copying application files...
xcopy /E /I /Y "frontend" "%HTDOCS_PATH%\frontend"
xcopy /E /I /Y "backend" "%HTDOCS_PATH%\backend"
xcopy /E /I /Y "database" "%HTDOCS_PATH%\database"

REM Update configuration files
echo ⚙️  Updating configuration files...

REM Create backend config for XAMPP
echo ^<?php > "%HTDOCS_PATH%\backend\config_xampp.php"
echo // XAMPP Database configuration >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo define('DB_HOST', 'localhost'); >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo define('DB_NAME', 'blood_donation'); >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo define('DB_USER', 'root'); >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo define('DB_PASS', ''); >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo define('DB_PORT', '3306'); >> "%HTDOCS_PATH%\backend\config_xampp.php"
echo ?^> >> "%HTDOCS_PATH%\backend\config_xampp.php"

REM Update main config to use XAMPP settings
copy "%HTDOCS_PATH%\backend\config_xampp.php" "%HTDOCS_PATH%\backend\config.php"

REM Create frontend config for XAMPP
echo const CONFIG = { > "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     API_BASE_URL: 'http://localhost/BloodDonationSystem/backend', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     ENDPOINTS: { >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         REGISTER: '/register.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         LOGIN: '/login.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         REQUEST: '/request.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         SEARCH: '/search.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         INVENTORY: '/inventory.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         DONATIONS: '/donations.php', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo         ADMIN: '/admin.php' >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     }, >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'], >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     APP_NAME: 'Blood Donation System', >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo     VERSION: '1.0.0' >> "%HTDOCS_PATH%\frontend\config_xampp.js"
echo }; >> "%HTDOCS_PATH%\frontend\config_xampp.js"

copy "%HTDOCS_PATH%\frontend\config_xampp.js" "%HTDOCS_PATH%\frontend\config.js"

REM Setup database
echo 🗄️  Setting up database...
echo Please ensure XAMPP MySQL service is running before proceeding...
pause

REM Check if MySQL is accessible
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  MySQL command not found in PATH
    echo Using XAMPP MySQL directly...
    set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
) else (
    set MYSQL_PATH=mysql
)

REM Create database and user
echo Creating database and importing schema...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS blood_donation;"
"%MYSQL_PATH%" -u root -e "CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';"
"%MYSQL_PATH%" -u root -e "GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';"
"%MYSQL_PATH%" -u root -e "FLUSH PRIVILEGES;"

REM Import database schema
if exist "%HTDOCS_PATH%\database\schema.sql" (
    echo Importing database schema...
    "%MYSQL_PATH%" -u root blood_donation < "%HTDOCS_PATH%\database\schema.sql"
)

if exist "%HTDOCS_PATH%\database\sample_data.sql" (
    echo Importing sample data...
    "%MYSQL_PATH%" -u root blood_donation < "%HTDOCS_PATH%\database\sample_data.sql"
)

REM Create .htaccess for better URL handling
echo RewriteEngine On > "%HTDOCS_PATH%\.htaccess"
echo RewriteCond %%{REQUEST_FILENAME} !-f >> "%HTDOCS_PATH%\.htaccess"
echo RewriteCond %%{REQUEST_FILENAME} !-d >> "%HTDOCS_PATH%\.htaccess"
echo RewriteRule ^(.*)$ frontend/$1 [L] >> "%HTDOCS_PATH%\.htaccess"

echo ✅ Setup completed successfully!
echo.
echo 🌐 Application URLs:
echo    - Web Application: http://localhost/BloodDonationSystem/frontend/
echo    - Admin Panel: http://localhost/BloodDonationSystem/backend/admin.php
echo    - phpMyAdmin: http://localhost/phpmyadmin/
echo.
echo 🗄️  Database Information:
echo    - Host: localhost
echo    - Database: blood_donation
echo    - Username: blooddonation
echo    - Password: blooddonation123
echo.
echo 👤 Default Login:
echo    - Email: john.doe@email.com
echo    - Password: password123
echo.
echo 📝 Make sure XAMPP Apache and MySQL services are running!
echo 🔧 Access XAMPP Control Panel to start/stop services
echo.
pause
