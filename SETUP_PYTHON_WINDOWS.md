# Setup Python untuk Laravel di Windows (Laragon)

## 📋 Prasyarat
- Laragon sudah terinstal
- Python 3.10 atau lebih baru di Laragon
- MySQL/MariaDB aktif di Laragon

## 🔧 Langkah-langkah Setup

### 1. Verifikasi Python Laragon
```powershell
C:\laragon\bin\python\python-3.10\python.exe --version
```

### 2. Install Python Libraries
Buka PowerShell atau Terminal di VS Code, lalu jalankan:

```powershell
cd c:\laragon\www\ProjectSkripsi\prediksi-paket\python
C:\laragon\bin\python\python-3.10\python.exe -m pip install -r requirements.txt
```

### 3. Verifikasi Database
Pastikan database `prediksi_paket` sudah ada dan tabel `weekly_shipment_data` sudah terisi dengan data.

Cek di phpMyAdmin atau MySQL client:
```sql
SELECT COUNT(*) FROM weekly_shipment_data;
SELECT DISTINCT kecamatan FROM weekly_shipment_data;
```

### 4. Test Script Python
Test script prediksi secara manual:

```powershell
cd c:\laragon\www\ProjectSkripsi\prediksi-paket\python\scripts
C:\laragon\bin\python\python-3.10\python.exe visualize_prophet.py --kecamatan BLIMBING --weeks_historical 52 --weeks_forecast 4
```

Jika berhasil, akan muncul output JSON dengan data prediksi.

### 5. Konfigurasi Path Python di Laravel

File controller sudah dikonfigurasi untuk menggunakan path Python Laragon:
```php
$pythonPath = 'C:\\laragon\\bin\\python\\python-3.10\\python.exe';
```

Lokasi: `app\Http\Controllers\VisualisasiController.php`

### 6. Test dari Browser

1. Pastikan Laragon sudah running (Apache + MySQL)
2. Buka browser: `http://localhost/ProjectSkripsi/prediksi-paket/public/visualisasi`
3. Pilih kecamatan
4. Klik "Tampilkan Grafik"

## 📦 Python Libraries yang Digunakan

- **pandas 2.1.4**: Manipulasi data
- **numpy 1.24.3**: Operasi numerik
- **prophet 1.2.1**: Time series forecasting (Facebook Prophet)
- **mysql-connector-python 8.2.0**: Koneksi ke MySQL
- **matplotlib 3.7.2**: Visualisasi (untuk development)
- **scikit-learn 1.3.0**: Machine learning utilities

## 🔍 Troubleshooting

### Error: "Python tidak ditemukan"
Pastikan path Python sudah benar di `VisualisasiController.php`

### Error: "Database connection failed"
- Cek apakah MySQL sudah running di Laragon
- Verifikasi nama database di `visualize_prophet.py`:
  ```python
  database='prediksi_paket'
  ```

### Error: "No module named 'prophet'"
Install ulang requirements:
```powershell
C:\laragon\bin\python\python-3.10\python.exe -m pip install --upgrade -r requirements.txt
```

### Error: "Data tidak ditemukan"
Pastikan data weekly sudah di-generate. Buka halaman "Ringkasan Mingguan" untuk trigger auto-aggregation.

## 📊 Cara Kerja

1. **Laravel** (VisualisasiController) menerima request dari user
2. **PHP** memanggil script Python via `Process::run()`
3. **Python** (visualize_prophet.py):
   - Koneksi ke database MySQL
   - Load data weekly untuk kecamatan tertentu
   - Train model Prophet dengan data historis
   - Generate prediksi 4 minggu ke depan
   - Return hasil dalam format JSON
4. **Laravel** menerima JSON dan return ke frontend
5. **JavaScript** (Chart.js) render grafik interaktif

## ✅ Checklist Setup

- [ ] Python 3.10+ terinstal di Laragon
- [ ] Semua library Python terinstal (cek dengan `pip list`)
- [ ] Database `prediksi_paket` exists
- [ ] Tabel `weekly_shipment_data` berisi data
- [ ] Script Python bisa dijalankan manual
- [ ] Path Python di controller sudah benar
- [ ] Laravel bisa memanggil Python script
- [ ] Halaman visualisasi bisa diakses di browser
- [ ] Grafik prediksi tampil dengan sempurna

## 🎯 Fitur Prediksi

- ✅ Menampilkan 52 minggu data historis
- ✅ Prediksi 4 minggu ke depan
- ✅ Confidence interval (upper & lower bound)
- ✅ Statistik total paket, rata-rata, dll
- ✅ Grafik interaktif dengan Chart.js
- ✅ Tooltip informatif
- ✅ Responsive design

## 📝 Notes

- Model Prophet di-train ulang setiap kali request untuk memastikan prediksi selalu up-to-date
- Proses training memakan waktu 2-5 detik tergantung spesifikasi komputer
- Data diambil langsung dari database, tidak menggunakan file Excel
- Timezone sudah disesuaikan dengan Indonesia
