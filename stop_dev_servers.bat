@echo off
REM Stop development servers script for Windows

echo 🛑 Stopping Blood Donation System development servers...

REM Kill servers by window title
taskkill /F /FI "WINDOWTITLE eq Frontend Server*" >nul 2>&1
taskkill /F /FI "WINDOWTITLE eq Backend Server*" >nul 2>&1

REM Kill any remaining PHP servers on our ports
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8080') do taskkill /f /pid %%a >nul 2>&1
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8081') do taskkill /f /pid %%a >nul 2>&1

echo ✅ Development servers stopped!
pause
