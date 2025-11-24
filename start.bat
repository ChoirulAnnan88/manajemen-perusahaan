@echo off
chcp 65001 > nul
title Toko Online CI4 - Development Server
color 0A

echo ========================================
echo    TOKO ONLINE CI4 - DEVELOPMENT SERVER
echo ========================================
echo.

:: Check jika PHP ada di PATH
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di PATH!
    echo Pastikan XAMPP sudah diinstall dan PHP ada di environment variables
    echo.
    pause
    exit /b 1
)

echo [INFO] Memulai development server...
echo [INFO] Project: Toko Online CI4
echo [INFO] URL: http://localhost:8081
echo [INFO] PHP Version:
php --version
echo.

:: Check jika port 8081 sedang digunakan
netstat -ano | findstr :8081 > nul
if %errorlevel% == 0 (
    echo [WARNING] Port 8081 sedang digunakan!
    echo Menutup proses yang menggunakan port 8081...
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8081') do (
        taskkill /PID %%a /F > nul 2>&1
    )
    timeout /t 2 /nobreak > nul
)

echo.
echo [SUCCESS] Server akan berjalan di:
echo http://localhost:8081
echo.
echo [TIP] Tekan Ctrl+C untuk menghentikan server
echo ========================================
echo.

:: Start development server
php spark serve --port 8081

pause