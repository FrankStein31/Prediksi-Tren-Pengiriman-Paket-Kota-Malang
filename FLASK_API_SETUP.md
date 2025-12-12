# 🐍 Setup Python Virtual Environment + Flask API

## 📋 Overview

Proyek ini menggunakan **Python Virtual Environment (venv)** untuk isolasi dependencies dan **Flask API** untuk integrasi dengan Laravel.

### Arsitektur:
```
Laravel (Frontend + Controller)
    ↓ HTTP Request
Flask API (Python) ← Model Prophet (.pkl)
    ↓ Query
MySQL Database (Laravel)
```

---

## 🔧 Setup Awal

### 1. Buat Virtual Environment

```powershell
cd c:\laragon\www\ProjectSkripsi\prediksi-paket\python
C:\laragon\bin\python\python-3.10\python.exe -m venv venv
```

### 2. Aktivasi Virtual Environment

**PowerShell:**
```powershell
.\venv\Scripts\Activate.ps1
```

**CMD:**
```cmd
venv\Scripts\activate.bat
```

### 3. Install Dependencies

```powershell
pip install -r requirements.txt
```

### 4. Verifikasi Instalasi

```powershell
pip list
```

Pastikan package ini terinstal:
- pandas==2.1.4
- numpy==1.24.3
- prophet==1.2.1
- flask==3.1.2
- flask-cors==6.0.1
- mysql-connector-python==8.2.0
- joblib==1.3.2

---

## 🚀 Menjalankan Flask API

### Cara 1: Menggunakan Script (Termudah)

**Windows CMD:**
```cmd
start_api.bat
```

**PowerShell:**
```powershell
.\start_api.ps1
```

### Cara 2: Manual

```powershell
# Aktifkan venv
.\venv\Scripts\Activate.ps1

# Jalankan Flask
python app.py
```

### Cara 3: Full Path (Tanpa Aktivasi)

```powershell
C:\laragon\www\ProjectSkripsi\prediksi-paket\python\venv\Scripts\python.exe C:\laragon\www\ProjectSkripsi\prediksi-paket\python\app.py
```

---

## 📡 Flask API Endpoints

### Base URL
```
http://127.0.0.1:5000
```

### 1. GET `/` - API Info
```bash
curl http://127.0.0.1:5000/
```

**Response:**
```json
{
  "name": "Prophet Prediction API",
  "version": "1.0.0",
  "endpoints": {
    "/": "API information",
    "/health": "Health check",
    "/api/predict": "POST - Generate predictions",
    "/api/kecamatans": "GET - List available kecamatans"
  }
}
```

### 2. GET `/health` - Health Check
```bash
curl http://127.0.0.1:5000/health
```

**Response:**
```json
{
  "status": "healthy",
  "database": "OK",
  "models": [
    {"kecamatan": "BLIMBING", "exists": true},
    {"kecamatan": "KEDUNGKANDANG", "exists": true},
    {"kecamatan": "KLOJEN", "exists": true},
    {"kecamatan": "LOWOKWARU", "exists": true},
    {"kecamatan": "SUKUN", "exists": true}
  ],
  "timestamp": "2025-12-12T10:00:00"
}
```

### 3. GET `/api/kecamatans` - List Kecamatans
```bash
curl http://127.0.0.1:5000/api/kecamatans
```

**Response:**
```json
{
  "kecamatans": ["BLIMBING", "KEDUNGKANDANG", "KLOJEN", "LOWOKWARU", "SUKUN"],
  "count": 5
}
```

### 4. POST `/api/predict` - Generate Prediction
```bash
curl -X POST http://127.0.0.1:5000/api/predict \
  -H "Content-Type: application/json" \
  -d '{
    "kecamatan": "BLIMBING",
    "weeks_historical": 52,
    "weeks_forecast": 4
  }'
```

**Request Body:**
```json
{
  "kecamatan": "BLIMBING",      // Required
  "weeks_historical": 52,        // Optional, default: 52
  "weeks_forecast": 4            // Optional, default: 4
}
```

**Response:**
```json
{
  "success": true,
  "kecamatan": "BLIMBING",
  "historical": [
    {
      "date": "2024-01-01",
      "actual": 726,
      "week_number": 1,
      "year": 2024
    }
    // ... 51 more weeks
  ],
  "forecast": [
    {
      "date": "2024-12-29",
      "predicted": 983,
      "lower_bound": 616,
      "upper_bound": 1335,
      "week_number": 52,
      "year": 2024
    }
    // ... 3 more weeks
  ],
  "statistics": {
    "total_historical": 48000,
    "average_weekly": 923,
    "total_forecast": 4090,
    "weeks_historical": 52,
    "weeks_forecast": 4,
    "date_range_start": "2024-01-01",
    "date_range_end": "2024-12-23",
    "forecast_start": "2024-12-29",
    "forecast_end": "2025-01-19"
  },
  "generated_at": "2025-12-12T10:00:00"
}
```

---

## 🔌 Integrasi dengan Laravel

### 1. Laravel Controller

File: `app/Http/Controllers/VisualisasiController.php`

Laravel menggunakan **GuzzleHTTP** untuk memanggil Flask API:

```php
$client = new \GuzzleHttp\Client([
    'timeout' => 120,
    'connect_timeout' => 10
]);

$response = $client->post('http://127.0.0.1:5000/api/predict', [
    'json' => [
        'kecamatan' => $kecamatan,
        'weeks_historical' => 52,
        'weeks_forecast' => 4
    ]
]);
```

### 2. Install Guzzle (Jika Belum)

```bash
cd c:\laragon\www\ProjectSkripsi\prediksi-paket
composer require guzzlehttp/guzzle
```

### 3. Test dari Laravel

```bash
php artisan serve
```

Buka: http://127.0.0.1:8000/visualisasi

---

## 🐛 Troubleshooting

### Error: "Cannot connect to Flask API"

**Penyebab:** Flask API tidak running

**Solusi:**
```powershell
cd c:\laragon\www\ProjectSkripsi\prediksi-paket\python
.\start_api.bat
```

### Error: "Virtual environment not found"

**Penyebab:** venv belum dibuat

**Solusi:**
```powershell
C:\laragon\bin\python\python-3.10\python.exe -m venv venv
pip install -r requirements.txt
```

### Error: "Module 'flask' not found"

**Penyebab:** Dependencies belum terinstall di venv

**Solusi:**
```powershell
.\venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

### Error: "Model file not found"

**Penyebab:** File .pkl model tidak ada

**Solusi:**
Pastikan file model ada di folder `python/models/`:
- prophet_model_BLIMBING.pkl
- prophet_model_KEDUNGKANDANG.pkl
- prophet_model_KLOJEN.pkl
- prophet_model_LOWOKWARU.pkl
- prophet_model_SUKUN.pkl

### Error: "Database connection failed"

**Penyebab:** MySQL tidak running atau kredensial salah

**Solusi:**
1. Start MySQL di Laragon
2. Cek konfigurasi di `app.py`:
```python
DB_CONFIG = {
    'host': 'localhost',
    'database': 'prediksi_paket',
    'user': 'root',
    'password': ''
}
```

### Port 5000 Already in Use

**Solusi 1: Kill proses yang menggunakan port 5000**
```powershell
netstat -ano | findstr :5000
taskkill /PID <PID> /F
```

**Solusi 2: Ganti port di app.py**
```python
app.run(host='127.0.0.1', port=5001)  # Ganti ke 5001
```

Jangan lupa update di Laravel Controller juga!

---

## 📊 Testing API

### Menggunakan Browser
```
http://127.0.0.1:5000/
http://127.0.0.1:5000/health
http://127.0.0.1:5000/api/kecamatans
```

### Menggunakan curl (PowerShell)
```powershell
curl http://127.0.0.1:5000/health

curl -X POST http://127.0.0.1:5000/api/predict `
  -H "Content-Type: application/json" `
  -Body '{"kecamatan":"BLIMBING","weeks_historical":52,"weeks_forecast":4}'
```

### Menggunakan Postman
1. Method: POST
2. URL: http://127.0.0.1:5000/api/predict
3. Headers: Content-Type: application/json
4. Body (raw JSON):
```json
{
  "kecamatan": "BLIMBING",
  "weeks_historical": 52,
  "weeks_forecast": 4
}
```

---

## 🔄 Development Workflow

### 1. Start Development

```powershell
# Terminal 1: Flask API
cd c:\laragon\www\ProjectSkripsi\prediksi-paket\python
.\start_api.bat

# Terminal 2: Laravel
cd c:\laragon\www\ProjectSkripsi\prediksi-paket
php artisan serve
```

### 2. Stop Services

**Flask:**
- Tekan `CTRL+C` di terminal Flask

**Laravel:**
- Tekan `CTRL+C` di terminal Laravel

### 3. Update Dependencies

```powershell
# Activate venv
.\venv\Scripts\Activate.ps1

# Update packages
pip install --upgrade <package-name>

# Update requirements.txt
pip freeze > requirements.txt
```

---

## 📝 File Structure

```
python/
├── venv/                      # Virtual environment (auto-generated)
├── models/                    # Prophet model files (.pkl)
│   ├── prophet_model_BLIMBING.pkl
│   ├── prophet_model_KEDUNGKANDANG.pkl
│   ├── prophet_model_KLOJEN.pkl
│   ├── prophet_model_LOWOKWARU.pkl
│   └── prophet_model_SUKUN.pkl
├── app.py                     # Flask API server
├── requirements.txt           # Python dependencies
├── start_api.bat             # Windows batch starter
└── start_api.ps1             # PowerShell starter
```

---

## ✅ Checklist Setup

- [ ] Python 3.10 terinstal
- [ ] Virtual environment dibuat (`venv/`)
- [ ] Dependencies terinstall (`pip install -r requirements.txt`)
- [ ] Flask API bisa dijalankan (`python app.py`)
- [ ] Database `prediksi_paket` exists dan terisi
- [ ] Model files (.pkl) tersedia di `models/`
- [ ] Laravel Controller updated (Guzzle HTTP)
- [ ] Guzzle terinstall di Laravel (`composer require guzzlehttp/guzzle`)
- [ ] Flask API health check OK (`http://127.0.0.1:5000/health`)
- [ ] Laravel bisa connect ke Flask API
- [ ] Visualisasi page tampil sempurna

---

## 🎯 Advantages of Flask API

✅ **Isolated Environment**: Dependencies tidak bentrok dengan system Python  
✅ **RESTful API**: Komunikasi standar antara Laravel dan Python  
✅ **Scalable**: Bisa di-deploy ke server terpisah  
✅ **Better Error Handling**: Response terstruktur dalam JSON  
✅ **Fast Response**: Model sudah di-load, tidak perlu training ulang  
✅ **CORS Support**: Bisa diakses dari domain lain  
✅ **Production Ready**: Mudah di-deploy dengan gunicorn/uwsgi  

---

## 🚀 Production Deployment (Future)

### Using Gunicorn (Linux/Production)

```bash
pip install gunicorn
gunicorn -w 4 -b 127.0.0.1:5000 app:app
```

### Using PM2 (Node.js Process Manager)

```bash
npm install -g pm2
pm2 start app.py --interpreter venv/Scripts/python.exe --name prophet-api
pm2 save
pm2 startup
```

### Using Docker

```dockerfile
FROM python:3.10-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
CMD ["python", "app.py"]
```

---

**Happy Coding! 🎉**
