@extends('layouts.app')

@section('title', 'Visualisasi Prediksi - Prophet Model')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-chart-line mr-3"></i>Visualisasi Prediksi
                </h1>
                <p class="text-purple-100 text-lg">Analisis dan Prediksi Tren Pengiriman Paket dengan Model Prophet</p>
                <p class="text-purple-200 text-sm mt-2">
                    <i class="far fa-calendar-alt mr-2"></i>Fleksibel: 4-52 minggu historis + 1-8 minggu prediksi | 
                    <i class="fas fa-infinity mr-2"></i>Mendukung prediksi masa depan
                </p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <i class="fas fa-brain text-6xl mb-2"></i>
                    <p class="text-sm font-semibold">Prophet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1 text-purple-600"></i>
                        Pilih Kecamatan
                    </label>
                    <select id="kecamatan-select" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec }}">{{ $kec }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1 text-indigo-600"></i>
                        Mode Tanggal
                    </label>
                    <div class="flex gap-2">
                        <label class="flex items-center flex-1 cursor-pointer">
                            <input type="radio" name="date-mode" value="realtime" checked 
                                   onchange="toggleDateMode()"
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-clock mr-1"></i>Sesuai Data (Latest Database)
                            </span>
                        </label>
                        <label class="flex items-center flex-1 cursor-pointer">
                            <input type="radio" name="date-mode" value="custom" 
                                   onchange="toggleDateMode()"
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-calendar-day mr-1"></i>Custom Date
                            </span>
                        </label>
                    </div>
                </div>
                
                <div id="custom-date-container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1 text-blue-600"></i>
                        Pilih Tanggal Referensi
                    </label>
                    <input type="date" id="custom-date" 
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sistem akan menampilkan data historis sebelum tanggal ini dan prediksi setelahnya.
                        Anda bisa pilih <strong>tanggal masa depan</strong> untuk melihat prediksi jangka panjang.
                    </p>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-history mr-1 text-blue-600"></i>
                            Minggu Historis
                        </label>
                        <input type="number" id="weeks-historical" value="52" min="4" max="52" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">Max: 52 minggu</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-forward mr-1 text-green-600"></i>
                            Minggu Prediksi
                        </label>
                        <input type="number" id="weeks-forecast" value="4" min="1" max="8" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">Max: 8 minggu</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-chart-line text-purple-600 text-xl mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800 mb-1">Range Data:</p>
                            <div class="text-xs text-gray-600 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <span id="preview-historical">52 minggu historis</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span id="preview-forecast">4 minggu prediksi</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold text-purple-700">
                                    <i class="fas fa-calendar-week"></i>
                                    <span id="preview-total">Total: 56 minggu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button onclick="loadPrediction()" 
                        class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-chart-line mr-2"></i>Tampilkan Grafik Prediksi
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden">
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
            <p class="mt-4 text-gray-600">Memuat data prediksi...</p>
        </div>
    </div>

    <!-- Statistics Cards - Disabled -->
    <!-- <div id="statistics-section" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-box text-3xl opacity-80"></i>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">Historis</span>
            </div>
            <h3 class="text-2xl font-bold mb-1" id="stat-total-historical">0</h3>
            <p class="text-blue-100 text-sm">Total Paket (52 Minggu)</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-chart-bar text-3xl opacity-80"></i>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">Rata-rata</span>
            </div>
            <h3 class="text-2xl font-bold mb-1" id="stat-avg-weekly">0</h3>
            <p class="text-green-100 text-sm">Paket per Minggu</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-crystal-ball text-3xl opacity-80"></i>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">Prediksi</span>
            </div>
            <h3 class="text-2xl font-bold mb-1" id="stat-total-forecast">0</h3>
            <p class="text-purple-100 text-sm">Total Prediksi (4 Minggu)</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-calendar-week text-3xl opacity-80"></i>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">Range</span>
            </div>
            <h3 class="text-2xl font-bold mb-1" id="stat-weeks-total">56</h3>
            <p class="text-orange-100 text-sm">Total Minggu Ditampilkan</p>
        </div>
    </div> -->

    <!-- Chart Section -->
    <div id="chart-section" class="hidden bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-area mr-2 text-purple-600"></i>
                Grafik Prediksi Pengiriman Paket
            </h2>
            <p class="text-gray-600 text-sm" id="chart-subtitle">
                Kecamatan: <span class="font-semibold" id="current-kecamatan">-</span> | 
                Range: <span class="font-semibold" id="current-range">-</span>
            </p>
        </div>
        
        <div class="relative" style="height: 500px;">
            <canvas id="prediction-chart"></canvas>
        </div>
        
        <div class="mt-6 flex flex-wrap gap-6 justify-center text-sm">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                <span class="text-gray-700">Data Aktual (Historis)</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                <span class="text-gray-700">Prediksi (Forecast)</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-200 rounded mr-2"></div>
                <span class="text-gray-700">Confidence Interval</span>
            </div>
        </div>
    </div>

    <!-- Forecast Table Section -->
    <div id="forecast-table-section" class="hidden bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-table mr-2 text-green-600"></i>
                Detail Prediksi Pengiriman
            </h2>
            <p class="text-gray-600 text-sm">
                Prediksi pengiriman paket untuk <span class="font-semibold" id="table-weeks-forecast">4</span> minggu ke depan
            </p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-green-50 to-emerald-50">
                    <tr id="table-header">
                        <!-- Headers will be dynamically inserted by JavaScript -->
                    </tr>
                </thead>
                <tbody id="forecast-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 text-lg mt-1"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Catatan:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Prediksi:</strong> Nilai prediksi yang paling mungkin terjadi</li>
                        <li><strong>Hari Libur:</strong> Hari libur nasional/hari raya yang jatuh pada minggu tersebut (mempengaruhi volume pengiriman)</li>
                        <li><strong>Aktual:</strong> Nilai sesungguhnya dari database (jika tersedia)</li>
                        <li><strong>Selisih:</strong> Perbedaan antara nilai aktual dan prediksi</li>
                        <li><strong>Akurasi:</strong> Persentase ketepatan prediksi (hijau ≥90%, kuning ≥80%, merah <80%)</li>
                        <!-- <li><strong>Terendah:</strong> Batas bawah confidence interval (80%)</li>
                        <li><strong>Tertinggi:</strong> Batas atas confidence interval (80%)</li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Section -->
    <div id="error-section" class="hidden bg-red-50 border border-red-200 rounded-xl p-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-3 mt-1"></i>
            <div>
                <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
                <p class="text-red-700" id="error-message"></p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
let predictionChart = null;

// Initialize date input with today's date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('custom-date').value = today;
    // Remove max date restriction to allow future dates
    // document.getElementById('custom-date').max = today;
    
    // Update preview on input change
    document.getElementById('weeks-historical').addEventListener('input', updatePreview);
    document.getElementById('weeks-forecast').addEventListener('input', updatePreview);
    updatePreview();
});

function toggleDateMode() {
    const dateMode = document.querySelector('input[name="date-mode"]:checked').value;
    const customDateContainer = document.getElementById('custom-date-container');
    
    if (dateMode === 'custom') {
        customDateContainer.classList.remove('hidden');
    } else {
        customDateContainer.classList.add('hidden');
    }
}

function updatePreview() {
    const weeksHistorical = parseInt(document.getElementById('weeks-historical').value) || 52;
    const weeksForecast = parseInt(document.getElementById('weeks-forecast').value) || 4;
    
    // Validate limits
    const validHistorical = Math.min(Math.max(weeksHistorical, 4), 52);
    const validForecast = Math.min(Math.max(weeksForecast, 1), 8);
    
    document.getElementById('preview-historical').textContent = `${validHistorical} minggu historis`;
    document.getElementById('preview-forecast').textContent = `${validForecast} minggu prediksi`;
    document.getElementById('preview-total').textContent = `Total: ${validHistorical + validForecast} minggu`;
}

async function loadPrediction() {
    const kecamatan = document.getElementById('kecamatan-select').value;
    const weeksHistorical = parseInt(document.getElementById('weeks-historical').value);
    const weeksForecast = parseInt(document.getElementById('weeks-forecast').value);
    const dateMode = document.querySelector('input[name="date-mode"]:checked').value;
    const customDate = document.getElementById('custom-date').value;
    
    // Validation
    if (!kecamatan) {
        alert('Pilih kecamatan terlebih dahulu!');
        return;
    }
    
    // Validate ranges
    if (weeksHistorical < 4 || weeksHistorical > 52) {
        alert('Minggu historis harus antara 4-52 minggu!');
        return;
    }
    
    if (weeksForecast < 1 || weeksForecast > 8) {
        alert('Minggu prediksi harus antara 1-8 minggu!');
        return;
    }
    
    if (dateMode === 'custom' && !customDate) {
        alert('Pilih tanggal custom terlebih dahulu!');
        return;
    }
    
    // Hide previous results
    // document.getElementById('statistics-section').classList.add('hidden');
    document.getElementById('chart-section').classList.add('hidden');
    document.getElementById('forecast-table-section').classList.add('hidden');
    document.getElementById('error-section').classList.add('hidden');
    
    // Show loading
    document.getElementById('loading-indicator').classList.remove('hidden');
    
    try {
        const payload = {
            kecamatan: kecamatan,
            weeks_historical: weeksHistorical,
            weeks_forecast: weeksForecast,
            date_mode: dateMode
        };
        
        if (dateMode === 'custom') {
            payload.custom_date = customDate;
        }
        
        const response = await fetch('{{ route("visualisasi.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        // Hide loading
        document.getElementById('loading-indicator').classList.add('hidden');
        
        if (data.error) {
            showError(data.error + (data.message ? ': ' + data.message : ''));
            return;
        }
        
        // Display data
        // displayStatistics(data.statistics);
        displayChart(data);
        displayForecastTable(data.forecast, data.statistics.weeks_forecast);
        
    } catch (error) {
        document.getElementById('loading-indicator').classList.add('hidden');
        showError('Gagal memuat data: ' + error.message);
        console.error('Error:', error);
    }
}

// Statistics section disabled
// function displayStatistics(stats) {
//     document.getElementById('stat-total-historical').textContent = stats.total_historical.toLocaleString('id-ID');
//     document.getElementById('stat-avg-weekly').textContent = stats.average_weekly.toLocaleString('id-ID');
//     document.getElementById('stat-total-forecast').textContent = stats.total_forecast.toLocaleString('id-ID');
//     document.getElementById('stat-weeks-total').textContent = (stats.weeks_historical + stats.weeks_forecast);
//     
//     document.getElementById('statistics-section').classList.remove('hidden');
// }

function displayChart(data) {
    const ctx = document.getElementById('prediction-chart').getContext('2d');
    
    // Prepare labels and datasets
    const historicalLabels = data.historical.map(d => {
        const date = new Date(d.date);
        return `W${d.week_number} '${date.getFullYear().toString().substr(-2)}`;
    });
    
    const forecastLabels = data.forecast.map(d => {
        const date = new Date(d.date);
        return `W${d.week_number} '${date.getFullYear().toString().substr(-2)}`;
    });
    
    const allLabels = [...historicalLabels, ...forecastLabels];
    
    // Historical data
    const historicalData = data.historical.map(d => d.actual);
    const historicalDataFull = [...historicalData, ...Array(data.forecast.length).fill(null)];
    
    // Forecast data with connection point
    const lastHistoricalValue = historicalData[historicalData.length - 1];
    const forecastData = [...Array(data.historical.length - 1).fill(null), lastHistoricalValue, ...data.forecast.map(d => d.predicted)];
    
    // Confidence interval
    const lowerBound = [...Array(data.historical.length - 1).fill(null), lastHistoricalValue, ...data.forecast.map(d => d.lower_bound)];
    const upperBound = [...Array(data.historical.length - 1).fill(null), lastHistoricalValue, ...data.forecast.map(d => d.upper_bound)];
    
    // Destroy existing chart
    if (predictionChart) {
        predictionChart.destroy();
    }
    
    // Create new chart
    predictionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Data Aktual',
                    data: historicalDataFull,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointRadius: 2,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Prediksi',
                    data: forecastData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    borderDash: [8, 4],
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Upper Bound',
                    data: upperBound,
                    borderColor: 'rgba(34, 197, 94, 0.2)',
                    backgroundColor: 'rgba(34, 197, 94, 0.15)',
                    borderWidth: 1,
                    fill: '+1',
                    pointRadius: 0,
                    tension: 0.4,
                    borderDash: [2, 2]
                },
                {
                    label: 'Lower Bound',
                    data: lowerBound,
                    borderColor: 'rgba(34, 197, 94, 0.2)',
                    backgroundColor: 'rgba(34, 197, 94, 0.15)',
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0,
                    tension: 0.4,
                    borderDash: [2, 2]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 13,
                            weight: '500'
                        },
                        filter: function(item, chart) {
                            // Hide confidence interval from legend
                            return !item.text.includes('Bound');
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label && !label.includes('Bound')) {
                                label += ': ';
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('id-ID') + ' paket';
                                }
                                return label;
                            }
                            return null;
                        },
                        afterBody: function(context) {
                            const index = context[0].dataIndex;
                            const datasets = context[0].chart.data.datasets;
                            
                            // Show confidence interval for forecast points
                            if (index >= data.historical.length) {
                                const upper = datasets.find(d => d.label === 'Upper Bound').data[index];
                                const lower = datasets.find(d => d.label === 'Lower Bound').data[index];
                                
                                if (upper !== null && lower !== null) {
                                    return [
                                        '',
                                        `Confidence Interval:`,
                                        `${lower.toLocaleString('id-ID')} - ${upper.toLocaleString('id-ID')} paket`
                                    ];
                                }
                            }
                            return [];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('id-ID');
                        },
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    
    // Update chart info
    document.getElementById('current-kecamatan').textContent = data.kecamatan;
    document.getElementById('current-range').textContent = 
        `${data.statistics.date_range_start} s/d ${data.statistics.forecast_end}`;
    
    document.getElementById('chart-section').classList.remove('hidden');
}

function displayForecastTable(forecastData, weeksCount) {
    console.log('=== displayForecastTable called ===');
    console.log('forecastData:', forecastData);
    console.log('First item:', forecastData[0]);
    
    const tableHeader = document.getElementById('table-header');
    const tableBody = document.getElementById('forecast-table-body');
    tableBody.innerHTML = ''; // Clear existing data
    
    // Update weeks info
    document.getElementById('table-weeks-forecast').textContent = weeksCount;
    
    // Check if any forecast has actual data
    const hasActualData = forecastData.some(item => item.actual !== undefined);
    
    // Build table header dynamically
    let headerHTML = `
        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
            <i class="fas fa-calendar-day mr-2"></i>Tanggal
        </th>
        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
            <i class="fas fa-calendar-week mr-2"></i>Minggu
        </th>
        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
            <i class="fas fa-calendar-check mr-2"></i>Hari Libur
        </th>
        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
            <i class="fas fa-chart-line mr-2"></i>Prediksi
        </th>
    `;
    
    if (hasActualData) {
        headerHTML += `
            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                <i class="fas fa-database mr-2"></i>Aktual
            </th>
            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                <i class="fas fa-exchange-alt mr-2"></i>Selisih
            </th>
            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                <i class="fas fa-percent mr-2"></i>Akurasi
            </th>
        `;
    }
    
    // Lower and Upper Bound columns disabled
    // headerHTML += `
    //     <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
    //         <i class="fas fa-arrow-down mr-2"></i>Terendah
    //     </th>
    //     <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
    //         <i class="fas fa-arrow-up mr-2"></i>Tertinggi
    //     </th>
    // `;
    
    tableHeader.innerHTML = headerHTML;
    
    // Build table rows
    forecastData.forEach((item, index) => {
        const date = new Date(item.date);
        const formattedDate = date.toLocaleDateString('id-ID', { 
            weekday: 'short', 
            day: 'numeric', 
            month: 'short', 
            year: 'numeric' 
        });
        
        const weekInfo = `Minggu ${item.week_number}, ${item.year}`;
        
        // Create row with alternating colors
        const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
        
        let rowHTML = `
            <tr class="${rowClass} hover:bg-blue-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                    <i class="far fa-calendar text-purple-500 mr-2"></i>
                    ${formattedDate}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                        ${weekInfo}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    ${item.is_holiday && item.holiday ? 
                        `<i class="fas fa-calendar-check text-red-500 mr-1"></i>
                         <span class="text-red-600 font-medium">${item.holiday}</span>` : 
                        `<span class="text-gray-400 italic">-</span>`
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-700">
                    <i class="fas fa-chart-line mr-1"></i>
                    ${item.predicted.toLocaleString('id-ID')}
                </td>
        `;
        
        // Add actual data columns if available
        if (hasActualData) {
            if (item.actual !== undefined) {
                const difference = item.difference || 0;
                const accuracy = item.accuracy_percent || 0;
                const diffClass = difference >= 0 ? 'text-green-600' : 'text-red-600';
                const diffIcon = difference >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                
                rowHTML += `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-blue-700">
                        <i class="fas fa-box mr-1"></i>
                        ${item.actual.toLocaleString('id-ID')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold ${diffClass}">
                        <i class="fas ${diffIcon} mr-1"></i>
                        ${Math.abs(difference).toLocaleString('id-ID')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                        <span class="px-2 py-1 ${accuracy >= 90 ? 'bg-green-100 text-green-800' : accuracy >= 80 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'} rounded-full text-xs font-bold">
                            ${accuracy.toFixed(1)}%
                        </span>
                    </td>
                `;
            } else {
                rowHTML += `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-400 italic">
                        -
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-400 italic">
                        -
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-400 italic">
                        -
                    </td>
                `;
            }
        }
        
        // Lower and Upper Bound columns disabled
        // rowHTML += `
        //         <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600">
        //             ${item.lower_bound.toLocaleString('id-ID')}
        //         </td>
        //         <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
        //             ${item.upper_bound.toLocaleString('id-ID')}
        //         </td>
        // `;
        
        rowHTML += `
            </tr>
        `;
        
        tableBody.innerHTML += rowHTML;
    });
    
    // Show table
    document.getElementById('forecast-table-section').classList.remove('hidden');
}

function showError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-section').classList.remove('hidden');
}
</script>

@endsection
