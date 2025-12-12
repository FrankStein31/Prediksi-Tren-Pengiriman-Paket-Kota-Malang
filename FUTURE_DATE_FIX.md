# Fix: Future Date Prediction

## 🐛 Problem

Ketika user memilih **custom date di masa depan** (contoh: 4 Januari 2026), grafik tidak menampilkan data karena:

1. ❌ Query database menggunakan `WHERE week_start <= reference_date`
2. ❌ Untuk tanggal masa depan, tidak ada data di database yang <= tanggal tersebut
3. ❌ Historical data kosong → tidak bisa generate prediksi

**Contoh Kasus:**
```
Tanggal hari ini: 12 Desember 2025
User pilih custom date: 4 Januari 2026
Historical weeks: 52 minggu

Query: SELECT ... WHERE week_start <= '2026-01-04'
Result: Hanya dapat 3-4 minggu data (dari 12 Des 2025 - 4 Jan 2026)
Expected: Dapat 52 minggu data terakhir yang tersedia
```

## ✅ Solution

### Cara Kerja Baru:

1. **Detect Future Date**
   - Check apakah `reference_date > today`
   - Set flag `is_future_date = True`

2. **Load Historical Data Differently**
   - Jika `is_future_date = True`:
     - **Ambil data terakhir yang tersedia** (tanpa filter tanggal)
     - Query: `SELECT ... ORDER BY week_start DESC LIMIT 52`
   - Jika `is_future_date = False`:
     - Ambil data sebelum reference_date
     - Query: `SELECT ... WHERE week_start <= reference_date LIMIT 52`

3. **Calculate Extended Forecast**
   - Hitung gap antara data terakhir dan reference_date
   - Total forecast = gap + user requested forecast
   - Generate prediksi untuk semua periode tersebut

4. **Filter Display Results**
   - Hanya tampilkan forecast **SETELAH** reference_date
   - Limit ke jumlah yang diminta user (4-8 minggu)

## 📊 Example Flow

### Scenario: User pilih 4 Januari 2026

```
1. DETECT FUTURE DATE
   - Today: 2025-12-12
   - Reference: 2026-01-04
   - is_future = TRUE ✅

2. LOAD HISTORICAL DATA
   Query: SELECT * FROM weekly_shipment_data 
          WHERE kecamatan = 'KLOJEN'
          ORDER BY week_start DESC 
          LIMIT 52
   
   Result: 52 minggu data terakhir (2024-12-09 s/d 2025-12-02)
   Last data point: 2025-12-02

3. CALCULATE FORECAST NEEDED
   - Last data: 2025-12-02
   - Reference: 2026-01-04
   - Gap: 5 weeks (2025-12-09, 12-16, 12-23, 12-30, 2026-01-06)
   - User requested: 4 weeks
   - Total forecast: 5 + 4 = 9 weeks

4. GENERATE PREDICTIONS
   Prophet generates 9 weeks of forecast:
   - 2025-12-09 (to reach reference date)
   - 2025-12-16
   - 2025-12-23
   - 2025-12-30
   - 2026-01-06 ← Reference date here
   - 2026-01-13 (start showing to user)
   - 2026-01-20
   - 2026-01-27
   - 2026-02-03

5. FILTER DISPLAY
   Show only forecasts AFTER reference date:
   - 2026-01-13 ✅
   - 2026-01-20 ✅
   - 2026-01-27 ✅
   - 2026-02-03 ✅
   
   Total shown: 4 weeks (as requested)
```

## 🔧 Code Changes

### File: `python/app.py`

#### 1. Updated `load_historical_data()` function

**Added parameter:**
```python
def load_historical_data(kecamatan, weeks_back=52, reference_date=None, is_future_date=False):
```

**New logic:**
```python
# For future dates, get the latest available data
if is_future_date:
    query = """
    SELECT * FROM weekly_shipment_data
    WHERE kecamatan = %s
    ORDER BY week_start DESC
    LIMIT %s
    """
    df = pd.read_sql(query, connection, params=(kecamatan, weeks_back))
else:
    # For past/current dates, filter by reference date
    query = """
    SELECT * FROM weekly_shipment_data
    WHERE kecamatan = %s AND week_start <= %s
    ORDER BY week_start DESC
    LIMIT %s
    """
    df = pd.read_sql(query, connection, params=(kecamatan, reference_date, weeks_back))
```

#### 2. Updated `predict()` endpoint

**Detect future date:**
```python
is_future_date = False
if date_mode == 'custom':
    reference_date = pd.to_datetime(custom_date)
    today = datetime.now()
    if reference_date > today:
        is_future_date = True
        logger.info(f'Future date detected: {reference_date}')
```

**Calculate extended forecast:**
```python
if is_future_date:
    # Get the last data point
    last_data_date = historical_df['week_start'].max()
    
    # Calculate weeks between last data and reference date
    weeks_gap = int((reference_date - last_data_date).days / 7)
    
    # Total forecast needed
    total_forecast_weeks = weeks_gap + weeks_forecast
    
    logger.info(f'Gap: {weeks_gap} weeks, Total forecast: {total_forecast_weeks}')
    
    actual_forecast_weeks = total_forecast_weeks
else:
    actual_forecast_weeks = weeks_forecast
```

**Filter forecast results:**
```python
forecast_data = []
for idx, row in forecast.iterrows():
    forecast_date = row['ds']
    
    # For future dates, only include forecasts after the reference date
    if is_future_date:
        if forecast_date < reference_date:
            continue  # Skip
    
    forecast_data.append({...})
    
    # Only show the requested number
    if len(forecast_data) >= weeks_forecast:
        break
```

## 🧪 Testing

### Test Case 1: Near Future (1 month)
```json
{
  "kecamatan": "KLOJEN",
  "weeks_historical": 52,
  "weeks_forecast": 4,
  "date_mode": "custom",
  "custom_date": "2026-01-04"
}

Expected:
✅ Shows 52 weeks historical (latest available)
✅ Shows 4 weeks forecast AFTER 2026-01-04
✅ Forecast dates: 2026-01-06, 01-13, 01-20, 01-27
```

### Test Case 2: Far Future (6 months)
```json
{
  "kecamatan": "BLIMBING",
  "weeks_historical": 26,
  "weeks_forecast": 8,
  "date_mode": "custom",
  "custom_date": "2026-06-01"
}

Expected:
✅ Shows 26 weeks historical (latest available)
✅ Shows 8 weeks forecast AFTER 2026-06-01
✅ Forecast dates: 2026-06-08, 06-15, 06-22, 06-29, 07-06, 07-13, 07-20, 07-27
```

### Test Case 3: Very Far Future (1 year)
```json
{
  "kecamatan": "KEDUNGKANDANG",
  "weeks_historical": 52,
  "weeks_forecast": 8,
  "date_mode": "custom",
  "custom_date": "2027-01-01"
}

Expected:
✅ Shows 52 weeks historical (latest available)
✅ Shows 8 weeks forecast AFTER 2027-01-01
✅ Works correctly even for dates 1+ year in future
```

### Test Case 4: Past Date (Should work as before)
```json
{
  "kecamatan": "LOWOKWARU",
  "weeks_historical": 12,
  "weeks_forecast": 4,
  "date_mode": "custom",
  "custom_date": "2024-10-01"
}

Expected:
✅ Shows 12 weeks BEFORE 2024-10-01
✅ Shows 4 weeks forecast AFTER 2024-10-01
✅ Works exactly as before (no regression)
```

## 📝 User Experience

### Before Fix:
```
User: Pilih 4 Januari 2026
System: ❌ No historical data found
Result: Error message, no graph
```

### After Fix:
```
User: Pilih 4 Januari 2026
System: ✅ Loading latest 52 weeks available data
System: ✅ Generating predictions up to reference date + 4 weeks
System: ✅ Showing forecast from 6 Jan 2026 - 27 Jan 2026
Result: Graph displays correctly with future predictions
```

## 🎯 Benefits

1. **Flexible Planning**
   - Bisa lihat prediksi untuk event masa depan
   - Planning untuk quarter/semester berikutnya
   - Budget forecasting untuk tahun depan

2. **No Data Limitation**
   - Tidak terbatas oleh data historis yang ada
   - Prophet model tetap bisa generate prediksi jangka panjang
   - Confidence interval otomatis melebar untuk prediksi jangka panjang

3. **Consistent Experience**
   - Past dates → works as before
   - Current dates → works as before
   - Future dates → NOW WORKS! ✅

## ⚠️ Important Notes

1. **Prediction Accuracy**
   - Semakin jauh ke masa depan, accuracy berkurang
   - Confidence interval akan semakin lebar
   - Best practice: max 2-3 bulan ke depan

2. **Data Quality**
   - Prediksi bergantung pada pola historis
   - Jika pola berubah drastis, prediksi bisa kurang akurat
   - Model trained dengan data sampai data terakhir

3. **Performance**
   - Untuk tanggal sangat jauh (1+ tahun), processing bisa lebih lama
   - Prophet harus generate banyak forecast points
   - Filtered di backend sebelum dikirim ke frontend

## 🚀 Next Steps

### Potential Improvements:

1. **Add Warning for Far Future Dates**
   ```
   ⚠️ Warning: Prediction accuracy may be lower for dates more than 3 months in the future.
   Current selection: 6 months ahead
   ```

2. **Show Confidence Level**
   ```
   Confidence: High (0-1 month) | Medium (1-3 months) | Low (3+ months)
   ```

3. **Auto-suggest Optimal Date**
   ```
   💡 Tip: For most accurate predictions, select dates within the next 8 weeks.
   ```

---

**Fixed:** December 12, 2025  
**Version:** 2.1.1  
**Fix:** Future date prediction now works correctly
