@echo off
chcp 65001 >nul 2>&1
setlocal

echo.
echo ==========================================
echo    WEB-Interia - lokalny server
echo ==========================================
echo.

if not exist "index.html" (
    echo CHYBA: Subor index.html nebol najdeny.
    echo.
    echo Uistite sa, ze ste v priecinku WEB-Interia.
    echo.
    pause
    exit /b 1
)

where python >nul 2>&1
if errorlevel 1 (
    echo CHYBA: Python nie je dostupny v systeme.
    echo Nainstalujte Python alebo spustite web cez Docker / Codespaces.
    echo.
    pause
    exit /b 1
)

echo Spustam lokalny server na http://localhost:8000 ...
start "" http://localhost:8000
python -m http.server 8000

echo.
echo HOTOVO.
echo Web sa otvoril cez lokalny server.
echo Ak sa stranka nenasla, obnovte prehliadac po par sekundach.
echo.
pause