@echo off
REM Blood Donation System - Stop Script for Windows

echo 🛑 Stopping Blood Donation System...

REM Stop and remove containers
docker-compose down

REM Optional: Remove volumes (uncomment if you want to reset database)
REM docker-compose down -v

echo ✅ All services stopped successfully!
echo.
echo 🔄 To start again: start.bat
echo 🗑️  To reset database: docker-compose down -v ^&^& start.bat

pause
