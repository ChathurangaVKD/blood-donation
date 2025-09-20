@echo off
REM start.bat - Simple startup script for Blood Donation System using PHP Built-in Server

echo.
echo 🩸 Blood Donation System - PHP Built-in Server
echo ==============================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    pause
    exit /b 1
)

echo ✅ PHP is ready

REM Setup database with comprehensive sample data
echo 🚀 Setting up database with comprehensive sample data...
cd database
php reset_and_populate.php
if %errorlevel% neq 0 (
    echo ❌ Database setup failed. Please check MySQL connection.
    pause
    exit /b 1
)
cd ..

echo.
echo 🔧 Starting backend server on http://localhost:8081...
cd backend
start "Backend Server" /min cmd /c "php -S localhost:8081"
cd ..

echo 🌐 Starting frontend server on http://localhost:8080...
cd frontend
start "Frontend Server" /min cmd /c "php -S localhost:8080"
cd ..

echo ⏳ Waiting for servers to start...
timeout /t 3 /nobreak >nul

echo 🚀 Opening frontend in browser...
start "" "http://localhost:8080"

echo.
echo ✅ Blood Donation System is running!
echo    📱 Frontend: http://localhost:8080
echo    🔧 Backend API: http://localhost:8081
echo    📊 Database: Populated with 29 donors, 47 inventory units
echo.
echo 🔐 Admin Login: admin / admin123
echo.
echo 📝 Both frontend and backend now run on PHP Built-in Servers
echo.
echo Press any key to stop all servers...
pause >nul

echo.
echo 🛑 Stopping all servers...
taskkill /f /im php.exe >nul 2>&1
echo All servers stopped. Goodbye!
