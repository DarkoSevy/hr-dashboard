@echo off
setlocal enabledelayedexpansion

:: Configuration
set APACHE_DIR=C:\xampp\apache
set APACHE_BIN=%APACHE_DIR%\bin
set APACHE_CONF=%APACHE_DIR%\conf\httpd.conf
set APACHE_PID=%APACHE_DIR%\logs\httpd.pid

:: Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo This script requires administrator privileges.
    echo Please run as administrator.
    pause
    exit /b 1
)

:menu
cls
echo Apache Service Manager
echo ====================
echo.
echo 1. Start Apache
echo 2. Stop Apache
echo 3. Restart Apache
echo 4. Check Status
echo 5. View Error Log
echo 6. Exit
echo.
set /p choice=Enter your choice (1-6): 

if "%choice%"=="1" goto start
if "%choice%"=="2" goto stop
if "%choice%"=="3" goto restart
if "%choice%"=="4" goto status
if "%choice%"=="5" goto viewlog
if "%choice%"=="6" goto end

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto menu

:start
echo Starting Apache...
%APACHE_BIN%\httpd.exe -k start -f "%APACHE_CONF%"
if %errorLevel% equ 0 (
    echo Apache started successfully.
) else (
    echo Failed to start Apache.
)
timeout /t 2 >nul
goto menu

:stop
echo Stopping Apache...
%APACHE_BIN%\httpd.exe -k stop -f "%APACHE_CONF%"
if %errorLevel% equ 0 (
    echo Apache stopped successfully.
) else (
    echo Failed to stop Apache.
)
timeout /t 2 >nul
goto menu

:restart
echo Restarting Apache...
%APACHE_BIN%\httpd.exe -k restart -f "%APACHE_CONF%"
if %errorLevel% equ 0 (
    echo Apache restarted successfully.
) else (
    echo Failed to restart Apache.
)
timeout /t 2 >nul
goto menu

:status
echo Checking Apache status...
sc query Apache2.4 >nul
if %errorLevel% equ 0 (
    echo Apache is running.
) else (
    echo Apache is not running.
)
timeout /t 2 >nul
goto menu

:viewlog
echo Opening error log...
start notepad "%APACHE_DIR%\logs\error.log"
goto menu

:end
echo Exiting...
exit /b 0 