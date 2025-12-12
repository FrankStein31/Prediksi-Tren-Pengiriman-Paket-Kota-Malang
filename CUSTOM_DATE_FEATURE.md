# Fitur Custom Date & Flexible Range

## 📅 Overview

Fitur ini memungkinkan pengguna untuk:
1. **Memilih tanggal referensi** - Bisa realtime (hari ini) atau custom date
2. **Mengatur jumlah minggu historis** - 4-52 minggu
3. **Mengatur jumlah minggu prediksi** - 1-8 minggu

## 🎯 Use Cases

### Use Case 1: Real-time Analysis
**Scenario:** User ingin melihat prediksi dari hari ini
- Mode: **Real-time**
- Historical: 52 minggu sebelum hari ini
- Forecast: 4 minggu ke depan

### Use Case 2: Historical Analysis
**Scenario:** User ingin analisis "what-if" di tanggal tertentu
- Mode: **Custom Date**
- Pilih tanggal: Misalnya 10 Oktober 2023
- Historical: 52 minggu sebelum 10 Okt 2023
- Forecast: 4 minggu setelah 10 Okt 2023

### Use Case 3: Short-term Prediction
**Scenario:** User hanya perlu prediksi jangka pendek
- Mode: Real-time atau Custom
- Historical: 12 minggu (3 bulan)
- Forecast: 2 minggu

### Use Case 4: Extended Forecast
**Scenario:** User perlu prediksi jangka panjang
- Mode: Real-time atau Custom
- Historical: 52 minggu (1 tahun)
- Forecast: 8 minggu (2 bulan)

## 🔧 Technical Implementation

### 1. Frontend (Blade)

**New UI Elements:**
```html
<!-- Date Mode Selection -->
<input type="radio" name="date-mode" value="realtime">Real-time
<input type="radio" name="date-mode" value="custom">Custom Date

<!-- Custom Date Input (shown when custom selected) -->
<input type="date" id="custom-date">

<!-- Flexible Ranges -->
<input type="number" id="weeks-historical" min="4" max="52" value="52">
<input type="number" id="weeks-forecast" min="1" max="8" value="4">

<!-- Live Preview -->
<div id="preview-historical">52 minggu historis</div>
<div id="preview-forecast">4 minggu prediksi</div>
<div id="preview-total">Total: 56 minggu</div>
```

### 2. Laravel Controller

**Updated Validation:**
```php
$request->validate([
    'kecamatan' => 'required|string',
    'weeks_historical' => 'integer|min:4|max:52',  // Changed from 12-104 to 4-52
    'weeks_forecast' => 'integer|min:1|max:8',     // Changed from 1-52 to 1-8
    'date_mode' => 'required|string|in:realtime,custom',
    'custom_date' => 'nullable|date'
]);
```

**API Request:**
```php
$requestData = [
    'kecamatan' => $kecamatan,
    'weeks_historical' => (int)$weeksHistorical,
    'weeks_forecast' => (int)$weeksForecast,
    'date_mode' => $dateMode
];

if ($dateMode === 'custom' && $customDate) {
    $requestData['custom_date'] = $customDate;
}
```

### 3. Flask API (Python)

**Updated Endpoint `/api/predict`:**
```python
# New parameters
date_mode = data.get('date_mode', 'realtime')
custom_date = data.get('custom_date')

# Validation
if not (4 <= weeks_historical <= 52):
    return error('weeks_historical must be between 4 and 52')

if not (1 <= weeks_forecast <= 8):
    return error('weeks_forecast must be between 1 and 8')

# Determine reference date
if date_mode == 'custom':
    reference_date = pd.to_datetime(custom_date)
else:
    reference_date = None  # Use today
```

**Updated Database Query:**
```python
query = """
SELECT week_start, week_end, year, week_number, total_paket
FROM weekly_shipment_data
WHERE kecamatan = %s AND week_start <= %s
ORDER BY week_start DESC
LIMIT %s
"""

df = pd.read_sql(query, connection, 
                 params=(kecamatan, reference_date, weeks_back))
```

### 4. Python Script

**Updated Function:**
```python
def generate_visualization_data(
    kecamatan, 
    weeks_historical=52, 
    weeks_forecast=4, 
    reference_date=None, 
    date_mode='realtime'
):
    # Validate ranges
    weeks_historical = min(max(weeks_historical, 4), 52)
    weeks_forecast = min(max(weeks_forecast, 1), 8)
    
    # Determine reference date
    if date_mode == 'custom' and reference_date is not None:
        ref_date = pd.to_datetime(reference_date)
    else:
        ref_date = datetime.now()
    
    # Load data before reference date
    historical_df = load_historical_data(kecamatan, weeks_historical, ref_date)
    ...
```

## 📊 Data Flow

```
User Input (Frontend)
    ↓
    ├─ date_mode: 'realtime' | 'custom'
    ├─ custom_date: '2023-10-10' (if custom)
    ├─ weeks_historical: 4-52
    └─ weeks_forecast: 1-8
    ↓
Laravel Controller (Validation)
    ↓
Flask API (/api/predict)
    ↓
Python Script (visualize_prophet.py)
    ↓
    ├─ Load historical data (before reference_date)
    ├─ Load Prophet model
    ├─ Generate predictions (after reference_date)
    └─ Calculate statistics
    ↓
JSON Response
    ↓
Frontend (Chart.js Visualization)
```

## 🔍 API Request Examples

### Example 1: Real-time with Default Settings
```json
{
  "kecamatan": "BLIMBING",
  "weeks_historical": 52,
  "weeks_forecast": 4,
  "date_mode": "realtime"
}
```

### Example 2: Custom Date Analysis
```json
{
  "kecamatan": "KEDUNGKANDANG",
  "weeks_historical": 26,
  "weeks_forecast": 8,
  "date_mode": "custom",
  "custom_date": "2023-10-10"
}
```

### Example 3: Short-term Prediction
```json
{
  "kecamatan": "KLOJEN",
  "weeks_historical": 12,
  "weeks_forecast": 2,
  "date_mode": "realtime"
}
```

## 📈 Response Structure

```json
{
  "success": true,
  "kecamatan": "BLIMBING",
  "date_mode": "custom",
  "reference_date": "2023-10-10",
  "historical": [
    {
      "date": "2023-01-02",
      "actual": 1234,
      "week_number": 1,
      "year": 2023
    }
  ],
  "forecast": [
    {
      "date": "2023-10-16",
      "predicted": 1456,
      "lower_bound": 1200,
      "upper_bound": 1700,
      "week_number": 42,
      "year": 2023
    }
  ],
  "statistics": {
    "total_historical": 65432,
    "average_weekly": 1258,
    "total_forecast": 5824,
    "weeks_historical": 52,
    "weeks_forecast": 4,
    "date_range_start": "2023-01-02",
    "date_range_end": "2023-10-10",
    "forecast_start": "2023-10-16",
    "forecast_end": "2023-11-06"
  },
  "generated_at": "2025-12-12T10:30:00"
}
```

## ⚙️ Configuration Limits

| Parameter | Min | Max | Default | Notes |
|-----------|-----|-----|---------|-------|
| `weeks_historical` | 4 | 52 | 52 | Minimal 1 bulan data |
| `weeks_forecast` | 1 | 8 | 4 | Maksimal 2 bulan prediksi |
| `custom_date` | - | Today | Today | Tidak boleh masa depan |

## 🎨 UI/UX Features

### 1. Date Mode Toggle
- Radio buttons untuk pilih Real-time vs Custom
- Custom date input muncul dinamis

### 2. Live Preview
- Menampilkan range yang akan ditampilkan
- Update otomatis saat user ubah input
- Visual indicator (ikon, warna)

### 3. Validation
- Client-side: Range validation, date validation
- Server-side: Business logic validation
- User-friendly error messages

### 4. Visual Feedback
- Loading indicator saat fetch data
- Success notification
- Error handling dengan pesan jelas

## 🧪 Testing Scenarios

### Test 1: Boundary Values
```javascript
// Test minimum values
weeks_historical = 4
weeks_forecast = 1

// Test maximum values
weeks_historical = 52
weeks_forecast = 8
```

### Test 2: Invalid Inputs
```javascript
// Should return error
weeks_historical = 3  // Below min
weeks_forecast = 9    // Above max
custom_date = "2030-01-01"  // Future date
```

### Test 3: Date Modes
```javascript
// Real-time mode (should use today)
{ date_mode: 'realtime' }

// Custom mode (should use specified date)
{ 
  date_mode: 'custom',
  custom_date: '2023-10-10'
}
```

## 📝 Usage Instructions

### For Users:

1. **Pilih Kecamatan**
   - Dropdown list kecamatan

2. **Pilih Mode Tanggal**
   - Real-time: Menggunakan tanggal hari ini
   - Custom: Pilih tanggal spesifik

3. **Atur Range**
   - Minggu Historis: 4-52 minggu
   - Minggu Prediksi: 1-8 minggu
   - Preview otomatis update

4. **Klik "Tampilkan Grafik"**
   - Chart akan di-generate
   - Statistik ditampilkan

### For Developers:

```bash
# Start Flask API
cd python
.\venv\Scripts\python.exe app.py

# Start Laravel
php artisan serve

# Access UI
http://127.0.0.1:8000/visualisasi
```

## 🚀 Future Enhancements

1. **Date Range Presets**
   - Last 3 months + 2 weeks forecast
   - Last 6 months + 4 weeks forecast
   - Last year + 8 weeks forecast

2. **Compare Mode**
   - Compare predictions from different dates
   - Side-by-side visualization

3. **Export Features**
   - Download data as CSV
   - Export chart as PNG
   - Generate PDF report

4. **Advanced Filters**
   - Filter by day of week
   - Exclude holidays
   - Seasonal adjustments

## 📞 Support

Jika ada pertanyaan atau issue:
1. Check documentation ini
2. Check Flask API logs
3. Check Laravel logs
4. Check browser console

---

**Last Updated:** December 12, 2025  
**Version:** 2.0.0  
**Author:** AI Assistant
