@echo off
REM Blood Donation System - Native Setup Script for Windows
REM This script sets up the application with native PHP and MySQL

echo 🩸 Blood Donation System - Native Setup
echo ========================================

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed
    echo Please download and install PHP from: https://www.php.net/downloads
    echo Make sure to enable php_mysqli extension
    pause
    exit /b 1
)

echo ✅ PHP found

REM Check if MySQL is installed
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ MySQL is not installed
    echo Please download and install MySQL from: https://dev.mysql.com/downloads/mysql/
    pause
    exit /b 1
)

echo ✅ MySQL found

REM Create project directory
set PROJECT_DIR=%USERPROFILE%\BloodDonationSystem
echo 📁 Setting up project in: %PROJECT_DIR%
if not exist "%PROJECT_DIR%" mkdir "%PROJECT_DIR%"

REM Copy application files
echo 📋 Copying application files...
xcopy /E /I /Y "frontend" "%PROJECT_DIR%\frontend"
xcopy /E /I /Y "backend" "%PROJECT_DIR%\backend"
xcopy /E /I /Y "database" "%PROJECT_DIR%\database"

REM Create native configuration
echo ⚙️  Creating configuration files...

REM Backend config
echo ^<?php > "%PROJECT_DIR%\backend\config.php"
echo // Native MySQL configuration >> "%PROJECT_DIR%\backend\config.php"
echo define('DB_HOST', 'localhost'); >> "%PROJECT_DIR%\backend\config.php"
echo define('DB_NAME', 'blood_donation'); >> "%PROJECT_DIR%\backend\config.php"
echo define('DB_USER', 'blooddonation'); >> "%PROJECT_DIR%\backend\config.php"
echo define('DB_PASS', 'blooddonation123'); >> "%PROJECT_DIR%\backend\config.php"
echo define('DB_PORT', '3306'); >> "%PROJECT_DIR%\backend\config.php"
echo. >> "%PROJECT_DIR%\backend\config.php"
echo // Application settings >> "%PROJECT_DIR%\backend\config.php"
echo define('APP_ENV', 'production'); >> "%PROJECT_DIR%\backend\config.php"
echo define('DEBUG_MODE', false); >> "%PROJECT_DIR%\backend\config.php"
echo define('LOG_ERRORS', true); >> "%PROJECT_DIR%\backend\config.php"
echo ?^> >> "%PROJECT_DIR%\backend\config.php"

REM Frontend config
echo const CONFIG = { > "%PROJECT_DIR%\frontend\config.js"
echo     API_BASE_URL: 'http://localhost:8081', >> "%PROJECT_DIR%\frontend\config.js"
echo     ENDPOINTS: { >> "%PROJECT_DIR%\frontend\config.js"
echo         REGISTER: '/register.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         LOGIN: '/login.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         REQUEST: '/request.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         SEARCH: '/search.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         INVENTORY: '/inventory.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         DONATIONS: '/donations.php', >> "%PROJECT_DIR%\frontend\config.js"
echo         ADMIN: '/admin.php' >> "%PROJECT_DIR%\frontend\config.js"
echo     }, >> "%PROJECT_DIR%\frontend\config.js"
echo     BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], >> "%PROJECT_DIR%\frontend\config.js"
echo     URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'], >> "%PROJECT_DIR%\frontend\config.js"
echo     APP_NAME: 'Blood Donation System', >> "%PROJECT_DIR%\frontend\config.js"
echo     VERSION: '1.0.0' >> "%PROJECT_DIR%\frontend\config.js"
echo }; >> "%PROJECT_DIR%\frontend\config.js"

REM Setup MySQL database
echo 🗄️  Setting up MySQL database...
echo Please enter MySQL root password when prompted...

REM Create database and user
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS blood_donation;"
mysql -u root -p -e "CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

REM Import database schema
if exist "%PROJECT_DIR%\database\schema.sql" (
    echo 📥 Importing database schema...
    mysql -u blooddonation -pblooddonation123 blood_donation < "%PROJECT_DIR%\database\schema.sql"
)

if exist "%PROJECT_DIR%\database\sample_data.sql" (
    echo 📥 Importing sample data...
    mysql -u blooddonation -pblooddonation123 blood_donation < "%PROJECT_DIR%\database\sample_data.sql"
)

REM Create startup scripts
echo 📝 Creating startup scripts...

echo @echo off > "%PROJECT_DIR%\start_native.bat"
echo echo 🚀 Starting Blood Donation System with PHP built-in server... >> "%PROJECT_DIR%\start_native.bat"
echo. >> "%PROJECT_DIR%\start_native.bat"
echo REM Start MySQL service >> "%PROJECT_DIR%\start_native.bat"
echo net start MySQL80 ^>nul 2^>^&1 ^|^| net start MySQL ^>nul 2^>^&1 >> "%PROJECT_DIR%\start_native.bat"
echo. >> "%PROJECT_DIR%\start_native.bat"
echo echo Starting frontend server on port 8080... >> "%PROJECT_DIR%\start_native.bat"
echo start "Frontend Server" cmd /k "cd /d \"%PROJECT_DIR%\frontend\" && php -S localhost:8080" >> "%PROJECT_DIR%\start_native.bat"
echo. >> "%PROJECT_DIR%\start_native.bat"
echo echo Starting backend server on port 8081... >> "%PROJECT_DIR%\start_native.bat"
echo start "Backend Server" cmd /k "cd /d \"%PROJECT_DIR%\backend\" && php -S localhost:8081" >> "%PROJECT_DIR%\start_native.bat"
echo. >> "%PROJECT_DIR%\start_native.bat"
echo echo ✅ Servers started! >> "%PROJECT_DIR%\start_native.bat"
echo echo 🌐 Access your application at: >> "%PROJECT_DIR%\start_native.bat"
echo echo    - Frontend: http://localhost:8080 >> "%PROJECT_DIR%\start_native.bat"
echo echo    - Backend API: http://localhost:8081 >> "%PROJECT_DIR%\start_native.bat"
echo echo    - Admin Panel: http://localhost:8081/admin.php >> "%PROJECT_DIR%\start_native.bat"
echo pause >> "%PROJECT_DIR%\start_native.bat"

echo @echo off > "%PROJECT_DIR%\stop_native.bat"
echo echo 🛑 Stopping Blood Donation System servers... >> "%PROJECT_DIR%\stop_native.bat"
echo taskkill /F /FI "WINDOWTITLE eq Frontend Server*" ^>nul 2^>^&1 >> "%PROJECT_DIR%\stop_native.bat"
echo taskkill /F /FI "WINDOWTITLE eq Backend Server*" ^>nul 2^>^&1 >> "%PROJECT_DIR%\stop_native.bat"
echo echo ✅ Servers stopped! >> "%PROJECT_DIR%\stop_native.bat"
echo pause >> "%PROJECT_DIR%\stop_native.bat"

echo ✅ Setup completed successfully!
echo.
echo 🌐 Application Access:
echo    - Frontend: http://localhost:8080
echo    - Backend API: http://localhost:8081
echo    - Admin Panel: http://localhost:8081/admin.php
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
echo 🚀 Start the application:
echo    cd "%PROJECT_DIR%" && start_native.bat
echo.
echo 🛑 Stop the application:
echo    cd "%PROJECT_DIR%" && stop_native.bat
echo.
pause
