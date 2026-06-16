@echo off
chcp 65001 >nul 2>&1
setlocal

echo.
echo ==========================================
echo    WEB-Interia - lokalny nahlad webu
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

echo Otvaram web v prehliadaci...
start "" "%CD%\index.html"

echo.
echo HOTOVO.
echo Web sa otvara lokalne zo suboru index.html.
echo Docker uz na tento nahlad nepotrebujete.
echo.
pause
