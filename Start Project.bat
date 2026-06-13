@echo off
title Real Estate App Launcher
color 0A

:: Automatically detect the directory of this batch file
set "BASE_DIR=%~dp0"
set "PROJECT_DIR=%BASE_DIR%realestate-manager"

cd /d "%PROJECT_DIR%"

echo ===================================================
echo     Real Estate Management System - Auto Starter
echo ===================================================
echo.
echo [1/2] Starting Laravel Backend Server...
start "Laravel Backend Server" cmd /k "C:\xampp\php\php.exe artisan serve"

echo [2/2] Starting Vite Frontend Server (CSS/JS Compiler)...
start "Vite Frontend Server" cmd /k "npm run dev"

echo.
echo Both servers are launching in separate background windows.
echo Opening your web browser to http://localhost:8000...
echo.

timeout /t 3 >nul
start http://localhost:8000
exit
