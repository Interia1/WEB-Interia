@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

echo.
echo ==========================================
echo    WEB-Interia - Automaticky start
echo ==========================================
echo.

REM ─────────────────────────────────────────
REM KROK 1: Kontrola Docker
REM ─────────────────────────────────────────
echo [1/5] Kontrolujem Docker...

docker --version >nul 2>&1
if !errorlevel! neq 0 (
    echo.
    echo ══════════════════════════════════════════════════════
    echo    CHYBA: Docker nie je nainstalovany!
    echo ══════════════════════════════════════════════════════
    echo.
    echo    Docker je potrebny na spustenie webu.
    echo.
    echo    Stiahnite Docker Desktop zadarmo:
    echo    https://www.docker.com/products/docker-desktop/
    echo.
    echo    Po instalacii spustite Docker Desktop a
    echo    spustite tento skript znova.
    echo.
    pause
    exit /b 1
)

docker info >nul 2>&1
if !errorlevel! neq 0 (
    echo.
    echo ══════════════════════════════════════════════════════
    echo    CHYBA: Docker nie je spusteny!
    echo ══════════════════════════════════════════════════════
    echo.
    echo    Otvorte aplikaciu Docker Desktop a pockajte
    echo    kym sa spusti. Potom spustite tento skript znova.
    echo.
    pause
    exit /b 1
)

echo    [OK] Docker je nainstalovany a spusteny

REM ─────────────────────────────────────────
REM KROK 2: Kontrola ci web uz bezi
REM ─────────────────────────────────────────
echo.
echo [2/5] Kontrolujem stav webu...

docker ps --format "{{.Names}}" 2>nul | findstr /i "interia laravel app nginx php" >nul 2>&1
if !errorlevel! equ 0 (
    echo.
    echo    UPOZORNENIE: Web sa zda byt uz spusteny!
    echo.
    echo    Otvorte prehliadac na: http://localhost:8000
    echo.
    set /p restart_choice="   Chcete web restartovat? (y/n): "
    if /i "!restart_choice!" equ "y" (
        echo    Zastavujem staru instanciu...
        docker-compose down >nul 2>&1
        echo    [OK] Stare kontajnery zastavene
    ) else (
        echo.
        echo    [OK] Web bezi na: http://localhost:8000
        echo.
        start "" "http://localhost:8000"
        echo.
        pause
        exit /b 0
    )
)

REM ─────────────────────────────────────────
REM KROK 3: Stiahnutie najnovsieho kodu
REM ─────────────────────────────────────────
echo.
echo [3/5] Stiahujem najnovsi kod...

where gh >nul 2>&1
if !errorlevel! equ 0 (
    echo    Hladam otvorene pull requesty...
    set "LATEST_PR="
    for /f "tokens=* delims=" %%i in ('gh pr list --state open --limit 1 --json number --jq ".[0].number" 2^>nul') do (
        set "LATEST_PR=%%i"
    )
    if defined LATEST_PR (
        if "!LATEST_PR!" neq "null" (
            echo    Mergujem PR #!LATEST_PR!...
            gh pr merge !LATEST_PR! --merge --auto >nul 2>&1
            if !errorlevel! equ 0 (
                echo    [OK] PR #!LATEST_PR! zluceny do main
                timeout /t 2 /nobreak >nul
            ) else (
                echo    UPOZORNENIE: PR nebolo mozne zlucit automaticky ^(pokracujem...^)
            )
        )
    ) else (
        echo    Ziadne otvorene PR nenajdene.
    )
)

git pull origin main >nul 2>&1
if !errorlevel! equ 0 (
    echo    [OK] Kod je aktualny
) else (
    git pull >nul 2>&1
    if !errorlevel! equ 0 (
        echo    [OK] Kod je aktualny
    ) else (
        echo    UPOZORNENIE: Git pull zlyhal ^(pokracujem s aktualnym kodom^)
    )
)

REM Kontrola docker-compose.yml
if not exist "docker-compose.yml" (
    if not exist "docker-compose.yaml" (
        echo.
        echo ══════════════════════════════════════════════════════
        echo    CHYBA: Subor docker-compose.yml nebol najdeny!
        echo ══════════════════════════════════════════════════════
        echo.
        echo    Uistite sa, ze:
        echo    1. Ste v spravnom priecinku projektu ^(WEB-Interia^)
        echo    2. Bol zluceny PR s Docker nastavenim
        echo.
        pause
        exit /b 1
    )
)

REM ─────────────────────────────────────────
REM KROK 4: Spustenie Docker
REM ─────────────────────────────────────────
echo.
echo [4/5] Spustam web (Docker)...

docker-compose up -d
if !errorlevel! neq 0 (
    echo.
    echo ══════════════════════════════════════════════════════
    echo    CHYBA: Spustenie zlyhalo!
    echo ══════════════════════════════════════════════════════
    echo.
    echo    Skuste:
    echo    1. docker-compose down
    echo    2. docker-compose up -d
    echo    alebo skontrolujte logy: docker-compose logs
    echo.
    pause
    exit /b 1
)

echo    [OK] Docker kontajnery spustene

REM ─────────────────────────────────────────
REM KROK 5: Cakanie na nabootovanie
REM ─────────────────────────────────────────
echo.
echo [5/5] Cakam kym sa aplikacia nabootuje (30 sekund)...
timeout /t 30 /nobreak >nul
echo    [OK] Hotovo!

REM ─────────────────────────────────────────
REM VYSLEDOK
REM ─────────────────────────────────────────
echo.
echo ==========================================
echo    WEB-Interia je spusteny!
echo ==========================================
echo.
echo    Adresa webu: http://localhost:8000
echo.
echo    Otváram prehliadac...
echo.
start "" "http://localhost:8000"
echo    INFO: Na zastavenie webu spustite: docker-compose down
echo.
pause
