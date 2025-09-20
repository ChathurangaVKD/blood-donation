# Blood Donation System - Stop Script for Windows PowerShell

Write-Host "🛑 Stopping Blood Donation System..." -ForegroundColor Yellow

# Stop and remove containers
docker-compose down

# Optional: Remove volumes (uncomment if you want to reset database)
# docker-compose down -v

Write-Host "✅ All services stopped successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "🔄 To start again: .\start.ps1" -ForegroundColor Cyan
Write-Host "🗑️  To reset database: docker-compose down -v; .\start.ps1" -ForegroundColor Cyan

Read-Host "Press Enter to continue"
