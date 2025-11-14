# Analisis Kode: Prediksi Tren Pengiriman Paket Kota Malang

## 📋 Ringkasan Proyek

Proyek ini mengimplementasikan prediksi tren pengiriman paket di Kota Malang menggunakan metode **Facebook Prophet**. Data yang digunakan mencakup 4 tahun data pengiriman paket dengan total **965,003 records**.

---

## 🏗️ Struktur Kode

### 1. **Setup dan Import Libraries** (Cell 1-2)

```python
pip install prophet

import numpy as np
import pandas as pd
import seaborn as sns
import matplotlib.pyplot as plt
from prophet import Prophet
from sklearn.metrics import mean_squared_error, mean_absolute_error
```

**Fungsi:**
- Instalasi library Prophet untuk time series forecasting
- Import library untuk data manipulation (pandas, numpy)
- Import library untuk visualisasi (matplotlib, seaborn)
- Import metrics untuk evaluasi model

**Custom Function:**
```python
def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    return np.mean(np.abs((y_true - y_pred) / y_true)) * 100
```
- Implementasi MAPE (Mean Absolute Percentage Error) untuk evaluasi akurasi prediksi

---

### 2. **Data Loading** (Cell 3-4)

```python
df = pd.read_excel("/content/drive/MyDrive/Skripsi Frankie Steinlie/Implementasi Kode/DATA_ASLI_4 TAHUN_ORI.xlsx")
df.describe()
df.head(10)
```

**Data Overview:**
- **Total Records:** 965,003 paket
- **Periode Data:** 26 Desember 2020 - 29 Desember 2024 (4 tahun)
- **Kolom Utama:**
  - `SLA`: Service Level Agreement (2-6 hari)
  - `Kantor_Kirim`: Kode kantor pengirim
  - `Tgl_Kirim`: Tanggal pengiriman
  - `Tgl_Antaran_Pertama`: Tanggal pengantaran pertama
  - `Tgl_Update`: Tanggal update status
  - `Berat`: Berat paket (0.01 - 10 kg)
  - `Cek`: Flag untuk counting (nilai: 1)
  - `Kota`: Informasi kota dan kecamatan

---

### 3. **Data Preprocessing** (Cell 5-7)

#### 3.1 Seleksi Kolom
```python
df = df[['Kota', 'Cek', 'Tgl_Kirim']]
```
- Memilih hanya kolom yang diperlukan untuk analisis

#### 3.2 Ekstraksi Kecamatan
```python
df['Kecamatan'] = df['Kota'].apply(lambda x: x.split(',')[1].strip() if len(x.split(',')) > 1 else '')
```
- Memisahkan nama kecamatan dari kolom `Kota`
- Menggunakan comma (`,`) sebagai delimiter
- Handling untuk data yang tidak memiliki kecamatan

#### 3.3 Final Selection
```python
df = df[['Kecamatan', 'Cek', 'Tgl_Kirim']]
```
- Fokus pada kecamatan, count flag, dan tanggal pengiriman

---

### 4. **Agregasi Data** (Cell 8-9)

#### 4.1 Weekly Aggregation
```python
df_kecamatan_weekly = df.groupby('Kecamatan').resample('W', on='Tgl_Kirim')['Cek'].count().reset_index()
df_kecamatan_weekly.rename(columns={'Cek': 'total paket'}, inplace=True)
```

**Proses:**
- Group by kecamatan
- Resample data per minggu (weekly)
- Hitung jumlah paket per minggu per kecamatan
- Rename kolom untuk clarity

#### 4.2 Week Number
```python
df_kecamatan_weekly['minggu_ke'] = df_kecamatan_weekly['Tgl_Kirim'].dt.isocalendar().week.astype(int)
```
- Menambahkan kolom `minggu_ke` untuk identifikasi minggu dalam tahun (1-52/53)

---

### 5. **Data Export** (Cell 10)

```python
output_path = '/content/drive/MyDrive/Skripsi Frankie Steinlie/Implementasi Kode/df_kecamatan_weekly.xlsx'
df_kecamatan_weekly.to_excel(output_path, index=False)
```

**Output:**
- Export hasil agregasi ke file Excel
- File: `df_kecamatan_weekly.xlsx`
- Format: Weekly aggregated data per district

---

### 6. **Visualisasi Data** (Cell 11-12)

#### 6.1 Multi-District Trend
```python
plt.figure(figsize=(12, 6))
sns.lineplot(data=df_kecamatan_weekly, x='Tgl_Kirim', y='total paket', hue='Kecamatan')
plt.title('Tren Pengiriman Paket Perminggu Perkecamatan')
```

**Tujuan:**
- Visualisasi tren pengiriman untuk semua kecamatan dalam satu grafik
- Membandingkan pola antar kecamatan
- Identifikasi kecamatan dengan volume tinggi/rendah

#### 6.2 Individual District Trends
```python
unique_kecamatan = df_kecamatan_weekly['Kecamatan'].unique()

for kecamatan in unique_kecamatan:
    df_filtered = df_kecamatan_weekly[df_kecamatan_weekly['Kecamatan'] == kecamatan]
    
    plt.figure(figsize=(12, 6))
    sns.lineplot(data=df_filtered, x='Tgl_Kirim', y='total paket')
    plt.title(f'Tren Pengiriman Paket Perminggu di Kecamatan {kecamatan}')
```

**Tujuan:**
- Analisis detail per kecamatan
- Identifikasi pola seasonality dan trend spesifik
- Pemahaman karakteristik pengiriman per area

---

### 7. **Train-Test Split** (Cell 13)

```python
for kecamatan in unique_kecamatan:
    df_filtered_kecamatan = df_kecamatan_weekly[df_kecamatan_weekly['Kecamatan'] == kecamatan]
    
    # 52 minggu terakhir untuk testing
    split_point_kecamatan = len(df_filtered_kecamatan) - 52
    
    train_kecamatan = df_filtered_kecamatan.iloc[:split_point_kecamatan]
    test_kecamatan = df_filtered_kecamatan.iloc[split_point_kecamatan:]
```

**Strategi Split:**
- **Training Data:** Semua data kecuali 52 minggu terakhir
- **Testing Data:** 52 minggu terakhir (1 tahun)
- **Visualisasi:** Menampilkan pemisahan data latih dan uji

**Alasan:**
- Evaluasi kemampuan model memprediksi 1 tahun ke depan
- Temporal split (tidak random) untuk time series
- Setiap kecamatan diproses secara independen

---

## ✅ Kekuatan Kode

### 1. **Preprocessing yang Baik**
- ✅ Ekstraksi kecamatan dari kolom Kota dengan error handling
- ✅ Agregasi temporal yang tepat (weekly)
- ✅ Penambahan features (minggu_ke)

### 2. **Visualisasi Komprehensif**
- ✅ Multi-level visualization (all districts + individual)
- ✅ Train-test split visualization
- ✅ Consistent styling (ggplot + fivethirtyeight)

### 3. **Time Series Best Practices**
- ✅ Temporal split (bukan random split)
- ✅ 1 tahun data testing (reasonable holdout)
- ✅ Per-district modeling approach

### 4. **Code Organization**
- ✅ Clear cell structure
- ✅ Step-by-step processing
- ✅ Data export untuk reproducibility

---

## ⚠️ Area yang Bisa Diperbaiki

### 1. **Missing Prophet Implementation**
**Issue:** Code berhenti di train-test split, belum ada implementasi model Prophet

**Saran:**
```python
# Example implementation
for kecamatan in unique_kecamatan:
    df_filtered = df_kecamatan_weekly[df_kecamatan_weekly['Kecamatan'] == kecamatan]
    
    # Prepare data for Prophet (requires 'ds' and 'y' columns)
    prophet_df = df_filtered.rename(columns={'Tgl_Kirim': 'ds', 'total paket': 'y'})
    
    # Split
    train = prophet_df.iloc[:-52]
    test = prophet_df.iloc[-52:]
    
    # Train Prophet model
    model = Prophet()
    model.fit(train)
    
    # Make predictions
    future = model.make_future_dataframe(periods=52, freq='W')
    forecast = model.predict(future)
    
    # Evaluate
    y_true = test['y'].values
    y_pred = forecast.iloc[-52:]['yhat'].values
    
    mape = mean_absolute_percentage_error(y_true, y_pred)
    rmse = np.sqrt(mean_squared_error(y_true, y_pred))
    mae = mean_absolute_error(y_true, y_pred)
    
    print(f"Kecamatan {kecamatan}:")
    print(f"  MAPE: {mape:.2f}%")
    print(f"  RMSE: {rmse:.2f}")
    print(f"  MAE: {mae:.2f}")
```

### 2. **Hardcoded Paths**
**Issue:** Paths menggunakan Google Drive spesifik
```python
"/content/drive/MyDrive/Skripsi Frankie Steinlie/Implementasi Kode/DATA_ASLI_4 TAHUN_ORI.xlsx"
```

**Saran:**
```python
# Gunakan relative paths atau config
import os

DATA_DIR = "data"
INPUT_FILE = os.path.join(DATA_DIR, "DATA_ASLI_4_TAHUN_ORI.xlsx")
OUTPUT_FILE = os.path.join(DATA_DIR, "df_kecamatan_weekly.xlsx")

df = pd.read_excel(INPUT_FILE)
df_kecamatan_weekly.to_excel(OUTPUT_FILE, index=False)
```

### 3. **Error Handling**
**Issue:** Tidak ada try-except untuk file operations atau data issues

**Saran:**
```python
try:
    df = pd.read_excel(INPUT_FILE)
except FileNotFoundError:
    print(f"Error: File {INPUT_FILE} tidak ditemukan")
except Exception as e:
    print(f"Error reading file: {e}")
```

### 4. **Data Validation**
**Issue:** Tidak ada validasi untuk data kosong atau missing values

**Saran:**
```python
# Check for missing values
print("Missing values per column:")
print(df.isnull().sum())

# Check for empty kecamatan
empty_kecamatan = df[df['Kecamatan'] == '']
print(f"Records dengan kecamatan kosong: {len(empty_kecamatan)}")

# Handle missing data
df = df[df['Kecamatan'] != '']  # Remove empty districts
```

### 5. **Performance Optimization**
**Issue:** Loop untuk setiap kecamatan bisa di-optimize

**Saran:**
```python
# Vectorized operations lebih cepat dari loops
results = []
for kecamatan in unique_kecamatan:
    # Process and store results
    results.append({
        'kecamatan': kecamatan,
        'mape': mape,
        'rmse': rmse,
        'mae': mae
    })

results_df = pd.DataFrame(results)
```

### 6. **Documentation**
**Issue:** Kurang komentar dalam kode

**Saran:** Tambahkan docstrings dan comments untuk setiap major step

### 7. **Reproducibility**
**Issue:** Tidak ada random seed atau version tracking

**Saran:**
```python
# Add at the beginning
import random
np.random.seed(42)
random.seed(42)

# Version tracking
print(f"Prophet version: {prophet.__version__}")
print(f"Pandas version: {pd.__version__}")
```

---

## 📊 Statistik Data

### Data Overview:
- **Total Records:** 965,003 paket
- **Periode:** 2020-12-26 hingga 2024-12-29 (4 tahun)
- **Average SLA:** 2.28 hari
- **Average Weight:** 0.57 kg
- **Median Weight:** 0.10 kg
- **Date Range:**
  - Min: 2020-12-26
  - Max: 2024-12-29

### Karakteristik Data:
- Mayoritas paket memiliki SLA 2 hari
- Berat paket bervariasi dari 0.01 kg hingga 10 kg
- Data mencakup multiple kecamatan di Kota Malang

---

## 🎯 Rekomendasi Next Steps

### Immediate:
1. ✅ Implementasikan model Prophet lengkap
2. ✅ Tambahkan model evaluation metrics
3. ✅ Visualisasi hasil prediksi vs actual
4. ✅ Export hasil prediksi dan metrics

### Enhancement:
1. ✅ Hyperparameter tuning untuk Prophet
2. ✅ Cross-validation untuk time series
3. ✅ Comparison dengan model lain (ARIMA, LSTM)
4. ✅ Feature engineering (holidays, events)
5. ✅ Ensemble modeling

### Production:
1. ✅ Modularisasi kode (functions/classes)
2. ✅ Configuration management
3. ✅ Logging dan monitoring
4. ✅ API untuk serving predictions
5. ✅ Automated retraining pipeline

---

## 📝 Kesimpulan

**Kode saat ini adalah foundation yang solid** untuk analisis tren pengiriman paket. Preprocessing dan visualisasi sudah baik, namun **implementasi model Prophet masih perlu ditambahkan** untuk melengkapi tujuan prediksi.

**Overall Assessment:**
- **Data Processing:** ⭐⭐⭐⭐⭐ (5/5) - Excellent
- **Visualization:** ⭐⭐⭐⭐⭐ (5/5) - Excellent
- **Modeling:** ⭐⭐ (2/5) - Incomplete (setup ada, implementation belum)
- **Code Quality:** ⭐⭐⭐⭐ (4/5) - Good (perlu error handling & docs)
- **Reproducibility:** ⭐⭐⭐ (3/5) - Fair (hardcoded paths)

**Total Score:** ⭐⭐⭐⭐ (4/5) - **Good foundation, needs completion**

---

## 📚 Referensi

- **Prophet Documentation:** https://facebook.github.io/prophet/
- **Time Series Best Practices:** Temporal split, proper evaluation metrics
- **Pandas Resample:** Weekly aggregation dengan 'W' frequency
