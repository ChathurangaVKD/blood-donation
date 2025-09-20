# Blood Donation System - Local Development Setup Script for Windows PowerShell

Write-Host "🩸 Blood Donation System - Docker Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Check if Docker is installed
try {
    $dockerVersion = docker --version 2>$null
    if ($LASTEXITCODE -ne 0) { throw }
    Write-Host "✅ Docker found: $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker is not installed. Please install Docker first." -ForegroundColor Red
    Write-Host "Visit: https://docs.docker.com/get-docker/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if Docker Compose is installed
try {
    $composeVersion = docker-compose --version 2>$null
    if ($LASTEXITCODE -ne 0) { throw }
    Write-Host "✅ Docker Compose found: $composeVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker Compose is not installed. Please install Docker Compose first." -ForegroundColor Red
    Write-Host "Visit: https://docs.docker.com/compose/install/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Stop any existing containers
Write-Host "🛑 Stopping any existing containers..." -ForegroundColor Yellow
docker-compose down

# Build and start services
Write-Host "🏗️  Building and starting services..." -ForegroundColor Yellow
docker-compose up -d --build

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Check if services are running
Write-Host "🔍 Checking service status..." -ForegroundColor Yellow
$servicesStatus = docker-compose ps
if ($servicesStatus -match "Up") {
    Write-Host "✅ Services are running successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Application URLs:" -ForegroundColor Cyan
    Write-Host "   - Web Application: http://localhost:8080" -ForegroundColor White
    Write-Host "   - phpMyAdmin: http://localhost:8081" -ForegroundColor White
    Write-Host ""
    Write-Host "🗄️  Database Connection:" -ForegroundColor Cyan
    Write-Host "   - Host: localhost" -ForegroundColor White
    Write-Host "   - Port: 3306" -ForegroundColor White
    Write-Host "   - Database: blood_donation" -ForegroundColor White
    Write-Host "   - Username: blooddonation" -ForegroundColor White
    Write-Host "   - Password: blooddonation123" -ForegroundColor White
    Write-Host ""
    Write-Host "👤 Default Login Credentials:" -ForegroundColor Cyan
    Write-Host "   - Email: john.doe@email.com" -ForegroundColor White
    Write-Host "   - Password: password123" -ForegroundColor White
    Write-Host ""
    Write-Host "🔧 Admin Panel:" -ForegroundColor Cyan
    Write-Host "   - Username: admin" -ForegroundColor White
    Write-Host "   - Password: admin123" -ForegroundColor White
    Write-Host ""
    Write-Host "📝 To view logs: docker-compose logs -f" -ForegroundColor Yellow
    Write-Host "🛑 To stop: docker-compose down" -ForegroundColor Yellow
} else {
    Write-Host "❌ Some services failed to start. Check logs with: docker-compose logs" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Read-Host "Press Enter to continue"
