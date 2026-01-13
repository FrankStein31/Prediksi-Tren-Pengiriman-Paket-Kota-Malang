@extends('layouts.app')

@section('title', 'Analisis & Pemilihan Model - Prediksi Pengiriman Paket')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-chart-line mr-3"></i>Analisis Model Prediksi
                </h1>
                <p class="text-orange-100 text-lg">Perbandingan SARIMA, Holt-Winters (ETS), dan Prophet</p>
                <p class="text-orange-200 text-sm mt-2">
                    <i class="fas fa-info-circle mr-2"></i>Mengapa Prophet dipilih sebagai model utama untuk prediksi pengiriman paket
                </p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <i class="fas fa-brain text-6xl mb-2"></i>
                    <p class="text-sm font-semibold">Prophet</p>
                    <p class="text-xs text-orange-100">Model Terpilih</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
        </a>
    </div>

    <!-- Model Comparison Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- SARIMA Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-red-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">SARIMA</h3>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-wave-square text-red-600 text-2xl"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Rata-rata MAPE</p>
                    <h4 class="text-3xl font-bold text-red-600">{{ number_format($sarima_avg_mape, 2) }}%</h4>
                </div>
                <!-- <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Kelebihan:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Bagus untuk data stasioner</li>
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Parameter detail (p,d,q)(P,D,Q,s)</li>
                    </ul>
                </div>
                <div class="pt-2">
                    <p class="text-sm text-gray-600 mb-2">Kekurangan:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li><i class="fas fa-times text-red-500 mr-1"></i>Sensitif terhadap outlier</li>
                        <li><i class="fas fa-times text-red-500 mr-1"></i>Tuning parameter lama</li>
                    </ul>
                </div> -->
            </div>
        </div>

        <!-- Holt-Winters Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Holt-Winters (ETS)</h3>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-chart-area text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Rata-rata MAPE</p>
                    <h4 class="text-3xl font-bold text-blue-600">{{ number_format($hw_avg_mape, 2) }}%</h4>
                </div>
                <!-- <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Kelebihan:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Sederhana dan cepat</li>
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Cocok seasonal stabil</li>
                    </ul>
                </div>
                <div class="pt-2">
                    <p class="text-sm text-gray-600 mb-2">Kekurangan:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li><i class="fas fa-times text-red-500 mr-1"></i>Kurang fleksibel</li>
                        <li><i class="fas fa-times text-red-500 mr-1"></i>Tidak handle holiday</li>
                    </ul>
                </div> -->
            </div>
        </div>

        <!-- Prophet Card (Winner) -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500 relative">
            <div class="absolute -top-3 -right-3 bg-green-500 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                <i class="fas fa-trophy text-xl"></i>
            </div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Prophet</h3>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-brain text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Rata-rata MAPE</p>
                    <h4 class="text-3xl font-bold text-green-600">{{ number_format($prophet_avg_mape, 2) }}%</h4>
                    <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                        TERBAIK <i class="fas fa-star ml-1"></i>
                    </span>
                </div>
                <!-- <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Kelebihan:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Handle outlier & missing data</li>
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Support hari libur Indonesia</li>
                        <li><i class="fas fa-check text-green-500 mr-1"></i>Interpretable components</li>
                    </ul>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Detailed Metrics Comparison -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-table mr-2 text-orange-600"></i>Perbandingan Metrik Detail per Kecamatan
        </h2>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button onclick="switchTab('mape')" id="tab-mape" class="tab-button active border-b-2 border-orange-600 py-4 px-1 text-orange-600 font-medium">
                    MAPE (%)
                </button>
                <button onclick="switchTab('mae')" id="tab-mae" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700 font-medium">
                    MAE
                </button>
                <button onclick="switchTab('rmse')" id="tab-rmse" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700 font-medium">
                    RMSE
                </button>
            </nav>
        </div>

        <!-- MAPE Table -->
        <div id="content-mape" class="tab-content">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kecamatan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SARIMA</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Holt-Winters</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prophet</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Terbaik</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comparison as $kec => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $kec }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $data['best_model'] == 'SARIMA' ? 'bg-green-50 font-bold text-green-700' : 'text-gray-500' }}">
                                {{ number_format($data['sarima_mape'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $data['best_model'] == 'Holt-Winters' ? 'bg-green-50 font-bold text-green-700' : 'text-gray-500' }}">
                                {{ number_format($data['hw_mape'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $data['best_model'] == 'Prophet' ? 'bg-green-50 font-bold text-green-700' : 'text-gray-500' }}">
                                {{ number_format($data['prophet_mape'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $data['best_model'] == 'Prophet' ? 'bg-green-100 text-green-800' : 
                                       ($data['best_model'] == 'SARIMA' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $data['best_model'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rata-rata</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-700">{{ number_format($sarima_avg_mape, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-700">{{ number_format($hw_avg_mape, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-700">{{ number_format($prophet_avg_mape, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <i class="fas fa-trophy text-green-600"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MAE Table -->
        <div id="content-mae" class="tab-content hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kecamatan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SARIMA</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Holt-Winters</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prophet</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comparison as $kec => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $kec }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['sarima_mae'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['hw_mae'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['prophet_mae'], 2) }}</td>
                        </tr>
                        @endforeach>
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rata-rata</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-700">{{ number_format($sarima_avg_mae, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-700">{{ number_format($hw_avg_mae, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-700">{{ number_format($prophet_avg_mae, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RMSE Table -->
        <div id="content-rmse" class="tab-content hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kecamatan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SARIMA</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Holt-Winters</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prophet</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comparison as $kec => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $kec }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['sarima_rmse'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['hw_rmse'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ number_format($data['prophet_rmse'], 2) }}</td>
                        </tr>
                        @endforeach>
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rata-rata</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-700">{{ number_format($sarima_avg_rmse, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-700">{{ number_format($hw_avg_rmse, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-700">{{ number_format($prophet_avg_rmse, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Visualization Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-chart-bar mr-2 text-orange-600"></i>Visualisasi Perbandingan Model
        </h2>
        <div class="mb-4">
            <canvas id="mapeComparisonChart" style="max-height: 400px;"></canvas>
        </div>
        <p class="text-sm text-gray-600 mt-4 text-center italic">
            <i class="fas fa-info-circle mr-1"></i>
            Grafik perbandingan MAPE (Mean Absolute Percentage Error) untuk ketiga model prediksi
        </p>
    </div>

    <!-- Actual vs Predicted Visualization -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-chart-line mr-2 text-orange-600"></i>Visualisasi Data Aktual vs Prediksi
        </h2>
        
        <!-- Kecamatan Selector -->
        <div class="mb-6">
            <label for="kecamatanSelect" class="block text-sm font-medium text-gray-700 mb-2">
                Pilih Kecamatan:
            </label>
            <select id="kecamatanSelect" onchange="updatePredictionChart()" 
                    class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 px-4 py-2 border">
                <option value="">-- Pilih Kecamatan --</option>
            </select>
        </div>

        <!-- Chart Canvas -->
        <div class="mb-4 relative">
            <canvas id="predictionComparisonChart" style="max-height: 500px;"></canvas>
            
            <!-- Placeholder Message (shown when no kecamatan selected) -->
            <div id="chartPlaceholder" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border-2 border-dashed border-gray-300">
                <div class="text-center p-8">
                    <div class="mb-4">
                        <i class="fas fa-chart-line text-6xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">
                        Pilih Kecamatan untuk Melihat Visualisasi
                    </h3>
                    <p class="text-gray-500 mb-4">
                        Grafik perbandingan data aktual dengan prediksi dari ketiga model akan ditampilkan di sini
                    </p>
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-arrow-up text-orange-500"></i>
                        <span>Gunakan dropdown di atas untuk memilih kecamatan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Info -->
        <div id="metricsInfo" class="mt-6 hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Metrik Evaluasi:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- SARIMA Metrics -->
                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <h4 class="font-semibold text-red-700 mb-2 flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                        SARIMA
                    </h4>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span>MAPE:</span>
                            <span id="sarima-mape" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>MAE:</span>
                            <span id="sarima-mae" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>RMSE:</span>
                            <span id="sarima-rmse" class="font-semibold">-</span>
                        </div>
                    </div>
                </div>

                <!-- Prophet Metrics -->
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <h4 class="font-semibold text-green-700 mb-2 flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                        Prophet
                    </h4>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span>MAPE:</span>
                            <span id="prophet-mape" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>MAE:</span>
                            <span id="prophet-mae" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>RMSE:</span>
                            <span id="prophet-rmse" class="font-semibold">-</span>
                        </div>
                    </div>
                </div>

                <!-- Holt-Winters Metrics -->
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <h4 class="font-semibold text-yellow-700 mb-2 flex items-center">
                        <div class="w-4 h-4 bg-yellow-400 rounded mr-2"></div>
                        Holt-Winters
                    </h4>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span>MAPE:</span>
                            <span id="hw-mape" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>MAE:</span>
                            <span id="hw-mae" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>RMSE:</span>
                            <span id="hw-rmse" class="font-semibold">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Best Model Indicator -->
            <div id="bestModelBadge" class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-l-4 border-green-500">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-trophy text-green-600 mr-2"></i>
                    <strong>Model Terbaik untuk kecamatan ini:</strong> 
                    <span id="bestModelName" class="font-bold text-green-700">-</span>
                </p>
            </div>
        </div>

        <!-- Metrics Placeholder (shown when no kecamatan selected) -->
        <div id="metricsPlaceholder" class="mt-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            Informasi Visualisasi
                        </h3>
                        <p class="text-gray-700 mb-3">
                            Setelah memilih kecamatan, Anda akan melihat:
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span><strong>Grafik Line Chart</strong> yang membandingkan data aktual dengan prediksi dari ketiga model</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span><strong>Metrik Evaluasi</strong> lengkap (MAPE, MAE, RMSE) untuk setiap model</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span><strong>Indikator Model Terbaik</strong> untuk kecamatan yang dipilih</span>
                            </li>
                        </ul>
                        <div class="mt-4 flex items-center space-x-2 text-sm font-medium text-blue-700">
                            <i class="fas fa-lightbulb"></i>
                            <span>Tip: Pilih kecamatan dari dropdown untuk memulai analisis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-600 mt-4 text-center italic">
            <i class="fas fa-info-circle mr-1"></i>
            Perbandingan visualisasi nilai aktual dengan prediksi dari ketiga model (SARIMA, Prophet, Holt-Winters)
        </p>
    </div>

    <!-- Conclusion -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-lg p-8 border-l-4 border-green-500">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            <i class="fas fa-check-circle mr-2 text-green-600"></i>Kesimpulan
        </h2>
        <div class="space-y-4 text-gray-700">
            <p class="text-lg">
                <strong>Prophet dipilih sebagai model utama</strong> untuk sistem prediksi pengiriman paket Kota Malang dengan pertimbangan:
            </p>
            <ul class="space-y-3 ml-6">
                <li class="flex items-start">
                    <i class="fas fa-star text-yellow-500 mr-3 mt-1"></i>
                    <span><strong>Akurasi Terbaik:</strong> MAPE rata-rata {{ number_format($prophet_avg_mape, 2) }}%, lebih rendah dibanding SARIMA ({{ number_format($sarima_avg_mape, 2) }}%) dan Holt-Winters ({{ number_format($hw_avg_mape, 2) }}%)</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-calendar-check text-blue-500 mr-3 mt-1"></i>
                    <span><strong>Support Hari Libur:</strong> Dapat memperhitungkan dampak hari libur nasional Indonesia terhadap volume pengiriman</span>
                </li>
                <!-- <li class="flex items-start">
                    <i class="fas fa-shield-alt text-purple-500 mr-3 mt-1"></i>
                    <span><strong>Robust terhadap Outlier:</strong> Mampu menangani data anomali dan missing values dengan baik</span>
                </li> -->
                <li class="flex items-start">
                    <i class="fas fa-eye text-indigo-500 mr-3 mt-1"></i>
                    <span><strong>Interpretable:</strong> Komponen trend, seasonality, dan holiday dapat dianalisis secara terpisah</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-cogs text-orange-500 mr-3 mt-1"></i>
                    <span><strong>Mudah Maintenance:</strong> Parameter yang lebih sederhana dan proses retraining yang efisien</span>
                </li>
            </ul>
            <div class="mt-6 p-4 bg-white rounded-lg border border-green-200">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    <strong>Rekomendasi:</strong> Model Prophet akan di-retrain setiap bulan dengan data terbaru untuk menjaga akurasi prediksi tetap optimal.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    /* Chart placeholder styling */
    #chartPlaceholder {
        min-height: 400px;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Pulse animation for icon */
    #chartPlaceholder i {
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.05); opacity: 1; }
    }
    
    /* Smooth transitions */
    #metricsInfo, #metricsPlaceholder {
        transition: all 0.3s ease-in-out;
    }
</style>

<script>
function switchTab(tab) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-orange-600', 'text-orange-600', 'border-b-2');
        button.classList.add('text-gray-500');
    });
    
    // Show selected content
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Add active class to selected button
    const activeButton = document.getElementById('tab-' + tab);
    activeButton.classList.add('active', 'border-orange-600', 'text-orange-600', 'border-b-2');
    activeButton.classList.remove('text-gray-500');
}

// Chart.js Configuration
const ctx = document.getElementById('mapeComparisonChart').getContext('2d');

// Data dari PHP
const kecamatans = {!! json_encode(array_keys($comparison)) !!};
const sarimaData = {!! json_encode(array_column($comparison, 'sarima_mape')) !!};
const prophetData = {!! json_encode(array_column($comparison, 'prophet_mape')) !!};
const hwData = {!! json_encode(array_column($comparison, 'hw_mape')) !!};

const mapeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: kecamatans,
        datasets: [
            {
                label: 'SARIMA',
                data: sarimaData,
                backgroundColor: 'rgba(255, 132, 168, 0.8)',  // Soft pink
                borderColor: 'rgba(255, 99, 145, 1)',
                borderWidth: 2
            },
            {
                label: 'Prophet',
                data: prophetData,
                backgroundColor: 'rgba(34, 211, 238, 0.8)',  // Neon cyan/blue
                borderColor: 'rgba(6, 182, 212, 1)',
                borderWidth: 2
            },
            {
                label: 'Holt-Winters (ETS)',
                data: hwData,
                backgroundColor: 'rgba(253, 224, 71, 0.8)',  // Soft yellow
                borderColor: 'rgba(250, 204, 21, 1)',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Perbandingan MAPE: SARIMA vs Prophet vs Holt-Winters (ETS)',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 20
                }
            },
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12
                    },
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'MAPE (%)',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Kecamatan',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    },
    plugins: [{
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            chart.data.datasets.forEach(function(dataset, i) {
                const meta = chart.getDatasetMeta(i);
                if (!meta.hidden) {
                    meta.data.forEach(function(element, index) {
                        // Draw the text
                        ctx.fillStyle = 'rgb(0, 0, 0)';
                        ctx.font = 'bold 11px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        
                        const dataString = dataset.data[index].toFixed(1) + '%';
                        ctx.fillText(dataString, element.x, element.y - 5);
                    });
                }
            });
        }
    }]
});

// Prediction Comparison Chart
let predictionChart = null;

// Initialize placeholder visibility
document.addEventListener('DOMContentLoaded', function() {
    // Show placeholders initially
    document.getElementById('chartPlaceholder').classList.remove('hidden');
    document.getElementById('metricsPlaceholder').classList.remove('hidden');
    document.getElementById('metricsInfo').classList.add('hidden');
});

// Load Prediction Comparison Data
fetch('/data/predictions_comparison.json')
    .then(response => response.json())
    .then(data => {
        console.log('Prediction data loaded:', Object.keys(data));
        window.predictionData = data;
        
        // Populate kecamatan dropdown
        const select = document.getElementById('kecamatanSelect');
        Object.keys(data).forEach(kecamatan => {
            const option = document.createElement('option');
            option.value = kecamatan;
            option.textContent = kecamatan;
            select.appendChild(option);
        });
    })
    .catch(error => console.error('Error loading prediction data:', error));

function updatePredictionChart() {
    const kecamatan = document.getElementById('kecamatanSelect').value;
    
    if (!kecamatan || !window.predictionData) {
        // Show placeholders, hide chart and metrics
        document.getElementById('chartPlaceholder').classList.remove('hidden');
        document.getElementById('metricsInfo').classList.add('hidden');
        document.getElementById('metricsPlaceholder').classList.remove('hidden');
        if (predictionChart) {
            predictionChart.destroy();
            predictionChart = null;
        }
        return;
    }
    
    // Hide placeholders, show chart and metrics
    document.getElementById('chartPlaceholder').classList.add('hidden');
    document.getElementById('metricsInfo').classList.remove('hidden');
    document.getElementById('metricsPlaceholder').classList.add('hidden');
    
    const data = window.predictionData[kecamatan];
    
    // Show metrics info
    document.getElementById('metricsInfo').classList.remove('hidden');
    
    // Update metrics
    document.getElementById('sarima-mape').textContent = data.predictions.sarima.mape.toFixed(2) + '%';
    document.getElementById('sarima-mae').textContent = data.predictions.sarima.mae.toFixed(2);
    document.getElementById('sarima-rmse').textContent = data.predictions.sarima.rmse.toFixed(2);
    
    document.getElementById('prophet-mape').textContent = data.predictions.prophet.mape.toFixed(2) + '%';
    document.getElementById('prophet-mae').textContent = data.predictions.prophet.mae.toFixed(2);
    document.getElementById('prophet-rmse').textContent = data.predictions.prophet.rmse.toFixed(2);
    
    document.getElementById('hw-mape').textContent = data.predictions.holtwinters.mape.toFixed(2) + '%';
    document.getElementById('hw-mae').textContent = data.predictions.holtwinters.mae.toFixed(2);
    document.getElementById('hw-rmse').textContent = data.predictions.holtwinters.rmse.toFixed(2);
    
    // Update best model badge
    document.getElementById('bestModelName').textContent = data.best_model;
    
    // Destroy existing chart if any
    if (predictionChart) {
        predictionChart.destroy();
    }
    
    // Create new chart
    const ctx = document.getElementById('predictionComparisonChart').getContext('2d');
    
    predictionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Data Aktual',
                    data: data.actual,
                    borderColor: 'rgb(0, 0, 0)',
                    backgroundColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 3,
                    pointRadius: 0,
                    tension: 0.1
                },
                {
                    label: 'SARIMA',
                    data: data.predictions.sarima.values,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0.1
                },
                {
                    label: 'Prophet',
                    data: data.predictions.prophet.values,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0.1
                },
                {
                    label: 'Holt-Winters',
                    data: data.predictions.holtwinters.values,
                    borderColor: 'rgb(234, 179, 8)',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Perbandingan Data Aktual vs Prediksi - ' + kecamatan,
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(0) + ' paket';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Paket',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 10
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>
@endsection
