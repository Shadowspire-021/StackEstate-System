@echo off
title Real Estate App Servers
color 0A

:: Hamesha pehle project directory me jayen
cd /d "c:\Users\Iqkas\OneDrive\AppData\realestate management system\realestate-manager"

echo =========================================
echo    Starting Real Estate App Servers...
echo =========================================
echo.

echo [1/2] Starting Laravel Server...
start "Laravel Server" cmd /k "C:\xampp\php\php.exe artisan serve"

echo [2/2] Starting Vite Frontend Server...
start "Vite Server" cmd /k "npm run dev"

echo.
echo Both servers are starting up in separate windows!
echo Opening your browser to http://localhost:8000
echo.

timeout /t 3 >nul
start http://localhost:8000
exit
