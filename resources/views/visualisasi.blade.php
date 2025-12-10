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
                    <i class="far fa-calendar-alt mr-2"></i>Menampilkan 52 minggu historis + 4 minggu prediksi
                </p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <i class="fas fa-brain text-6xl mb-2"></i>
                    <p class="text-sm font-semibold">Prophet AI</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
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
            
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-history mr-1 text-blue-600"></i>
                    Minggu Historis
                </label>
                <input type="number" id="weeks-historical" value="52" min="12" max="104" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-forward mr-1 text-green-600"></i>
                    Minggu Prediksi
                </label>
                <input type="number" id="weeks-forecast" value="4" min="1" max="52" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            
            <button onclick="loadPrediction()" 
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg hover:shadow-xl">
                <i class="fas fa-chart-line mr-2"></i>Tampilkan Grafik
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden">
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
            <p class="mt-4 text-gray-600">Memuat data prediksi...</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div id="statistics-section" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
    </div>

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

async function loadPrediction() {
    const kecamatan = document.getElementById('kecamatan-select').value;
    const weeksHistorical = document.getElementById('weeks-historical').value;
    const weeksForecast = document.getElementById('weeks-forecast').value;
    
    // Validation
    if (!kecamatan) {
        alert('Pilih kecamatan terlebih dahulu!');
        return;
    }
    
    // Hide previous results
    document.getElementById('statistics-section').classList.add('hidden');
    document.getElementById('chart-section').classList.add('hidden');
    document.getElementById('error-section').classList.add('hidden');
    
    // Show loading
    document.getElementById('loading-indicator').classList.remove('hidden');
    
    try {
        const response = await fetch('{{ route("visualisasi.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                kecamatan: kecamatan,
                weeks_historical: parseInt(weeksHistorical),
                weeks_forecast: parseInt(weeksForecast)
            })
        });
        
        const data = await response.json();
        
        // Hide loading
        document.getElementById('loading-indicator').classList.add('hidden');
        
        if (data.error) {
            showError(data.error + (data.message ? ': ' + data.message : ''));
            return;
        }
        
        // Display data
        displayStatistics(data.statistics);
        displayChart(data);
        
    } catch (error) {
        document.getElementById('loading-indicator').classList.add('hidden');
        showError('Gagal memuat data: ' + error.message);
        console.error('Error:', error);
    }
}

function displayStatistics(stats) {
    document.getElementById('stat-total-historical').textContent = stats.total_historical.toLocaleString('id-ID');
    document.getElementById('stat-avg-weekly').textContent = stats.average_weekly.toLocaleString('id-ID');
    document.getElementById('stat-total-forecast').textContent = stats.total_forecast.toLocaleString('id-ID');
    document.getElementById('stat-weeks-total').textContent = (stats.weeks_historical + stats.weeks_forecast);
    
    document.getElementById('statistics-section').classList.remove('hidden');
}

function displayChart(data) {
    const ctx = document.getElementById('prediction-chart').getContext('2d');
    
    // Prepare labels and datasets
    const historicalLabels = data.historical.map(d => {
        const date = new Date(d.date);
        return `W${d.week_number} ${date.getFullYear()}`;
    });
    
    const forecastLabels = data.forecast.map(d => {
        const date = new Date(d.date);
        return `W${d.week_number} ${date.getFullYear()}`;
    });
    
    const allLabels = [...historicalLabels, ...forecastLabels];
    
    // Historical data
    const historicalData = data.historical.map(d => d.actual);
    const historicalDataFull = [...historicalData, ...Array(data.forecast.length).fill(null)];
    
    // Forecast data
    const forecastData = [...Array(data.historical.length).fill(null), ...data.forecast.map(d => d.predicted)];
    
    // Confidence interval
    const lowerBound = [...Array(data.historical.length).fill(null), ...data.forecast.map(d => d.lower_bound)];
    const upperBound = [...Array(data.historical.length).fill(null), ...data.forecast.map(d => d.upper_bound)];
    
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
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.3
                },
                {
                    label: 'Prediksi',
                    data: forecastData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3
                },
                {
                    label: 'Upper Bound',
                    data: upperBound,
                    borderColor: 'rgba(34, 197, 94, 0.3)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 1,
                    fill: '+1',
                    pointRadius: 0,
                    tension: 0.3
                },
                {
                    label: 'Lower Bound',
                    data: lowerBound,
                    borderColor: 'rgba(34, 197, 94, 0.3)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0,
                    tension: 0.3
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
                        padding: 15,
                        filter: function(item, chart) {
                            // Hide confidence interval from legend
                            return !item.text.includes('Bound');
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString('id-ID') + ' paket';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('id-ID');
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

function showError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-section').classList.remove('hidden');
}
</script>

@endsection
