@echo off
REM Blood Donation System - Universal Launcher for Windows
REM Choose your preferred deployment method

:main
cls
echo.
echo 🩸 Blood Donation System - Deployment Options
echo =============================================
echo.
echo Choose your preferred setup method:
echo.
echo 1. Docker (Recommended - Isolated environment)
echo 2. XAMPP (Easy for beginners)
echo 3. WAMP (Windows users)
echo 4. Native PHP + MySQL (Advanced users)
echo 5. Development Server (Quick testing)
echo 6. Exit
echo.
set /p choice="Enter your choice (1-6): "

if "%choice%"=="1" goto docker
if "%choice%"=="2" goto xampp
if "%choice%"=="3" goto wamp
if "%choice%"=="4" goto native
if "%choice%"=="5" goto devserver
if "%choice%"=="6" goto exit
goto invalid

:docker
echo.
echo 🐳 Starting Docker deployment...
if exist "start.bat" (
    call start.bat
) else (
    echo ❌ Docker setup not found. Please ensure start.bat exists.
    pause
)
goto main

:xampp
echo.
echo 🔶 Starting XAMPP setup...
if exist "setup_xampp.bat" (
    call setup_xampp.bat
) else (
    echo ❌ XAMPP setup script not found.
    pause
)
goto main

:wamp
echo.
echo 🟡 Starting WAMP setup...
if exist "setup_wamp.bat" (
    call setup_wamp.bat
) else (
    echo ❌ WAMP setup script not found.
    pause
)
goto main

:native
echo.
echo 🔧 Starting native PHP + MySQL setup...
if exist "setup_native.bat" (
    call setup_native.bat
) else (
    echo ❌ Native setup script not found.
    pause
)
goto main

:devserver
echo.
echo 🚀 Starting development server...
if exist "start_dev_servers.bat" (
    call start_dev_servers.bat
) else (
    echo ❌ Development server script not found.
    pause
)
goto main

:invalid
echo.
echo ❌ Invalid choice. Please select 1-6.
pause
goto main

:exit
echo.
echo 👋 Goodbye!
exit /b 0
