@echo off
REM Production Server Starter with Gunicorn (Windows)

echo ==========================================
echo  Starting Flask API (Production Mode)
echo ==========================================
echo.

cd /d "%~dp0"

if not exist "venv\Scripts\activate.bat" (
    echo [ERROR] Virtual environment not found!
    echo Please run: python -m venv venv
    echo Then install requirements: venv\Scripts\pip install -r requirements.txt
    pause
    exit /b 1
)

echo Activating virtual environment...
call venv\Scripts\activate.bat

REM Check if gunicorn is installed
pip show gunicorn >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Gunicorn not found!
    echo Installing gunicorn...
    pip install gunicorn
)

REM Set environment variables
set FLASK_ENV=production
set FLASK_DEBUG=False
set GUNICORN_WORKERS=4
set GUNICORN_BIND=0.0.0.0:5000

echo.
echo Starting Gunicorn server...
echo Server will run at: http://0.0.0.0:5000
echo Workers: %GUNICORN_WORKERS%
echo Press CTRL+C to stop the server
echo.

REM Start Gunicorn
gunicorn ^
    --config gunicorn_config.py ^
    --workers %GUNICORN_WORKERS% ^
    --bind %GUNICORN_BIND% ^
    --timeout 300 ^
    --access-logfile - ^
    --error-logfile - ^
    --log-level info ^
    app:application

pause
