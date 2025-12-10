@echo off
setlocal EnableDelayedExpansion

rem Minimal PHP dev server helper. Usage:
rem   build                -> serve on 127.0.0.1:8000 using src/public
rem   build serve [port]   -> serve on 127.0.0.1:<port>
rem   build serve [host] [port] -> serve on <host>:<port>
rem   build help           -> print usage

set ROOT=%~dp0
set PUBLIC_DIR=%ROOT%src\public
set HOST=127.0.0.1
set PORT=8000
set SRC_DIR=%ROOT%src
set VENDOR_DIR=%ROOT%vendor
set LIB_DIR=%ROOT%lib

if "%1"==""          goto :usage
if /I "%1"=="serve"  goto :serve
if /I "%1"=="help"   goto :usage
goto :usage

:serve
if NOT "%2"=="" set HOST=%2
if NOT "%3"=="" set PORT=%3

where php >nul 2>&1
if errorlevel 1 (
    echo PHP not found in PATH.
    exit /b 1
)

if not exist "%PUBLIC_DIR%" (
    echo Public directory not found: %PUBLIC_DIR%
    exit /b 1
)

echo Serving %PUBLIC_DIR% on %HOST%:%PORT%
php -S %HOST%:%PORT% -t %PUBLIC_DIR%
goto :eof

:usage
echo Usage:
echo   build ^| build serve [host] [port]
echo   build help
echo.
echo Defaults:
echo   host: %HOST%
echo   port: %PORT%
exit /b 1
