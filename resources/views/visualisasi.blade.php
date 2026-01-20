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
                    <i class="far fa-calendar-alt mr-2"></i>Fleksibel: 4-52 minggu historis + 1-12 minggu prediksi | 
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
            <div class="space-y-6">
                <!-- Kecamatan Selection Card -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200 shadow-sm">
                    <label class="block text-sm font-semibold text-gray-800 mb-3">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                        Pilih Kecamatan
                    </label>
                    <select id="kecamatan-select" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-base py-2.5">
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec }}">{{ $kec }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-600 flex items-start gap-1">
                        <i class="fas fa-info-circle mt-0.5 text-purple-500"></i>
                        <span>Pilih kecamatan untuk melihat prediksi pengiriman paket di wilayah tersebut</span>
                    </p>
                </div>
                
                <!-- Date Mode Card (Hidden but keeping structure) -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200 shadow-sm">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-calendar-alt mr-1 text-indigo-600"></i>
                        Mode Tanggal
                    </label>
                    <div class="flex gap-2">
                        <label class="flex items-center flex-1 cursor-pointer hidden">
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
                    
                    <div id="custom-date-container" class="hidden mt-4 pt-4 border-t border-indigo-300">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-1 text-blue-600"></i>
                            Pilih Tanggal Referensi
                        </label>
                        <input type="date" id="custom-date" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <p class="mt-2 text-xs text-gray-600 bg-white/50 rounded p-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Sistem akan menampilkan data historis sebelum tanggal ini dan prediksi setelahnya.
                            Anda bisa pilih <strong>tanggal masa depan</strong> untuk melihat prediksi jangka panjang.
                        </p>
                    </div>
                </div>
                
                <!-- Weeks Settings Card -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200 shadow-sm">
                    <label class="block text-sm font-semibold text-gray-800 mb-4">
                        <i class="fas fa-calendar-week mr-2 text-blue-600"></i>
                        Pengaturan Periode Prediksi
                    </label>
                    
                    <!-- Hidden Historical Weeks (Locked) -->
                    <!-- <div class="bg-white rounded-lg p-3 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-history mr-1 text-blue-600"></i>
                            Minggu Historis
                        </label>
                        <input type="number" id="weeks-historical" value="12" min="4" max="52" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">Default: 12 minggu</p>
                    </div> -->
                    <div class="bg-white rounded-lg p-3 shadow-sm opacity-75 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1 text-gray-500"></i>
                            Minggu Historis
                        </label>
                        <input type="number" id="weeks-historical" value="12" min="4" max="52" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-gray-100 cursor-not-allowed"
                               readonly disabled>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>Terkunci: 12 minggu
                        </p>
                    </div>
                    
                    <!-- Forecast Weeks Input -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <label class="block text-sm font-semibold text-gray-800 mb-3">
                            <i class="fas fa-forward mr-2 text-green-600"></i>
                            Minggu Prediksi
                        </label>
                        <input type="number" id="weeks-forecast" value="4" min="1" max="12" 
                               class="w-full text-base rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 py-2.5">
                        <p class="mt-2 text-xs text-gray-600 flex items-start gap-1">
                            <i class="fas fa-info-circle mt-0.5 text-green-500"></i>
                            <span>Tentukan berapa minggu ke depan yang ingin diprediksi (1-12 minggu)</span>
                        </p>
                    </div>
                    
                </div>
                
                <!-- Range Data Preview (Hidden but keeping structure) -->
                <!-- <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-chart-line text-purple-600 text-xl mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800 mb-1">Range Data:</p>
                            <div class="text-xs text-gray-600 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <span id="preview-historical">4 minggu historis</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span id="preview-forecast">4 minggu prediksi</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold text-purple-700">
                                    <i class="fas fa-calendar-week"></i>
                                    <span id="preview-total">Total: 8 minggu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Courier Capacity Settings -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200 shadow-sm">
                    <label class="text-sm font-semibold text-gray-800 flex items-center mb-4">
                        <i class="fas fa-users-cog mr-2 text-indigo-600 text-lg"></i>
                        Pengaturan Kapasitas Kurir Per Hari
                    </label>
                    
                    <input type="hidden" id="capacity-unit" value="daily">
                    
                    <!-- Normal Capacity -->
                    <div class="mb-4 bg-white rounded-lg p-4 shadow-sm">
                        <label class="block text-sm font-semibold text-gray-800 mb-3">
                            <i class="fas fa-calendar mr-2 text-gray-600"></i>
                            Kapasitas Hari Normal
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">
                                    <i class="fas fa-arrow-down mr-1 text-blue-500"></i>Min (10 paket/hari)
                                </label>
                                <input type="number" id="courier-capacity-normal-min" value="65" min="10" max="200" 
                                       class="w-full text-base rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2"
                                       onchange="updateCapacityPreview()" oninput="updateCapacityPreview()">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">
                                    <i class="fas fa-arrow-up mr-1 text-green-500"></i>Max (200 paket/hari)
                                </label>
                                <input type="number" id="courier-capacity-normal-max" value="80" min="10" max="200" 
                                       class="w-full text-base rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2"
                                       onchange="updateCapacityPreview()" oninput="updateCapacityPreview()">
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-600 flex items-center gap-1">
                                <i class="fas fa-calculator text-gray-500"></i>
                                Setara per minggu: <strong id="preview-normal-range" class="text-gray-800 ml-1">455-560 paket</strong>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Holiday Capacity -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <label class="block text-sm font-semibold text-gray-800 mb-3">
                            <i class="fas fa-calendar-check mr-2 text-red-600"></i>
                            Kapasitas Hari Libur
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">
                                    <i class="fas fa-arrow-down mr-1 text-blue-500"></i>Min (10 paket/hari)
                                </label>
                                <input type="number" id="courier-capacity-holiday-min" value="100" min="10" max="250" 
                                       class="w-full text-base rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2"
                                       onchange="updateCapacityPreview()" oninput="updateCapacityPreview()">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">
                                    <i class="fas fa-arrow-up mr-1 text-green-500"></i>Max (250 paket/hari)
                                </label>
                                <input type="number" id="courier-capacity-holiday-max" value="120" min="10" max="250" 
                                       class="w-full text-base rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2"
                                       onchange="updateCapacityPreview()" oninput="updateCapacityPreview()">
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-600 flex items-center gap-1">
                                <i class="fas fa-calculator text-gray-500"></i>
                                Setara per minggu: <strong id="preview-holiday-range" class="text-gray-800 ml-1">700-840 paket</strong>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Reset Button -->
                    <button type="button" onclick="resetCourierSettings()" 
                            class="mt-4 w-full text-sm font-medium text-indigo-700 hover:text-indigo-900 py-2.5 bg-white hover:bg-indigo-50 rounded-lg transition-colors shadow-sm border border-indigo-200">
                        <i class="fas fa-undo mr-2"></i>Reset ke Pengaturan Default
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tampilkan Grafik Button - Full Width -->
        <div class="mt-6">
            <button onclick="loadPrediction()" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl text-lg transform hover:scale-[1.01]">
                <i class="fas fa-chart-line mr-2"></i>Tampilkan Grafik Prediksi
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
        
        <div class="mt-6 flex flex-wrap gap-6 justify-center text-sm hidden">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                <span class="text-gray-700">Data Aktual (Historis)</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                <span class="text-gray-700">Prediksi (Forecast)</span>
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
                        <li><strong>Rekomendasi Kurir:</strong> <span class="text-red-600 font-bold">JUMLAH TOTAL KURIR</span> yang dibutuhkan (bukan penambahan kurir)
                            <ul class="list-circle list-inside ml-5 mt-1 text-xs">
                                <li><strong>Cara Hitung:</strong> Total Kurir = Prediksi Paket ÷ Kapasitas per Kurir</li>
                                <li><strong>Minimum:</strong> Kurir dengan beban MAKSIMAL (paling sedikit jumlah, beban tinggi)</li>
                                <li><strong>Optimal (Rekomendasi):</strong> Kurir dengan beban SEIMBANG</li>
                                <li><strong>Maximum:</strong> Kurir dengan beban MINIMAL (paling banyak jumlah, lebih safety)</li>
                                <!-- <li>Minggu Normal: 455-560 paket/minggu/kurir</li>
                                <li>Minggu Libur: 700-840 paket/minggu/kurir (volume lebih tinggi)</li> -->
                            </ul>
                        </li>
                        <li><strong>Aktual:</strong> Nilai sesungguhnya dari database (jika tersedia)</li>
                        <li><strong>Selisih:</strong> Perbedaan antara nilai prediksi dan aktual (prediksi sebagai patokan)</li>
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
    
    // Initialize capacity preview
    updateCapacityPreview();
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
    const weeksHistorical = parseInt(document.getElementById('weeks-historical').value) || 4;
    const weeksForecast = parseInt(document.getElementById('weeks-forecast').value) || 4;
    
    // Validate limits
    const validHistorical = Math.min(Math.max(weeksHistorical, 4), 52);
    const validForecast = Math.min(Math.max(weeksForecast, 1), 12);
    
    document.getElementById('preview-historical').textContent = `${validHistorical} minggu historis`;
    document.getElementById('preview-forecast').textContent = `${validForecast} minggu prediksi`;
    document.getElementById('preview-total').textContent = `Total: ${validHistorical + validForecast} minggu`;
}

function switchUnit(unit) {
    const isDaily = unit === 'daily';
    document.getElementById('capacity-unit').value = unit;
    
    // Update button states
    const dailyBtn = document.getElementById('unit-daily-btn');
    const weeklyBtn = document.getElementById('unit-weekly-btn');
    
    if (isDaily) {
        dailyBtn.className = 'flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors bg-indigo-600 text-white';
        weeklyBtn.className = 'flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100';
    } else {
        dailyBtn.className = 'flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100';
        weeklyBtn.className = 'flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors bg-indigo-600 text-white';
    }
    
    // Update labels
    const suffix = isDaily ? '(paket/hari)' : '(paket/minggu)';
    document.getElementById('unit-label-normal-min').textContent = suffix;
    document.getElementById('unit-label-normal-max').textContent = suffix;
    document.getElementById('unit-label-holiday-min').textContent = suffix;
    document.getElementById('unit-label-holiday-max').textContent = suffix;
    
    // Convert values
    const normalMin = document.getElementById('courier-capacity-normal-min');
    const normalMax = document.getElementById('courier-capacity-normal-max');
    const holidayMin = document.getElementById('courier-capacity-holiday-min');
    const holidayMax = document.getElementById('courier-capacity-holiday-max');
    
    if (isDaily) {
        // Convert from weekly to daily
        normalMin.value = Math.round(parseInt(normalMin.value) / 7);
        normalMax.value = Math.round(parseInt(normalMax.value) / 7);
        holidayMin.value = Math.round(parseInt(holidayMin.value) / 7);
        holidayMax.value = Math.round(parseInt(holidayMax.value) / 7);
        
        // Update min/max attributes
        normalMin.min = 10;
        normalMin.max = 200;
        normalMax.min = 10;
        normalMax.max = 200;
        holidayMin.min = 10;
        holidayMin.max = 250;
        holidayMax.min = 10;
        holidayMax.max = 250;
    } else {
        // Convert from daily to weekly
        normalMin.value = Math.round(parseInt(normalMin.value) * 7);
        normalMax.value = Math.round(parseInt(normalMax.value) * 7);
        holidayMin.value = Math.round(parseInt(holidayMin.value) * 7);
        holidayMax.value = Math.round(parseInt(holidayMax.value) * 7);
        
        // Update min/max attributes
        normalMin.min = 70;
        normalMin.max = 1400;
        normalMax.min = 70;
        normalMax.max = 1400;
        holidayMin.min = 70;
        holidayMin.max = 1750;
        holidayMax.min = 70;
        holidayMax.max = 1750;
    }
    
    updateCapacityPreview();
}

function updateCapacityPreview() {
    const unit = document.getElementById('capacity-unit').value;
    const multiplier = unit === 'daily' ? 7 : 1;
    
    let normalMin = parseInt(document.getElementById('courier-capacity-normal-min').value) || 0;
    let normalMax = parseInt(document.getElementById('courier-capacity-normal-max').value) || 0;
    let holidayMin = parseInt(document.getElementById('courier-capacity-holiday-min').value) || 0;
    let holidayMax = parseInt(document.getElementById('courier-capacity-holiday-max').value) || 0;
    
    // Validate minimum threshold
    const minThreshold = unit === 'daily' ? 10 : 70;
    const maxThresholdNormal = unit === 'daily' ? 200 : 1400;
    const maxThresholdHoliday = unit === 'daily' ? 250 : 1750;
    
    // Highlight invalid inputs
    const normalMinInput = document.getElementById('courier-capacity-normal-min');
    const normalMaxInput = document.getElementById('courier-capacity-normal-max');
    const holidayMinInput = document.getElementById('courier-capacity-holiday-min');
    const holidayMaxInput = document.getElementById('courier-capacity-holiday-max');
    
    // Reset border colors
    normalMinInput.style.borderColor = '';
    normalMaxInput.style.borderColor = '';
    holidayMinInput.style.borderColor = '';
    holidayMaxInput.style.borderColor = '';
    
    // Enforce maximum limits and show warnings
    let hasError = false;
    
    if (normalMin > maxThresholdNormal) {
        normalMin = maxThresholdNormal;
        normalMinInput.value = maxThresholdNormal;
        normalMinInput.style.borderColor = '#f59e0b'; // orange-500
        normalMinInput.style.borderWidth = '2px';
        hasError = true;
    }
    if (normalMax > maxThresholdNormal) {
        normalMax = maxThresholdNormal;
        normalMaxInput.value = maxThresholdNormal;
        normalMaxInput.style.borderColor = '#f59e0b';
        normalMaxInput.style.borderWidth = '2px';
        hasError = true;
    }
    if (holidayMin > maxThresholdHoliday) {
        holidayMin = maxThresholdHoliday;
        holidayMinInput.value = maxThresholdHoliday;
        holidayMinInput.style.borderColor = '#f59e0b';
        holidayMinInput.style.borderWidth = '2px';
        hasError = true;
    }
    if (holidayMax > maxThresholdHoliday) {
        holidayMax = maxThresholdHoliday;
        holidayMaxInput.value = maxThresholdHoliday;
        holidayMaxInput.style.borderColor = '#f59e0b';
        holidayMaxInput.style.borderWidth = '2px';
        hasError = true;
    }
    
    // Check and highlight values below minimum
    if (normalMin < minThreshold && normalMin > 0) {
        normalMinInput.style.borderColor = '#ef4444'; // red-500
        normalMinInput.style.borderWidth = '2px';
    }
    if (normalMax < minThreshold && normalMax > 0) {
        normalMaxInput.style.borderColor = '#ef4444';
        normalMaxInput.style.borderWidth = '2px';
    }
    if (holidayMin < minThreshold && holidayMin > 0) {
        holidayMinInput.style.borderColor = '#ef4444';
        holidayMinInput.style.borderWidth = '2px';
    }
    if (holidayMax < minThreshold && holidayMax > 0) {
        holidayMaxInput.style.borderColor = '#ef4444';
        holidayMaxInput.style.borderWidth = '2px';
    }
    
    // Show alert if maximum exceeded
    if (hasError) {
        const unitLabel = unit === 'daily' ? 'paket/hari' : 'paket/minggu';
        setTimeout(() => {
            alert(`⚠️ Nilai melebihi batas maksimal!\n\nBatas maksimal:\n• Kapasitas Normal: ${maxThresholdNormal} ${unitLabel}\n• Kapasitas Libur: ${maxThresholdHoliday} ${unitLabel}\n\nNilai telah disesuaikan ke batas maksimal.`);
        }, 100);
    }
    
    // Calculate weekly values
    const normalMinWeekly = normalMin * multiplier;
    const normalMaxWeekly = normalMax * multiplier;
    const holidayMinWeekly = holidayMin * multiplier;
    const holidayMaxWeekly = holidayMax * multiplier;
    
    document.getElementById('preview-normal-range').textContent = 
        `${normalMinWeekly}-${normalMaxWeekly} paket`;
    document.getElementById('preview-holiday-range').textContent = 
        `${holidayMinWeekly}-${holidayMaxWeekly} paket`;
}

function resetCourierSettings() {
    const unit = document.getElementById('capacity-unit').value;
    
    if (unit === 'daily') {
        document.getElementById('courier-capacity-normal-min').value = 65;
        document.getElementById('courier-capacity-normal-max').value = 80;
        document.getElementById('courier-capacity-holiday-min').value = 100;
        document.getElementById('courier-capacity-holiday-max').value = 120;
    } else {
        document.getElementById('courier-capacity-normal-min').value = 455;
        document.getElementById('courier-capacity-normal-max').value = 560;
        document.getElementById('courier-capacity-holiday-min').value = 700;
        document.getElementById('courier-capacity-holiday-max').value = 840;
    }
    
    updateCapacityPreview();
    alert('Pengaturan kapasitas kurir direset ke default.');
}

async function loadPrediction() {
    const kecamatan = document.getElementById('kecamatan-select').value;
    const weeksHistorical = parseInt(document.getElementById('weeks-historical').value);
    const weeksForecast = parseInt(document.getElementById('weeks-forecast').value);
    const dateMode = document.querySelector('input[name="date-mode"]:checked').value;
    const customDate = document.getElementById('custom-date').value;
    
    // Get capacity values
    const unit = document.getElementById('capacity-unit').value;
    const multiplier = unit === 'daily' ? 7 : 1;
    
    const normalMin = parseInt(document.getElementById('courier-capacity-normal-min').value) * multiplier;
    const normalMax = parseInt(document.getElementById('courier-capacity-normal-max').value) * multiplier;
    const holidayMin = parseInt(document.getElementById('courier-capacity-holiday-min').value) * multiplier;
    const holidayMax = parseInt(document.getElementById('courier-capacity-holiday-max').value) * multiplier;
    
    // Validation
    if (!kecamatan) {
        alert('Pilih kecamatan terlebih dahulu!');
        return;
    }
    
    // Validate ranges
    if (weeksHistorical < 4 || weeksHistorical > 52) {
        alert('⚠️ Minggu historis harus antara 4-52 minggu!');
        return;
    }
    
    if (weeksForecast < 1 || weeksForecast > 12) {
        alert('⚠️ Minggu prediksi harus antara 1-12 minggu!');
        return;
    }
    
    if (dateMode === 'custom' && !customDate) {
        alert('Pilih tanggal custom terlebih dahulu!');
        return;
    }
    
    // Validate minimum values (must be >= 70 for weekly or >= 10 for daily)
    const minWeeklyThreshold = 70;
    const minDailyThreshold = 10;
    const maxWeeklyThresholdNormal = 1400;
    const maxWeeklyThresholdHoliday = 1750;
    
    // Check minimum values
    if (normalMin < minWeeklyThreshold) {
        alert(`❌ Error: Kapasitas Normal Min terlalu rendah!\n\nNilai minimum: ${minDailyThreshold} paket/hari atau ${minWeeklyThreshold} paket/minggu\nNilai Anda: ${normalMin} paket/minggu\n\nSilakan tingkatkan nilai atau gunakan default (65 paket/hari).`);
        return;
    }
    
    if (normalMax < minWeeklyThreshold) {
        alert(`❌ Error: Kapasitas Normal Max terlalu rendah!\n\nNilai minimum: ${minDailyThreshold} paket/hari atau ${minWeeklyThreshold} paket/minggu\nNilai Anda: ${normalMax} paket/minggu\n\nSilakan tingkatkan nilai atau gunakan default (80 paket/hari).`);
        return;
    }
    
    if (holidayMin < minWeeklyThreshold) {
        alert(`❌ Error: Kapasitas Libur Min terlalu rendah!\n\nNilai minimum: ${minDailyThreshold} paket/hari atau ${minWeeklyThreshold} paket/minggu\nNilai Anda: ${holidayMin} paket/minggu\n\nSilakan tingkatkan nilai atau gunakan default (100 paket/hari).`);
        return;
    }
    
    if (holidayMax < minWeeklyThreshold) {
        alert(`❌ Error: Kapasitas Libur Max terlalu rendah!\n\nNilai minimum: ${minDailyThreshold} paket/hari atau ${minWeeklyThreshold} paket/minggu\nNilai Anda: ${holidayMax} paket/minggu\n\nSilakan tingkatkan nilai atau gunakan default (120 paket/hari).`);
        return;
    }
    
    // Check maximum values
    if (normalMin > maxWeeklyThresholdNormal) {
        alert(`❌ Error: Kapasitas Normal Min terlalu tinggi!\n\nNilai maksimum: 200 paket/hari atau ${maxWeeklyThresholdNormal} paket/minggu\nNilai Anda: ${normalMin} paket/minggu\n\nSilakan turunkan nilai atau gunakan default (65 paket/hari).`);
        return;
    }
    
    if (normalMax > maxWeeklyThresholdNormal) {
        alert(`❌ Error: Kapasitas Normal Max terlalu tinggi!\n\nNilai maksimum: 200 paket/hari atau ${maxWeeklyThresholdNormal} paket/minggu\nNilai Anda: ${normalMax} paket/minggu\n\nSilakan turunkan nilai atau gunakan default (80 paket/hari).`);
        return;
    }
    
    if (holidayMin > maxWeeklyThresholdHoliday) {
        alert(`❌ Error: Kapasitas Libur Min terlalu tinggi!\n\nNilai maksimum: 250 paket/hari atau ${maxWeeklyThresholdHoliday} paket/minggu\nNilai Anda: ${holidayMin} paket/minggu\n\nSilakan turunkan nilai atau gunakan default (100 paket/hari).`);
        return;
    }
    
    if (holidayMax > maxWeeklyThresholdHoliday) {
        alert(`❌ Error: Kapasitas Libur Max terlalu tinggi!\n\nNilai maksimum: 250 paket/hari atau ${maxWeeklyThresholdHoliday} paket/minggu\nNilai Anda: ${holidayMax} paket/minggu\n\nSilakan turunkan nilai atau gunakan default (120 paket/hari).`);
        return;
    }
    
    // Validate courier capacity min < max
    if (normalMin >= normalMax) {
        alert('❌ Error: Kapasitas Normal Min harus lebih kecil dari Max!');
        return;
    }
    
    if (holidayMin >= holidayMax) {
        alert('❌ Error: Kapasitas Libur Min harus lebih kecil dari Max!');
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
            date_mode: dateMode,
            courier_capacity_normal_min: normalMin,
            courier_capacity_normal_max: normalMax,
            courier_capacity_holiday_min: holidayMin,
            courier_capacity_holiday_max: holidayMax
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
                    label: 'Data Aktual (Historis)',
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
                    label: 'Prediksi (Forecast)',
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
                            if (label) {
                                label += ': ';
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('id-ID') + ' paket';
                                }
                                return label;
                            }
                            return null;
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
            <i class="fas fa-chart-line mr-2"></i>Prediksi Jumlah Paket
        </th>
        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
            <i class="fas fa-users mr-2"></i>Rekomendasi Jumlah Kurir
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    ${item.courier_recommendation ? `
                        <div class="inline-flex items-center gap-2">
                            <span class="px-3 py-1.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg font-bold text-base shadow-md">
                                <i class="fas fa-user-tie mr-1"></i>
                                ${item.courier_recommendation.optimal}
                            </span>
                            <div class="text-xs text-gray-500">
                                <div class="font-medium">Min: ${item.courier_recommendation.minimum} | Max: ${item.courier_recommendation.maximum}</div>
                                ${item.courier_recommendation.is_holiday_week ? 
                                    '<div class="text-red-600 font-semibold"><i class="fas fa-calendar-check mr-1"></i>Kapasitas Libur</div>' : 
                                    '<div class="text-gray-600">Kapasitas Normal</div>'
                                }
                            </div>
                        </div>
                    ` : `<span class="text-gray-400 italic">-</span>`}
                </td>
        `;
        
        // Add actual data columns if available
        if (hasActualData) {
            if (item.actual !== undefined) {
                // Selisih = Prediksi - Aktual (prediksi sebagai patokan)
                // Positif = prediksi lebih tinggi dari aktual
                // Negatif = prediksi lebih rendah dari aktual
                const difference = item.predicted - item.actual;
                const accuracy = item.accuracy_percent || 0;
                const diffClass = difference >= 0 ? 'text-green-600' : 'text-red-600';
                const diffIcon = difference >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                const diffText = difference >= 0 ? 
                    `+${difference.toLocaleString('id-ID')}` : 
                    difference.toLocaleString('id-ID');
                
                rowHTML += `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-blue-700">
                        <i class="fas fa-box mr-1"></i>
                        ${item.actual.toLocaleString('id-ID')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold ${diffClass}">
                        <i class="fas ${diffIcon} mr-1"></i>
                        ${diffText}
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
