@echo off
chcp 65001 >nul 2>&1
title WEB-Interia - lokalny server
cd /d "%~dp0"

echo.
echo ==========================================
echo        WEB-Interia
echo ==========================================
echo.
echo Spustam lokalny server na porte 8000...
echo.

if not exist "%~dp0index.html" (
    echo.
    echo CHYBA: Súbor index.html nebol nájdený.
    echo Uistite sa, že tento súbor spúšťate z priečinka WEB-Interia.
    echo.
    pause
    exit /b 1
)

start "" http://localhost:8000
python -m http.server 8000

echo.
echo Server bol ukonceny.
echo.
pause
