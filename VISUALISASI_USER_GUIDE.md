# 📊 Visualisasi Prediksi Paket - User Guide

## Deskripsi
Fitur visualisasi prediksi menggunakan **Facebook Prophet**, sebuah algoritma time series forecasting yang powerful untuk memprediksi tren pengiriman paket di masa depan.

## 🎯 Fitur Utama

### 1. **Data Historis**
- Menampilkan hingga 52 minggu (1 tahun) data historis
- Data diambil langsung dari database
- Visualisasi berupa line chart berwarna biru

### 2. **Prediksi (Forecast)**
- Prediksi 4 minggu ke depan secara default
- Bisa diatur dari 1-52 minggu
- Line chart berwarna hijau dengan garis putus-putus
- Menggunakan model Prophet yang di-train real-time

### 3. **Confidence Interval**
- Upper bound dan lower bound prediksi
- Menunjukkan rentang kemungkinan nilai
- Area hijau transparan di sekitar garis prediksi

### 4. **Statistik Summary**
- Total paket historis (52 minggu)
- Rata-rata paket per minggu
- Total prediksi (4 minggu ke depan)
- Total minggu yang ditampilkan

## 🚀 Cara Menggunakan

### Langkah 1: Akses Halaman
Buka URL: `http://localhost:8000/visualisasi`

### Langkah 2: Pilih Kecamatan
Pilih salah satu kecamatan dari dropdown:
- BLIMBING
- KEDUNGKANDANG
- KLOJEN
- LOWOKWARU
- SUKUN

### Langkah 3: Atur Parameter (Opsional)
- **Minggu Historis**: 12-104 minggu (default: 52)
- **Minggu Prediksi**: 1-52 minggu (default: 4)

### Langkah 4: Klik "Tampilkan Grafik"
Sistem akan:
1. Mengambil data dari database
2. Melatih model Prophet
3. Generate prediksi
4. Menampilkan grafik interaktif

## 📈 Membaca Grafik

### Label X-Axis (Horizontal)
Format: `W{nomor_minggu} '{tahun}`
Contoh: `W1 '24` = Minggu ke-1 tahun 2024

### Label Y-Axis (Vertical)
Jumlah paket (format: dengan pemisah ribuan)

### Tooltip (Hover)
Arahkan kursor ke titik data untuk melihat:
- Minggu dan tahun
- Jumlah paket aktual (data historis)
- Jumlah paket prediksi (forecast)
- Confidence interval (rentang prediksi)

### Legend
- 🔵 **Data Aktual**: Data historis yang sudah terjadi
- 🟢 **Prediksi**: Hasil forecasting Prophet
- 🟢 (transparan): Area confidence interval

## 🎨 Fitur Interaktif

### 1. Zoom
- Scroll pada grafik untuk zoom in/out
- Atau gunakan pinch gesture (touchpad)

### 2. Pan
- Klik dan drag untuk menggeser grafik

### 3. Reset View
- Double-click pada grafik untuk reset zoom

### 4. Toggle Dataset
- Klik pada legend untuk hide/show data series

## 💡 Tips Penggunaan

### Untuk Analisis Jangka Pendek
- Gunakan 26 minggu historis + 2 minggu prediksi
- Lebih responsif terhadap perubahan tren terbaru

### Untuk Analisis Jangka Panjang
- Gunakan 52-104 minggu historis + 4-12 minggu prediksi
- Menangkap seasonality tahunan

### Interpretasi Confidence Interval
- **Interval Sempit**: Model confident dengan prediksi
- **Interval Lebar**: Banyak ketidakpastian (variasi data tinggi)

## ⚡ Performa

### Waktu Loading
- Data loading: < 1 detik
- Model training: 2-5 detik
- Render grafik: < 1 detik
- **Total**: ~3-7 detik

### Tips Mempercepat
- Kurangi jumlah minggu historis jika tidak perlu
- Gunakan browser modern (Chrome/Edge/Firefox)
- Pastikan tidak ada aplikasi berat lain yang berjalan

## 🔧 Teknologi yang Digunakan

### Backend
- **PHP Laravel**: Controller dan routing
- **Python Prophet**: Time series forecasting
- **MySQL**: Database storage
- **Laravel Process**: PHP-Python integration

### Frontend
- **Chart.js 4.4**: Library visualisasi
- **TailwindCSS**: Styling UI
- **JavaScript ES6**: Interaktivitas

### Data Science
- **pandas**: Data manipulation
- **numpy**: Numerical operations
- **Prophet**: Forecasting algorithm

## 🐛 Troubleshooting

### Grafik Tidak Muncul
**Solusi:**
1. Cek console browser (F12) untuk error
2. Pastikan data sudah di-aggregate (kunjungi halaman Ringkasan Mingguan)
3. Refresh halaman (Ctrl+F5)

### Prediksi Terlihat Aneh
**Kemungkinan Penyebab:**
- Data historis terlalu sedikit (< 12 minggu)
- Data memiliki outlier ekstrem
- Tren data tidak konsisten

**Solusi:**
- Tambah jumlah minggu historis
- Cek data di halaman Data Pengiriman

### Loading Lama
**Solusi:**
- Kurangi jumlah minggu historis
- Restart Laragon
- Check CPU usage

### Error "Failed to generate prediction"
**Solusi:**
1. Cek apakah Python terinstal: 
   ```powershell
   C:\laragon\bin\python\python-3.10\python.exe --version
   ```
2. Cek apakah library Prophet terinstal:
   ```powershell
   C:\laragon\bin\python\python-3.10\python.exe -m pip list | findstr prophet
   ```
3. Re-install requirements jika perlu

## 📊 Contoh Use Case

### Use Case 1: Perencanaan Sumber Daya
**Tujuan**: Estimasi kebutuhan petugas untuk 4 minggu ke depan

**Langkah:**
1. Pilih kecamatan target
2. Gunakan 52 minggu historis + 4 minggu prediksi
3. Lihat total prediksi di card statistik
4. Hitung: jumlah petugas = total prediksi / kapasitas per petugas

### Use Case 2: Analisis Tren Musiman
**Tujuan**: Identifikasi pola seasonal (libur, hari raya, dll)

**Langkah:**
1. Pilih kecamatan
2. Gunakan 104 minggu historis (2 tahun)
3. Amati pola berulang di grafik
4. Bandingkan dengan kalender hari libur

### Use Case 3: Evaluasi Performa
**Tujuan**: Bandingkan prediksi vs aktual

**Langkah:**
1. Lihat prediksi minggu lalu
2. Compare dengan data aktual minggu ini
3. Hitung error rate: `|actual - predicted| / actual * 100%`
4. Gunakan untuk improve planning

## 📚 Referensi

- [Prophet Documentation](https://facebook.github.io/prophet/)
- [Chart.js Documentation](https://www.chartjs.org/)
- [Time Series Forecasting Guide](https://otexts.com/fpp3/)

## 🎓 Pemahaman Model Prophet

### Komponen Model
1. **Trend**: Tren jangka panjang (naik/turun)
2. **Seasonality**: Pola berulang (musiman)
3. **Holidays**: Efek hari libur/special events
4. **Error**: Noise dan irregularitas

### Parameter yang Digunakan
```python
Prophet(
    yearly_seasonality=True,  # Deteksi pola tahunan
    weekly_seasonality=False, # Tidak gunakan pola mingguan
    daily_seasonality=False,  # Tidak gunakan pola harian
    changepoint_prior_scale=0.05,  # Fleksibilitas tren
    seasonality_prior_scale=10.0,  # Kekuatan seasonality
    interval_width=0.95  # 95% confidence interval
)
```

## ✨ Best Practices

1. **Selalu gunakan data terbaru**: Kunjungi Ringkasan Mingguan untuk update data
2. **Pertimbangkan context**: Lihat kalender untuk hari libur/event khusus
3. **Kombinasikan dengan intuisi**: Model adalah tools, keputusan tetap di tangan Anda
4. **Review berkala**: Update prediksi setiap minggu
5. **Dokumentasi insight**: Catat pola menarik untuk analisis mendalam

## 🎯 KPI yang Bisa Dimonitor

- Akurasi prediksi (MAPE, MAE, RMSE)
- Tren pertumbuhan/penurunan
- Stabilitas variance
- Dampak seasonality
- Efektivitas resource allocation

---

**Dibuat dengan ❤️ menggunakan Laravel + Prophet + Chart.js**
