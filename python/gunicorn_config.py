# Gunicorn Configuration File
# Production-ready settings for Flask Prophet API

import os
import multiprocessing

# Server socket
bind = os.environ.get('GUNICORN_BIND', '0.0.0.0:5000')
backlog = 2048

# Worker processes
workers = int(os.environ.get('GUNICORN_WORKERS', multiprocessing.cpu_count() * 2 + 1))
worker_class = 'sync'
worker_connections = 1000
max_requests = 1000
max_requests_jitter = 50
timeout = 300  # 5 minutes for long predictions
keepalive = 5

# Logging
accesslog = os.environ.get('GUNICORN_ACCESS_LOG', '-')  # '-' means stdout
errorlog = os.environ.get('GUNICORN_ERROR_LOG', '-')   # '-' means stderr
loglevel = os.environ.get('GUNICORN_LOG_LEVEL', 'info')
access_log_format = '%(h)s %(l)s %(u)s %(t)s "%(r)s" %(s)s %(b)s "%(f)s" "%(a)s" %(D)s'

# Process naming
proc_name = 'prophet_api'

# Server mechanics
daemon = False
pidfile = None
user = None
group = None
tmp_upload_dir = None

# SSL (jika diperlukan)
# keyfile = '/path/to/key.pem'
# certfile = '/path/to/cert.pem'

# Preload app for better memory usage
preload_app = False

# Graceful timeout
graceful_timeout = 30

print(f"""
{'=' * 60}
🚀 Gunicorn Configuration
{'=' * 60}
Bind: {bind}
Workers: {workers}
Worker Class: {worker_class}
Timeout: {timeout}s
Log Level: {loglevel}
{'=' * 60}
""")
