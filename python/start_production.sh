#!/bin/bash
# Production Server Starter with Gunicorn

echo "=========================================="
echo " Starting Flask API (Production Mode)"
echo "=========================================="
echo ""

cd "$(dirname "$0")"

# Check if virtual environment exists
if [ ! -d "venv" ]; then
    echo "[ERROR] Virtual environment not found!"
    echo "Please run: python -m venv venv"
    echo "Then install requirements: venv/bin/pip install -r requirements.txt"
    exit 1
fi

# Activate virtual environment
echo "Activating virtual environment..."
source venv/bin/activate

# Check if gunicorn is installed
if ! command -v gunicorn &> /dev/null; then
    echo "[ERROR] Gunicorn not found!"
    echo "Installing gunicorn..."
    pip install gunicorn
fi

# Set environment variables
export FLASK_ENV=production
export FLASK_DEBUG=False
export GUNICORN_WORKERS=4
export GUNICORN_BIND=0.0.0.0:5000

echo ""
echo "Starting Gunicorn server..."
echo "Server will run at: http://0.0.0.0:5000"
echo "Workers: $GUNICORN_WORKERS"
echo "Press CTRL+C to stop the server"
echo ""

# Start Gunicorn
gunicorn \
    --config gunicorn_config.py \
    --workers $GUNICORN_WORKERS \
    --bind $GUNICORN_BIND \
    --timeout 300 \
    --access-logfile - \
    --error-logfile - \
    --log-level info \
    app:application
