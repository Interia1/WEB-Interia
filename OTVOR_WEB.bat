@echo off
chcp 65001 >nul 2>&1
title WEB-Interia - otvorenie v prehliadači
cd /d "%~dp0"

echo.
echo ==========================================
echo        WEB-Interia
echo ==========================================
echo.
echo   [ ENTER ] Otvoriť web v prehliadači
echo.
pause >nul

if not exist "%~dp0index.html" (
    echo.
    echo CHYBA: Súbor index.html nebol nájdený.
    echo Uistite sa, že tento súbor spúšťate z priečinka WEB-Interia.
    echo.
    pause
    exit /b 1
)

start "" "%~dp0index.html"

echo.
echo Web sa otvoril v prehliadači.
echo.
pause
