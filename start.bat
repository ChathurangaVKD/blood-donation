@echo off
REM Blood Donation System - Local Development Setup Script for Windows

echo 🩸 Blood Donation System - Docker Setup
echo ========================================

REM Check if Docker is installed
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not installed. Please install Docker first.
    echo Visit: https://docs.docker.com/get-docker/
    pause
    exit /b 1
)

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose is not installed. Please install Docker Compose first.
    echo Visit: https://docs.docker.com/compose/install/
    pause
    exit /b 1
)

echo ✅ Docker and Docker Compose are installed

REM Stop any existing containers
echo 🛑 Stopping any existing containers...
docker-compose down

REM Build and start services
echo 🏗️  Building and starting services...
docker-compose up -d --build

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 30 /nobreak >nul

REM Check if services are running
echo 🔍 Checking service status...
docker-compose ps | findstr "Up" >nul
if %errorlevel% equ 0 (
    echo ✅ Services are running successfully!
    echo.
    echo 🌐 Application URLs:
    echo    - Web Application: http://localhost:8080
    echo    - phpMyAdmin: http://localhost:8081
    echo.
    echo 🗄️  Database Connection:
    echo    - Host: localhost
    echo    - Port: 3306
    echo    - Database: blood_donation
    echo    - Username: blooddonation
    echo    - Password: blooddonation123
    echo.
    echo 👤 Default Login Credentials:
    echo    - Email: john.doe@email.com
    echo    - Password: password123
    echo.
    echo 🔧 Admin Panel:
    echo    - Username: admin
    echo    - Password: admin123
    echo.
    echo 📝 To view logs: docker-compose logs -f
    echo 🛑 To stop: docker-compose down
) else (
    echo ❌ Some services failed to start. Check logs with: docker-compose logs
    pause
    exit /b 1
)

pause
