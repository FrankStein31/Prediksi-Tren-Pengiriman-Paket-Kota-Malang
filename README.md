# 📦 Prediksi Pengiriman Paket Menggunakan Metode Prophet

Sistem ini merupakan aplikasi **berbasis web** yang digunakan untuk melakukan **prediksi jumlah pengiriman paket mingguan** menggunakan **Facebook Prophet**, serta memberikan **rekomendasi jumlah kurir** berdasarkan kapasitas kerja harian.

---

## 🚀 Fitur Utama

- 📊 Visualisasi data pengiriman historis
- 📈 Prediksi pengiriman paket mingguan (Prophet)
- 👷 Rekomendasi jumlah kurir (min & max)
- 📂 Upload & manajemen data pengiriman
- 🔍 Perbandingan model prediksi
- 📜 Riwayat upload data

---

## 🧠 Teknologi yang Digunakan

| Komponen | Teknologi |
|--------|----------|
| Backend | Python (Flask API) |
| Forecasting | Prophet |
| Framework | Laravel |
| Visualisasi | Chart.js |
| Database | MySQL |

---

## Tambahkan ini di gitattributes untuk penggunaan LFS
python/models/*.pkl filter=lfs diff=lfs merge=lfs -text <br>
*.pkl filter=lfs diff=lfs merge=lfs -text <br>
*.csv filter=lfs diff=lfs merge=lfs -text <br>
*.xlsx filter=lfs diff=lfs merge=lfs -text <br>
*.xls filter=lfs diff=lfs merge=lfs -text <br>
*.sql filter=lfs diff=lfs merge=lfs -text <br>

---

## 🖥️ Tampilan Antarmuka Aplikasi

### 🏠 Dashboard Utama
Menampilkan ringkasan data pengiriman, grafik tren, serta insight awal kondisi pengiriman.

<p align="center">
  <img src="https://github.com/user-attachments/assets/a0d12ae3-7798-4180-a39b-aebc7e52437e" width="90%">
</p>

---

### 📦 Halaman Data Pengiriman
Menampilkan seluruh data pengiriman historis yang digunakan sebagai dasar perhitungan prediksi.

<p align="center">
  <img src="https://github.com/user-attachments/assets/11311c44-07c5-48e4-b230-387e62150c34" width="90%">
</p>

---

### ⬆️ Upload Data Pengiriman Baru
Digunakan untuk menambahkan data pengiriman terbaru ke dalam sistem.

<p align="center">
  <img src="https://github.com/user-attachments/assets/b229d4d7-744a-43c0-b5d8-cbe6a1af1323" width="90%">
</p>

---

### 🕒 Riwayat Upload Data
Menyajikan histori upload data pengiriman untuk keperluan audit dan validasi.

<p align="center">
  <img src="https://github.com/user-attachments/assets/7e00f192-daea-4854-8389-9fcbe69e5656" width="90%">
</p>

---

### 📈 Halaman Prediksi Pengiriman
Menampilkan hasil prediksi pengiriman paket mingguan beserta rekomendasi jumlah kurir berdasarkan kapasitas kerja.

<p align="center">
  <img src="https://github.com/user-attachments/assets/a36c2235-ba8c-4da6-99da-62f876eba9f5" width="90%">
</p>

---

### ⚖️ Perbandingan Model Prediksi
Menampilkan perbandingan performa model prediksi melalui route:

---
/model-explanation
<p align="center">
    <img src="https://github.com/user-attachments/assets/606acce5-2a7e-43b0-9bf2-8b2bfb5d054d" width="90%">
</p>

---
### 📌 Metodologi Prediksi
Model Prophet membentuk prediksi berdasarkan persamaan: <br>
y(t) = g(t) + s(t) + h(t)
- g(t) : Tren
- s(t) : Musiman
- h(t) : Hari libur
<br> Hasil prediksi kemudian digunakan untuk menentukan rekomendasi jumlah kurir berdasarkan:
- Kapasitas hari normal
- Kapasitas hari libur
- Nilai minimum & maksimum paket per kurir

---
### 🎯 Tujuan Sistem
- Membantu perusahaan ekspedisi dalam perencanaan SDM
- Mengurangi risiko kekurangan atau kelebihan kurir
- Memberikan dukungan keputusan berbasis data

---
### 👨‍💻 Author - Frankie Steinlie <br>
🎓 Sarjana Komputer <br>
📍 Indonesia

---
### ⭐ Catatan <br>
Jika repository ini bermanfaat, jangan lupa untuk memberikan star ⭐
