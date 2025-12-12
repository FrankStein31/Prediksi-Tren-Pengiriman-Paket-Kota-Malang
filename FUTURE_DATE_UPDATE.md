# Update: Future Date Support & Week-based Real-time

## 🔄 Changes Made (December 12, 2025)

### 1. Custom Date - Future Date Support

**Previous Behavior:**
- ❌ Custom date dibatasi sampai hari ini saja (`max = today`)
- ❌ Tidak bisa pilih tanggal masa depan

**New Behavior:**
- ✅ **Tanggal masa depan DIPERBOLEHKAN**
- ✅ Tidak ada batasan max date
- ✅ User bisa pilih tanggal berapa saja untuk analisis "what-if"

**Use Case:**
```
Scenario: User ingin melihat prediksi untuk 1 bulan ke depan
- Pilih Custom Date: 2026-01-15
- Historical: 52 minggu sebelum 2026-01-15
- Forecast: 4 minggu setelah 2026-01-15
Result: Menampilkan prediksi sampai 2026-02-12
```

### 2. Real-time Mode - Week-based Reference

**Previous Behavior:**
- ❌ Menggunakan hari ini (`datetime.now()`)
- ❌ Bisa di tengah-tengah minggu, tidak konsisten

**New Behavior:**
- ✅ **Menggunakan awal minggu ini (Senin)**
- ✅ Konsisten dengan data mingguan
- ✅ Prediksi selalu mulai dari minggu depan

**Implementation:**
```python
# Get start of current week (Monday)
today = datetime.now()
start_of_week = today - timedelta(days=today.weekday())
reference_date = start_of_week
```

**Example:**
```
Jika hari ini: 12 Desember 2025 (Jumat)
Reference date: 8 Desember 2025 (Senin)
Historical: 52 minggu sebelum 8 Des 2025
Forecast: 4 minggu setelah 8 Des 2025 (mulai 15 Des 2025)
```

## 📊 Updated Flow

### Real-time Mode:
```
User clicks "Real-time"
    ↓
System calculates start of current week
    ↓
reference_date = Monday of this week
    ↓
Load 52 weeks before Monday
    ↓
Predict 4 weeks after Monday
    ↓
Display results
```

### Custom Mode (Future Date):
```
User clicks "Custom Date"
    ↓
User selects: 2026-06-15
    ↓
reference_date = 2026-06-15
    ↓
Load 52 weeks before 2026-06-15
    ↓
Predict 4 weeks after 2026-06-15
    ↓
Display results (prediksi sampai 2026-07-13)
```

## 🎯 Benefits

### 1. For Prediction Analysis
- ✅ Bisa simulasi prediksi jangka panjang
- ✅ Analisis "what-if" untuk masa depan
- ✅ Planning untuk bulan/tahun depan

### 2. For Data Consistency
- ✅ Real-time selalu pakai awal minggu
- ✅ Tidak ada data "di tengah minggu"
- ✅ Lebih mudah compare antar periode

### 3. For Business Planning
- ✅ Forecast untuk event masa depan
- ✅ Capacity planning jangka panjang
- ✅ Budget planning berdasarkan prediksi

## 🔧 Technical Changes

### File: `visualisasi.blade.php`

**Changed:**
```javascript
// BEFORE:
document.getElementById('custom-date').max = today;

// AFTER:
// Removed max date restriction to allow future dates
// document.getElementById('custom-date').max = today;
```

**UI Text Updated:**
```html
<!-- BEFORE -->
Real-time (Hari Ini)

<!-- AFTER -->
Real-time (Minggu Ini)
```

### File: `app.py` & `visualize_prophet.py`

**Changed:**
```python
# BEFORE:
if reference_date is None:
    reference_date = datetime.now()

# AFTER:
if reference_date is None:
    # Get start of current week (Monday)
    today = datetime.now()
    start_of_week = today - timedelta(days=today.weekday())
    reference_date = start_of_week
```

**Custom Date Validation:**
```python
# BEFORE:
reference_date = pd.to_datetime(custom_date)
# Had restrictions for future dates

# AFTER:
reference_date = pd.to_datetime(custom_date)
# Allow future dates - no restriction
```

## 📝 Usage Examples

### Example 1: Real-time (Current Week)
```json
{
  "kecamatan": "BLIMBING",
  "weeks_historical": 52,
  "weeks_forecast": 4,
  "date_mode": "realtime"
}

Result:
- Reference: 2025-12-08 (Monday this week)
- Historical: 2024-12-09 to 2025-12-08 (52 weeks)
- Forecast: 2025-12-15 to 2026-01-05 (4 weeks)
```

### Example 2: Custom Date (Future)
```json
{
  "kecamatan": "KEDUNGKANDANG",
  "weeks_historical": 26,
  "weeks_forecast": 8,
  "date_mode": "custom",
  "custom_date": "2026-06-01"
}

Result:
- Reference: 2026-06-01
- Historical: 2025-12-08 to 2026-06-01 (26 weeks)
- Forecast: 2026-06-08 to 2026-07-27 (8 weeks)
```

### Example 3: Long-term Forecast
```json
{
  "kecamatan": "KLOJEN",
  "weeks_historical": 52,
  "weeks_forecast": 8,
  "date_mode": "custom",
  "custom_date": "2027-01-01"
}

Result:
- Reference: 2027-01-01
- Historical: 2026-01-05 to 2027-01-01 (52 weeks)
- Forecast: 2027-01-08 to 2027-02-26 (8 weeks)
```

## ⚠️ Important Notes

### 1. Data Availability
- Historical data hanya tersedia sampai data terakhir di database
- Jika pilih tanggal masa depan terlalu jauh, historical data bisa kurang dari yang diminta

### 2. Prediction Accuracy
- Prediksi masa depan bergantung pada pola historis
- Semakin jauh prediksi, confidence interval semakin lebar
- Best practice: gunakan max 8 minggu forecast

### 3. Week Alignment
- Real-time mode selalu align ke awal minggu (Senin)
- Custom date bisa pilih tanggal apa saja
- System akan ambil data mingguan terdekat

## 🧪 Testing Checklist

- [x] Real-time mode menggunakan start of week
- [x] Custom date tidak ada max restriction
- [x] Bisa pilih tanggal masa depan (2026, 2027, dst)
- [x] Historical data load correctly
- [x] Forecast generate correctly for future dates
- [x] UI shows correct date info
- [x] Error handling for invalid dates
- [x] Statistics calculate correctly

## 🚀 Future Enhancements

### Possible Additions:
1. **Date Presets**
   - Next Quarter (3 bulan ke depan)
   - Next Half Year (6 bulan ke depan)
   - Next Year (1 tahun ke depan)

2. **Smart Date Picker**
   - Suggest optimal forecast dates
   - Highlight weeks with events/holidays
   - Show data availability indicator

3. **Comparison Mode**
   - Compare predictions from different reference dates
   - Track prediction accuracy over time
   - Adjust model based on actual vs predicted

---

**Updated:** December 12, 2025  
**Version:** 2.1.0  
**Changes:** Future date support + Week-based real-time
