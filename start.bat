@echo off
chcp 65001 >nul 2>&1
setlocal EnableDelayedExpansion
cd /d "%~dp0"

echo.
echo ==========================================
echo    WEB-Interia - lokalny server
echo ==========================================
echo.

if not exist "artisan" (
    echo CHYBA: Subor artisan nebol najdeny.
    echo.
    echo Uistite sa, ze ste v priecinku WEB-Interia.
    echo.
    pause
    exit /b 1
)

where git >nul 2>&1
if errorlevel 1 (
    echo UPOZORNENIE: Git nie je dostupny, commit/push/backup sa preskakuje.
    echo.
    goto :SYNC_DONE
)

echo Synchronizujem projekt (commit + pull + push + backup)...
set "GIT_TERMINAL_PROMPT=0"

for /f %%i in ('git status --porcelain ^| find /c /v ""') do set "CHANGES=%%i"
if not "!CHANGES!"=="0" (
    git add -A
    set "SYNC_MSG=Auto sync %DATE% %TIME%"
    git commit -m "!SYNC_MSG!" >nul 2>&1
    if errorlevel 1 (
        echo UPOZORNENIE: Commit sa nepodaril. Skontrolujte git config user.name/user.email.
    ) else (
        echo OK: Zmeny boli automaticky commitnute.
    )
) else (
    echo UPOZORNENIE: Nenasli sa lokalne zmeny na commit.
)

git pull --rebase origin main >nul 2>&1
if errorlevel 1 (
    echo UPOZORNENIE: git pull --rebase zlyhal (pravdepodobne konflikt).
) else (
    echo OK: Vetva main je aktualizovana.
)

git push origin main >nul 2>&1
if errorlevel 1 (
    echo UPOZORNENIE: git push zlyhal alebo nebolo co pushnut.
 ) else (
    echo OK: Zmeny su pushnute na origin/main.
)

set "BACKUP_DIR=%~dp0..\WEB-Interia-backup.git"
if exist "!BACKUP_DIR!\HEAD" (
    git --git-dir="!BACKUP_DIR!" fetch --all --prune >nul 2>&1
    if errorlevel 1 (
        echo UPOZORNENIE: Aktualizacia backup klonu zlyhala: !BACKUP_DIR!
    ) else (
        echo OK: Backup klon aktualizovany: !BACKUP_DIR!
    )
) else (
    git clone --mirror . "!BACKUP_DIR!" >nul 2>&1
    if errorlevel 1 (
        echo UPOZORNENIE: Vytvorenie backup klonu zlyhalo: !BACKUP_DIR!
    ) else (
        echo OK: Backup klon vytvoreny: !BACKUP_DIR!
    )
)

echo.

:SYNC_DONE

where php >nul 2>&1
if errorlevel 1 (
    echo CHYBA: PHP nie je dostupne v systeme.
    echo Nainstalujte PHP 8.2+ alebo spustite web cez Docker.
    echo.
    where python >nul 2>&1
    if errorlevel 1 (
        echo CHYBA: Nie je dostupny ani Python fallback server.
        echo.
        pause
        exit /b 1
    )

    echo Spustam len staticky fallback na http://localhost:8000 ...
    echo UPOZORNENIE: Vyzor moze byt iny ako v Laravel aplikacii.
    start "" http://localhost:8000
    python -m http.server 8000
    goto :EOF
)

echo Spustam Laravel server na http://localhost:8000 ...
start "" http://localhost:8000
php artisan serve --host=127.0.0.1 --port=8000

echo.
echo HOTOVO.
echo Web sa otvoril cez Laravel server.
echo Ak sa stranka nenasla, obnovte prehliadac po par sekundach.
echo.
pause