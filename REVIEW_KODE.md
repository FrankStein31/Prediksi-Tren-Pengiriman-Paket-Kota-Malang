# 📊 Review Kode: Skripsi Pengiriman Paket

## 🎯 Tujuan Proyek
Memprediksi tren pengiriman paket di Kota Malang menggunakan **Facebook Prophet** berdasarkan data historis 4 tahun (965,003 records).

---

## 📁 Struktur File

```
Prediksi-Tren-Pengiriman-Paket-Kota-Malang/
├── README.md
├── Skripsi_Pengiriman_Paket.ipynb   # Main notebook (13 code cells)
├── CODE_ANALYSIS.md                  # Analisis detail (English)
└── REVIEW_KODE.md                    # Review ini (Indonesian)
```

---

## 🔍 Alur Kode (Flow Diagram)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. SETUP                                                        │
│    - Install Prophet                                            │
│    - Import libraries (pandas, numpy, matplotlib, seaborn)      │
│    - Define MAPE function                                       │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 2. LOAD DATA                                                    │
│    - Read Excel: DATA_ASLI_4_TAHUN_ORI.xlsx                    │
│    - 965,003 records (2020-2024)                               │
│    - Show statistics (describe, head)                           │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 3. DATA PREPROCESSING                                           │
│    - Select columns: Kota, Cek, Tgl_Kirim                     │
│    - Extract Kecamatan from Kota (split by comma)             │
│    - Final columns: Kecamatan, Cek, Tgl_Kirim                 │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 4. AGGREGATION                                                  │
│    - Group by: Kecamatan                                       │
│    - Resample: Weekly ('W')                                    │
│    - Count: Total paket per minggu per kecamatan              │
│    - Add: Week number (minggu_ke)                              │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 5. EXPORT RESULTS                                               │
│    - Save to: df_kecamatan_weekly.xlsx                         │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 6. VISUALIZATION                                                │
│    A. Multi-district trends (all kecamatan in one plot)        │
│    B. Individual district trends (one plot per kecamatan)      │
└────────────────────────────────┬────────────────────────────────┘
                                 │
┌────────────────────────────────▼────────────────────────────────┐
│ 7. TRAIN-TEST SPLIT                                            │
│    - Split: Last 52 weeks = Test, Rest = Train                │
│    - Visualize: Train vs Test data per kecamatan              │
│                                                                 │
│    ⚠️  STOP HERE - Model training belum diimplementasikan      │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📝 Penjelasan Tiap Cell

### **Cell 1: Install Prophet**
```python
pip install prophet
```
✅ Install library forecasting

---

### **Cell 2: Import & Setup**
```python
import numpy as np
import pandas as pd
import seaborn as sns
import matplotlib.pyplot as plt
from prophet import Prophet
from sklearn.metrics import mean_squared_error, mean_absolute_error

warnings.filterwarnings("ignore")
plt.style.use('ggplot')
plt.style.use('fivethirtyeight')

def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    return np.mean(np.abs((y_true - y_pred) / y_true)) * 100
```

**Yang Dilakukan:**
- Import semua library yang dibutuhkan
- Set style untuk plot agar lebih menarik
- Definisi fungsi MAPE untuk evaluasi model (error dalam persen)

---

### **Cell 3-4: Load Data**
```python
df = pd.read_excel("/content/drive/MyDrive/.../DATA_ASLI_4 TAHUN_ORI.xlsx")
df.describe()
df.head(10)
```

**Output Statistics:**
- **965,003 paket** dalam 4 tahun
- SLA rata-rata: **2.28 hari**
- Berat rata-rata: **0.57 kg**
- Periode: **26 Des 2020 - 29 Des 2024**

---

### **Cell 5: Seleksi Kolom Awal**
```python
df = df[['Kota', 'Cek', 'Tgl_Kirim']]
df.head(10)
```

**Fokus ke 3 kolom:**
1. `Kota` - Informasi lokasi (kota, kecamatan)
2. `Cek` - Flag untuk counting (selalu = 1)
3. `Tgl_Kirim` - Tanggal pengiriman

---

### **Cell 6: Ekstraksi Kecamatan**
```python
df['Kecamatan'] = df['Kota'].apply(
    lambda x: x.split(',')[1].strip() if len(x.split(',')) > 1 else ''
)
display(df)
```

**Proses:**
- Pisahkan string di kolom `Kota` menggunakan koma (`,`)
- Ambil bagian kedua = nama kecamatan
- Jika tidak ada koma, isi dengan string kosong
- **Error handling:** `if len(x.split(',')) > 1` mencegah IndexError

**Contoh:**
```
"Malang, Lowokwaru"  →  "Lowokwaru"
"Malang, Blimbing"   →  "Blimbing"
"Malang"             →  ""
```

---

### **Cell 7: Seleksi Final**
```python
df = df[['Kecamatan', 'Cek', 'Tgl_Kirim']]
display(df)
```

**Final DataFrame:**
| Kecamatan  | Cek | Tgl_Kirim  |
|-----------|-----|------------|
| Lowokwaru | 1   | 2021-01-05 |
| Blimbing  | 1   | 2021-01-05 |
| ...       | ... | ...        |

---

### **Cell 8: Agregasi Weekly**
```python
df_kecamatan_weekly = df.groupby('Kecamatan')\
                        .resample('W', on='Tgl_Kirim')['Cek']\
                        .count()\
                        .reset_index()

df_kecamatan_weekly.rename(columns={'Cek': 'total paket'}, inplace=True)
display(df_kecamatan_weekly)
```

**Proses Agregasi:**
1. **GroupBy** `Kecamatan` - Group per kecamatan
2. **Resample** `'W'` - Agregasi per minggu (Weekly)
3. **Count** `Cek` - Hitung jumlah paket
4. **Rename** - Ubah nama kolom jadi lebih jelas

**Output:**
| Kecamatan  | Tgl_Kirim  | total paket |
|-----------|------------|-------------|
| Blimbing  | 2021-01-03 | 450         |
| Blimbing  | 2021-01-10 | 523         |
| Lowokwaru | 2021-01-03 | 678         |
| ...       | ...        | ...         |

---

### **Cell 9: Tambah Week Number**
```python
df_kecamatan_weekly['minggu_ke'] = \
    df_kecamatan_weekly['Tgl_Kirim'].dt.isocalendar().week.astype(int)
display(df_kecamatan_weekly)
```

**Menambah kolom `minggu_ke`:**
- Minggu ke-1, ke-2, ..., ke-52/53 dalam setahun
- Berguna untuk analisis pola musiman (seasonality)

---

### **Cell 10: Export Data**
```python
output_path = '/content/drive/MyDrive/.../df_kecamatan_weekly.xlsx'
df_kecamatan_weekly.to_excel(output_path, index=False)
print(f"DataFrame 'df_kecamatan_weekly' berhasil di simpan di {output_path}")
```

**Output:** File Excel dengan data agregasi weekly

---

### **Cell 11: Visualisasi Multi-Kecamatan**
```python
plt.figure(figsize=(12, 6))
sns.lineplot(data=df_kecamatan_weekly, 
             x='Tgl_Kirim', 
             y='total paket', 
             hue='Kecamatan')
plt.title('Tren Pengiriman Paket Perminggu Perkecamatan')
plt.xlabel('Minggu Pengiriman')
plt.ylabel('Total Paket')
plt.legend(title='Kecamatan')
plt.grid(True)
plt.tight_layout()
plt.show()
```

**Tujuan:**
- Lihat semua kecamatan dalam satu grafik
- Bandingkan volume pengiriman antar kecamatan
- Identifikasi kecamatan dengan tren tertinggi/terendah

---

### **Cell 12: Visualisasi Per-Kecamatan**
```python
unique_kecamatan = df_kecamatan_weekly['Kecamatan'].unique()

for kecamatan in unique_kecamatan:
    df_filtered = df_kecamatan_weekly[
        df_kecamatan_weekly['Kecamatan'] == kecamatan
    ]
    
    plt.figure(figsize=(12, 6))
    sns.lineplot(data=df_filtered, x='Tgl_Kirim', y='total paket')
    plt.title(f'Tren Pengiriman Paket Perminggu di Kecamatan {kecamatan}')
    plt.xlabel('Minggu Pengiriman')
    plt.ylabel('Total Paket')
    plt.grid(True)
    plt.tight_layout()
    plt.show()
```

**Tujuan:**
- Analisis detail per kecamatan
- Lihat pola unik setiap area
- Identifikasi seasonality dan trend

---

### **Cell 13: Train-Test Split**
```python
unique_kecamatan = df_kecamatan_weekly['Kecamatan'].unique()

for kecamatan in unique_kecamatan:
    df_filtered_kecamatan = df_kecamatan_weekly[
        df_kecamatan_weekly['Kecamatan'] == kecamatan
    ]
    
    # 52 minggu terakhir = Test
    split_point_kecamatan = len(df_filtered_kecamatan) - 52
    
    # Split data
    train_kecamatan = df_filtered_kecamatan.iloc[:split_point_kecamatan]
    test_kecamatan = df_filtered_kecamatan.iloc[split_point_kecamatan:]
    
    # Visualisasi
    plt.figure(figsize=(12, 6))
    sns.lineplot(data=train_kecamatan, x='Tgl_Kirim', y='total paket', 
                 label='Data Latih')
    sns.lineplot(data=test_kecamatan, x='Tgl_Kirim', y='total paket', 
                 label='Data Uji')
    plt.title(f'Pemisahan Data Latih dan Uji untuk Kecamatan {kecamatan}')
    plt.xlabel('Tanggal Kirim')
    plt.ylabel('Total Paket')
    plt.legend()
    plt.grid(True)
    plt.tight_layout()
    plt.show()
```

**Strategi Split:**
- **Training:** Semua data KECUALI 52 minggu terakhir
- **Testing:** 52 minggu terakhir (1 tahun)
- **Alasan:** Evaluasi prediksi 1 tahun ke depan

**⚠️ PENTING:** Code berhenti di sini - belum ada training Prophet!

---

## ✅ Kelebihan Kode

1. **✅ Data Processing Bagus**
   - Ekstraksi kecamatan dengan error handling
   - Agregasi temporal yang benar (weekly)
   - Feature engineering (minggu_ke)

2. **✅ Visualisasi Lengkap**
   - Multi-level: all districts + individual
   - Train-test visualization
   - Consistent styling

3. **✅ Time Series Best Practice**
   - Temporal split (bukan random)
   - Reasonable holdout period (1 tahun)
   - Per-district approach

4. **✅ Code Terstruktur**
   - Step-by-step jelas
   - Export intermediate results
   - Easy to follow

---

## ⚠️ Yang Perlu Diperbaiki

### 1. **Model Prophet Belum Diimplementasikan** ❌

**Masalah:** Kode berhenti di train-test split. Prophet belum dipakai!

**Solusi: Tambahkan Cell Baru**

```python
# CELL BARU: Train & Evaluate Prophet Model

results = []

for kecamatan in unique_kecamatan:
    print(f"\n{'='*60}")
    print(f"Processing: {kecamatan}")
    print('='*60)
    
    # Filter data
    df_filtered = df_kecamatan_weekly[
        df_kecamatan_weekly['Kecamatan'] == kecamatan
    ]
    
    # Prepare for Prophet (needs 'ds' and 'y' columns)
    prophet_df = df_filtered.rename(columns={
        'Tgl_Kirim': 'ds', 
        'total paket': 'y'
    })[['ds', 'y']]
    
    # Split
    split_point = len(prophet_df) - 52
    train = prophet_df.iloc[:split_point]
    test = prophet_df.iloc[split_point:]
    
    # Train Model
    model = Prophet(
        yearly_seasonality=True,
        weekly_seasonality=True,
        daily_seasonality=False
    )
    model.fit(train)
    
    # Predict
    future = model.make_future_dataframe(periods=52, freq='W')
    forecast = model.predict(future)
    
    # Get test predictions
    y_true = test['y'].values
    y_pred = forecast.iloc[-52:]['yhat'].values
    
    # Calculate metrics
    mape = mean_absolute_percentage_error(y_true, y_pred)
    rmse = np.sqrt(mean_squared_error(y_true, y_pred))
    mae = mean_absolute_error(y_true, y_pred)
    
    # Store results
    results.append({
        'Kecamatan': kecamatan,
        'MAPE (%)': round(mape, 2),
        'RMSE': round(rmse, 2),
        'MAE': round(mae, 2),
        'Train Size': len(train),
        'Test Size': len(test)
    })
    
    # Print results
    print(f"MAPE: {mape:.2f}%")
    print(f"RMSE: {rmse:.2f}")
    print(f"MAE: {mae:.2f}")
    
    # Plot Prediction vs Actual
    plt.figure(figsize=(14, 6))
    
    # Plot historical
    plt.plot(train['ds'], train['y'], label='Training Data', color='blue')
    plt.plot(test['ds'], test['y'], label='Actual (Test)', 
             color='green', linewidth=2)
    
    # Plot prediction
    plt.plot(forecast.iloc[-52:]['ds'], forecast.iloc[-52:]['yhat'], 
             label='Prediction', color='red', linewidth=2, linestyle='--')
    
    # Confidence interval
    plt.fill_between(forecast.iloc[-52:]['ds'],
                     forecast.iloc[-52:]['yhat_lower'],
                     forecast.iloc[-52:]['yhat_upper'],
                     alpha=0.3, color='red', label='Confidence Interval')
    
    plt.title(f'Prediksi vs Actual - {kecamatan}')
    plt.xlabel('Tanggal')
    plt.ylabel('Total Paket')
    plt.legend()
    plt.grid(True)
    plt.tight_layout()
    plt.show()
    
    # Plot components
    model.plot_components(forecast)
    plt.show()

# Summary table
results_df = pd.DataFrame(results)
print("\n" + "="*60)
print("SUMMARY RESULTS")
print("="*60)
display(results_df)

# Save results
results_df.to_excel('/content/drive/MyDrive/.../model_evaluation_results.xlsx', 
                    index=False)
```

---

### 2. **Hardcoded Paths** ❌

**Masalah:**
```python
"/content/drive/MyDrive/Skripsi Frankie Steinlie/Implementasi Kode/DATA_ASLI_4 TAHUN_ORI.xlsx"
```
- Path spesifik ke Google Drive
- Sulit direplikasi di mesin lain

**Solusi:**
```python
# Di awal notebook
import os

# Configuration
BASE_DIR = "/content/drive/MyDrive/Skripsi Frankie Steinlie/Implementasi Kode"
DATA_DIR = os.path.join(BASE_DIR, "data")
OUTPUT_DIR = os.path.join(BASE_DIR, "output")

# File paths
INPUT_FILE = os.path.join(DATA_DIR, "DATA_ASLI_4_TAHUN_ORI.xlsx")
WEEKLY_OUTPUT = os.path.join(OUTPUT_DIR, "df_kecamatan_weekly.xlsx")
RESULTS_OUTPUT = os.path.join(OUTPUT_DIR, "model_evaluation_results.xlsx")

# Buat direktori jika belum ada
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Load data
df = pd.read_excel(INPUT_FILE)
```

---

### 3. **Missing Error Handling** ❌

**Solusi:**
```python
# File reading dengan error handling
try:
    df = pd.read_excel(INPUT_FILE)
    print(f"✅ Data loaded: {len(df)} records")
except FileNotFoundError:
    print(f"❌ Error: File not found at {INPUT_FILE}")
    raise
except Exception as e:
    print(f"❌ Error reading file: {e}")
    raise

# Check missing values
print("\nMissing values:")
print(df.isnull().sum())

# Check empty kecamatan
empty_kec = df[df['Kecamatan'] == '']
if len(empty_kec) > 0:
    print(f"\n⚠️  Warning: {len(empty_kec)} records with empty Kecamatan")
    df = df[df['Kecamatan'] != '']  # Remove them
    print(f"   Removed. New total: {len(df)} records")
```

---

### 4. **No Data Validation** ❌

**Tambahkan:**
```python
# Validate data after loading
def validate_data(df):
    print("Data Validation:")
    print(f"  Total records: {len(df)}")
    print(f"  Date range: {df['Tgl_Kirim'].min()} to {df['Tgl_Kirim'].max()}")
    print(f"  Unique Kecamatan: {df['Kecamatan'].nunique()}")
    print(f"  Missing values: {df.isnull().sum().sum()}")
    
    # Check for duplicates
    duplicates = df.duplicated().sum()
    if duplicates > 0:
        print(f"  ⚠️  Duplicates found: {duplicates}")
    
    # Check date format
    if not pd.api.types.is_datetime64_any_dtype(df['Tgl_Kirim']):
        print("  ⚠️  Tgl_Kirim is not datetime type")
        df['Tgl_Kirim'] = pd.to_datetime(df['Tgl_Kirim'])
    
    return df

df = validate_data(df)
```

---

### 5. **Kurang Documentation** ❌

**Tambahkan di tiap cell:**
```python
"""
Cell Purpose: Ekstraksi nama kecamatan dari kolom Kota

Input: 
  - df dengan kolom 'Kota' (format: "Kota, Kecamatan")

Output:
  - df dengan kolom baru 'Kecamatan'

Example:
  "Malang, Lowokwaru" → "Lowokwaru"
"""
```

---

## 🎯 Checklist Perbaikan

### Must Have (Prioritas Tinggi):
- [ ] **Implementasi Prophet model** - Training & prediction
- [ ] **Model evaluation** - MAPE, RMSE, MAE
- [ ] **Prediction visualization** - Actual vs predicted
- [ ] **Results export** - Save metrics dan predictions

### Should Have (Prioritas Sedang):
- [ ] **Error handling** - Try-catch untuk file operations
- [ ] **Data validation** - Check missing, duplicates, format
- [ ] **Config management** - Centralized paths
- [ ] **Documentation** - Comments dan docstrings

### Nice to Have (Prioritas Rendah):
- [ ] **Hyperparameter tuning** - Optimize Prophet parameters
- [ ] **Cross-validation** - Time series CV
- [ ] **Model comparison** - ARIMA, LSTM, etc.
- [ ] **Feature engineering** - Holidays, events
- [ ] **Code modularization** - Functions/classes

---

## 📈 Expected Final Output

Setelah perbaikan, hasil akhir harus include:

1. **Excel Files:**
   - `df_kecamatan_weekly.xlsx` - Weekly aggregated data
   - `model_evaluation_results.xlsx` - Metrics per kecamatan
   - `predictions_52_weeks.xlsx` - Predictions untuk 52 minggu

2. **Visualizations:**
   - Tren historical semua kecamatan
   - Tren per kecamatan
   - Train-test split
   - **Prediction vs Actual** (dengan confidence interval)
   - **Prophet components** (trend, yearly, weekly)

3. **Metrics per Kecamatan:**
   - MAPE (%)
   - RMSE
   - MAE
   - Training size
   - Test size

---

## 🏆 Final Score

| Aspek               | Score | Komentar                          |
|--------------------|-------|-----------------------------------|
| Data Processing    | 5/5   | ✅ Excellent - Clean & systematic |
| Visualization      | 5/5   | ✅ Excellent - Comprehensive      |
| Model Implementation | 2/5 | ⚠️  Incomplete - Setup only       |
| Code Quality       | 4/5   | ⭐ Good - Needs error handling   |
| Documentation      | 3/5   | 📝 Fair - Needs more comments    |
| **TOTAL**          | **3.8/5** | **Good foundation, needs completion** |

---

## 💡 Kesimpulan

**Kode Anda sudah sangat bagus untuk tahap preprocessing dan exploratory analysis!** 

Data processing rapi, visualisasi lengkap, dan sudah prepare train-test split dengan benar. 

**Yang kurang:** Implementasi Prophet model itu sendiri (training, prediction, evaluation).

**Next step:** Fokus complete Prophet implementation dengan code yang saya berikan di atas. Setelah itu, kode siap untuk submission skripsi! 🎓

---

## 📞 Bantuan Lebih Lanjut

Jika perlu bantuan implementasi Prophet atau ada pertanyaan, silakan tanya! 😊
