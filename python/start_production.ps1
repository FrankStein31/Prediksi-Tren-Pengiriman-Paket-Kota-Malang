# Production Server Starter with Gunicorn (PowerShell)
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Starting Flask API (Production Mode)" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptPath

if (!(Test-Path "venv\Scripts\Activate.ps1")) {
    Write-Host "[ERROR] Virtual environment not found!" -ForegroundColor Red
    Write-Host "Please run: python -m venv venv" -ForegroundColor Yellow
    Write-Host "Then install requirements: .\venv\Scripts\pip install -r requirements.txt" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "Activating virtual environment..." -ForegroundColor Green
& ".\venv\Scripts\Activate.ps1"

# Check if gunicorn is installed
$gunicornCheck = pip show gunicorn 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Gunicorn not found!" -ForegroundColor Red
    Write-Host "Installing gunicorn..." -ForegroundColor Yellow
    pip install gunicorn
}

# Set environment variables
$env:FLASK_ENV = "production"
$env:FLASK_DEBUG = "False"
$env:GUNICORN_WORKERS = "4"
$env:GUNICORN_BIND = "0.0.0.0:5000"

Write-Host ""
Write-Host "Starting Gunicorn server..." -ForegroundColor Green
Write-Host "Server will run at: http://0.0.0.0:5000" -ForegroundColor Yellow
Write-Host "Workers: $env:GUNICORN_WORKERS" -ForegroundColor Yellow
Write-Host "Press CTRL+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start Gunicorn
gunicorn `
    --config gunicorn_config.py `
    --workers $env:GUNICORN_WORKERS `
    --bind $env:GUNICORN_BIND `
    --timeout 300 `
    --access-logfile - `
    --error-logfile - `
    --log-level info `
    app:application
