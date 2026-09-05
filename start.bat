@echo off
chcp 65001 >nul 2>&1
setlocal EnableExtensions EnableDelayedExpansion
cd /d "%~dp0"

set "PORT=8000"
set "LOCAL_URL=http://127.0.0.1:%PORT%"
set "LOG_FILE=%TEMP%\web-interia-artisan.log"

echo.
echo ==========================================
echo WEB-Interia - fresh start (PC + mobile)
echo ==========================================
echo.

if not exist "artisan" (
  echo ERROR: Subor artisan nebol najdeny.
  echo Spustite start.bat v priecinku WEB-Interia.
  echo.
  pause
  exit /b 1
)

where php >nul 2>&1
if errorlevel 1 (
  echo ERROR: PHP nie je dostupne.
  echo Nainstalujte PHP 8.2+ a skuste znova.
  echo.
  pause
  exit /b 1
)

if /I "%WEB_AUTO_BACKUP%"=="0" (
  echo Automaticka zaloha je pre tento start vypnuta.
) else (
  echo Vytvaram automaticku zalohu pred spustenim...
  powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doplnky\automaticke-zalohy\backup-full.ps1"
  if errorlevel 1 (
    echo ERROR: Zaloha sa nepodarila vytvorit. Server sa nespusti.
    echo.
    pause
    exit /b 1
  )
)

echo Kontrolujem, ci server uz bezi na porte %PORT%...
for /f "tokens=*" %%i in ('powershell -NoProfile -Command "try {(Invoke-WebRequest -UseBasicParsing %LOCAL_URL% -TimeoutSec 2) ^| Out-Null; 'RUNNING'} catch {'STOPPED'}"') do set "STATE=%%i"

if /I "%STATE%"=="RUNNING" (
  echo Server uz bezi.
) else (
  echo Spustam Laravel server na porte %PORT%...
  start "WEB-Interia Server" /B cmd /c "php artisan serve --host=0.0.0.0 --port=%PORT% > \"%LOG_FILE%\" 2>&1"
)

set "READY=0"
for /L %%n in (1,1,25) do (
  powershell -NoProfile -Command "try {(Invoke-WebRequest -UseBasicParsing %LOCAL_URL% -TimeoutSec 2) ^| Out-Null; exit 0} catch {exit 1}"
  if not errorlevel 1 (
    set "READY=1"
    goto :READY_OK
  )
  timeout /t 1 /nobreak >nul
)

:READY_OK
if not "%READY%"=="1" (
  echo ERROR: Server neodpoveda na %LOCAL_URL%.
  if exist "%LOG_FILE%" (
    echo Posledne logy:
    powershell -NoProfile -Command "Get-Content -Tail 30 '%LOG_FILE%'"
  )
  echo.
  pause
  exit /b 1
)

for /f "tokens=*" %%i in ('powershell -NoProfile -Command "$ip=(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue ^| Where-Object {$_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' -and $_.InterfaceAlias -notmatch 'Loopback^|vEthernet^|WSL^|Hyper-V^|Docker^|Virtual'} ^| Select-Object -First 1 -ExpandProperty IPAddress); if($ip){$ip}"') do set "LAN_IP=%%i"

echo.
echo WEB-Interia bezi.
echo PC:     %LOCAL_URL%
if defined LAN_IP (
  echo Mobile: http://%LAN_IP%:%PORT%
  echo Poznamka: mobil musi byt na rovnakej Wi-Fi sieti.
)

echo.
start "" %LOCAL_URL%
exit /b 0
