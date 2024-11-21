@echo off
setlocal enabledelayedexpansion

:: Functie voor input validatie
:validate_input
if "%~1"=="" (
    call :prompt_hostname
) else (
    set "hostname=%~1"
)

:: Controleer of hostname geldig is
echo %hostname% | findstr /R /C:"^[0-9a-zA-Z\.\-]*$" >nul
if errorlevel 1 (
    echo Ongeldige hostnaam of IP-adres.
    exit /b 1
)

:: Standaard poorten configuratie
set "default_ports=21 22 23 80 443 3306 3389 5432 8080 9000"
set "custom_ports="

:: Menu voor poort selectie
:port_menu
cls
echo Poort Scanner Menu
echo 1. Standaard poorten scannen
echo 2. Eigen poorten toevoegen
echo 3. Scan starten
echo 4. Afsluiten
set /p "choice=Maak een keuze (1-4): "

if "%choice%"=="1" goto scan
if "%choice%"=="2" goto custom_ports
if "%choice%"=="3" goto scan
if "%choice%"=="4" exit /b

:custom_ports
set /p "new_ports=Voer poorten in (gescheiden door spaties): "
set "custom_ports=%new_ports%"
goto port_menu

:prompt_hostname
set /p "hostname=Voer hostnaam of IP-adres in: "
goto validate_input

:scan
:: Combineer standaard en aangepaste poorten
if defined custom_ports (
    set "scan_ports=%default_ports% %custom_ports%"
) else (
    set "scan_ports=%default_ports%"
)

:: Log-bestand aanmaken
set "logfile=port_scan_%hostname%_%date:~-4,4%%date:~-10,2%%date:~-7,2%.log"
echo Poort scan voor %hostname% op %date% %time% > "%logfile%"

:: Scan uitvoeren met PowerShell voor betrouwbaarheid
powershell -Command "& {
    $hostname = '%hostname%'
    $ports = '%scan_ports%' -split ' '
    $results = @()

    foreach ($port in $ports) {
        $result = Test-NetConnection -ComputerName $hostname -Port $port -InformationLevel Quiet
        $status = if ($result) { 'OPEN' } else { 'GESLOTEN' }
        $results += \"Poort $port : $status\"
        
        if ($result) {
            Write-Host \"[OPEN] Poort $port is open!\" -ForegroundColor Green
        } else {
            Write-Host \"[GESLOTEN] Poort $port is gesloten.\" -ForegroundColor Red
        }
    }

    $results | Out-File -Append '%logfile%'
}"

echo Scan resultaten zijn opgeslagen in %logfile%
pause
goto port_menu
