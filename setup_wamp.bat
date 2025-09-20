@echo off
REM Blood Donation System - WAMP Setup Script for Windows
REM This script sets up the application to run on WAMP

echo 🩸 Blood Donation System - WAMP Setup
echo =======================================

REM Check common WAMP installation paths
set WAMP_PATHS=C:\wamp64 C:\wamp C:\WampServer

set WAMP_PATH=
for %%p in (%WAMP_PATHS%) do (
    if exist "%%p" (
        set WAMP_PATH=%%p
        goto :found_wamp
    )
)

echo ❌ WAMP not found in common locations:
echo    - C:\wamp64
echo    - C:\wamp
echo    - C:\WampServer
echo.
echo Please install WAMP from: http://www.wampserver.com/
echo Or update the path in this script if installed elsewhere
pause
exit /b 1

:found_wamp
echo ✅ WAMP installation found at: %WAMP_PATH%

REM Set application directory
set WWW_PATH=%WAMP_PATH%\www\BloodDonationSystem

REM Create application directory
echo 📁 Creating application directory...
if not exist "%WWW_PATH%" mkdir "%WWW_PATH%"

REM Copy application files
echo 📋 Copying application files...
xcopy /E /I /Y "frontend" "%WWW_PATH%\frontend"
xcopy /E /I /Y "backend" "%WWW_PATH%\backend"
xcopy /E /I /Y "database" "%WWW_PATH%\database"

REM Update configuration files
echo ⚙️  Updating configuration files...

REM Create backend config for WAMP
echo ^<?php > "%WWW_PATH%\backend\config_wamp.php"
echo // WAMP Database configuration >> "%WWW_PATH%\backend\config_wamp.php"
echo define('DB_HOST', 'localhost'); >> "%WWW_PATH%\backend\config_wamp.php"
echo define('DB_NAME', 'blood_donation'); >> "%WWW_PATH%\backend\config_wamp.php"
echo define('DB_USER', 'root'); >> "%WWW_PATH%\backend\config_wamp.php"
echo define('DB_PASS', ''); >> "%WWW_PATH%\backend\config_wamp.php"
echo define('DB_PORT', '3306'); >> "%WWW_PATH%\backend\config_wamp.php"
echo ?^> >> "%WWW_PATH%\backend\config_wamp.php"

copy "%WWW_PATH%\backend\config_wamp.php" "%WWW_PATH%\backend\config.php"

REM Create frontend config for WAMP
echo const CONFIG = { > "%WWW_PATH%\frontend\config_wamp.js"
echo     API_BASE_URL: 'http://localhost/BloodDonationSystem/backend', >> "%WWW_PATH%\frontend\config_wamp.js"
echo     ENDPOINTS: { >> "%WWW_PATH%\frontend\config_wamp.js"
echo         REGISTER: '/register.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         LOGIN: '/login.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         REQUEST: '/request.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         SEARCH: '/search.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         INVENTORY: '/inventory.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         DONATIONS: '/donations.php', >> "%WWW_PATH%\frontend\config_wamp.js"
echo         ADMIN: '/admin.php' >> "%WWW_PATH%\frontend\config_wamp.js"
echo     } >> "%WWW_PATH%\frontend\config_wamp.js"
echo }; >> "%WWW_PATH%\frontend\config_wamp.js"

copy "%WWW_PATH%\frontend\config_wamp.js" "%WWW_PATH%\frontend\config.js"

REM Setup database
echo 🗄️  Setting up database...
echo Please ensure WAMP MySQL service is running before proceeding...
echo You can start it from WAMP System Tray icon
pause

REM Use WAMP MySQL
set MYSQL_PATH=%WAMP_PATH%\bin\mysql\mysql8.0.31\bin\mysql.exe
if not exist "%MYSQL_PATH%" (
    set MYSQL_PATH=%WAMP_PATH%\bin\mysql\mysql5.7.36\bin\mysql.exe
)
if not exist "%MYSQL_PATH%" (
    set MYSQL_PATH=mysql
)

REM Create database and user
echo Creating database and importing schema...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS blood_donation;"
"%MYSQL_PATH%" -u root -e "CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';"
"%MYSQL_PATH%" -u root -e "GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';"
"%MYSQL_PATH%" -u root -e "FLUSH PRIVILEGES;"

REM Import database files
if exist "%WWW_PATH%\database\schema.sql" (
    echo Importing database schema...
    "%MYSQL_PATH%" -u root blood_donation < "%WWW_PATH%\database\schema.sql"
)

if exist "%WWW_PATH%\database\sample_data.sql" (
    echo Importing sample data...
    "%MYSQL_PATH%" -u root blood_donation < "%WWW_PATH%\database\sample_data.sql"
)

REM Create virtual host (optional)
echo 📝 Creating virtual host configuration...
set VHOST_FILE=%WAMP_PATH%\bin\apache\apache2.4.54\conf\extra\httpd-vhosts.conf
if not exist "%VHOST_FILE%" (
    set VHOST_FILE=%WAMP_PATH%\bin\apache\apache2.4.51\conf\extra\httpd-vhosts.conf
)

if exist "%VHOST_FILE%" (
    echo. >> "%VHOST_FILE%"
    echo # Blood Donation System Virtual Host >> "%VHOST_FILE%"
    echo ^<VirtualHost *:80^> >> "%VHOST_FILE%"
    echo     ServerName blooddonation.local >> "%VHOST_FILE%"
    echo     DocumentRoot "%WWW_PATH%" >> "%VHOST_FILE%"
    echo     ^<Directory "%WWW_PATH%"^> >> "%VHOST_FILE%"
    echo         AllowOverride All >> "%VHOST_FILE%"
    echo         Require all granted >> "%VHOST_FILE%"
    echo     ^</Directory^> >> "%VHOST_FILE%"
    echo ^</VirtualHost^> >> "%VHOST_FILE%"

    echo 🌐 Virtual host created! Add this to your hosts file:
    echo    127.0.0.1 blooddonation.local
    echo    File location: C:\Windows\System32\drivers\etc\hosts
)

echo ✅ Setup completed successfully!
echo.
echo 🌐 Application URLs:
echo    - Web Application: http://localhost/BloodDonationSystem/frontend/
echo    - Virtual Host: http://blooddonation.local/frontend/ (if hosts file updated)
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
echo 📝 Make sure WAMP services are running (green icon in system tray)!
pause
