@echo off
echo ========================================
echo  Starting Flask API Server (venv)
echo ========================================
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

echo.
echo Starting Flask server...
echo Server will run at: http://127.0.0.1:5000
echo Press CTRL+C to stop the server
echo.

python app.py

pause
