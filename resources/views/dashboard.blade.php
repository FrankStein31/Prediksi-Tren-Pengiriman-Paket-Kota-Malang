<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prediksi Tren Pengiriman Paket</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Prediksi Tren Pengiriman Paket - Kota Malang</h1>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 text-gray-700">
                <li><a href="/dashboard" class="block py-4 border-b-2 border-blue-600 font-semibold">Dashboard</a></li>
                <li><a href="/data-pengiriman" class="block py-4 hover:border-b-2 hover:border-blue-600">Data Pengiriman Paket</a></li>
                <li><a href="/visualisasi" class="block py-4 hover:border-b-2 hover:border-blue-600">Visualisasi</a></li>
                <li><a href="/upload" class="block py-4 hover:border-b-2 hover:border-blue-600">Upload Data Terbaru</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Total Kecamatan -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Kecamatan</p>
                        <h3 class="text-3xl font-bold text-blue-600" id="total-kecamatan">5</h3>
                    </div>
                    <div class="text-blue-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Model Tersedia -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Model Tersedia</p>
                        <h3 class="text-3xl font-bold text-green-600" id="model-ready">5</h3>
                    </div>
                    <div class="text-green-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Last Update -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Last Update</p>
                        <h3 class="text-lg font-bold text-gray-700" id="last-update">{{ now()->format('d M Y') }}</h3>
                    </div>
                    <div class="text-gray-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Prediction Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold mb-4 text-gray-800">🔮 Quick Prediction</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kecamatan</label>
                    <select id="select-kecamatan" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Minggu</label>
                    <input type="number" id="input-weeks" value="4" min="1" max="52" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <button id="btn-predict" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                        Prediksi Sekarang
                    </button>
                </div>
                <div class="flex items-end">
                    <button id="btn-train" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                        Train Model
                    </button>
                </div>
            </div>
        </div>

        <!-- Prediction Results -->
        <div id="prediction-results" class="bg-white rounded-lg shadow-md p-6 hidden">
            <h2 class="text-xl font-bold mb-4 text-gray-800">📊 Hasil Prediksi</h2>
            <div class="mb-4">
                <p class="text-gray-600">Kecamatan: <span id="result-kecamatan" class="font-bold text-blue-600"></span></p>
                <p class="text-gray-600">Periode: <span id="result-weeks" class="font-bold"></span> minggu ke depan</p>
            </div>
            
            <!-- Chart -->
            <div class="mb-6">
                <canvas id="predictionChart" height="80"></canvas>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Minggu Ke</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prediksi Jumlah Paket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Range (Min - Max)</th>
                        </tr>
                    </thead>
                    <tbody id="prediction-table-body" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-700 font-semibold">Processing...</p>
                <p class="text-gray-500 text-sm" id="loading-text">Mohon tunggu...</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-8">
        <p>Copyright @frankie</p>
    </footer>

    <script>
        let predictionChart = null;

        $(document).ready(function() {
            loadKecamatan();

            $('#btn-predict').click(function() {
                const kecamatan = $('#select-kecamatan').val();
                const weeks = $('#input-weeks').val();

                if (!kecamatan) {
                    alert('Pilih kecamatan terlebih dahulu!');
                    return;
                }

                makePrediction(kecamatan, weeks);
            });

            $('#btn-train').click(function() {
                if (confirm('Training model akan memakan waktu beberapa menit. Lanjutkan?')) {
                    trainModel();
                }
            });
        });

        function loadKecamatan() {
            showLoading('Memuat data kecamatan...');
            
            $.ajax({
                url: '/prediksi/kecamatan',
                method: 'GET',
                success: function(response) {
                    hideLoading();
                    
                    if (response.available_kecamatan) {
                        const kecamatanList = response.available_kecamatan;
                        $('#total-kecamatan').text(response.total);
                        $('#model-ready').text(response.total);

                        kecamatanList.forEach(function(kec) {
                            $('#select-kecamatan').append(`<option value="${kec}">${kec.toUpperCase()}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    console.error('Error loading kecamatan:', xhr);
                }
            });
        }

        function makePrediction(kecamatan, weeks) {
            showLoading('Membuat prediksi...');
            
            $.ajax({
                url: '/prediksi/predict',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    kecamatan: kecamatan,
                    weeks: weeks
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        displayPredictionResults(response);
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    alert('Error making prediction: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        }

        function trainModel() {
            showLoading('Training model... Ini akan memakan waktu beberapa menit.');
            
            $.ajax({
                url: '/prediksi/train',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 600000,
                success: function(response) {
                    hideLoading();
                    alert('Training berhasil! Model siap digunakan.');
                    loadKecamatan();
                },
                error: function(xhr) {
                    hideLoading();
                    alert('Error training model: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        }

        function displayPredictionResults(data) {
            $('#result-kecamatan').text(data.kecamatan.toUpperCase());
            $('#result-weeks').text(data.weeks_ahead);
            
            $('#prediction-table-body').empty();
            
            const predictions = data.predictions;
            predictions.forEach(function(pred, index) {
                const row = `
                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : ''}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(pred.tanggal)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${pred.minggu_ke}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">${pred.prediksi.toLocaleString('id-ID')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pred.lower_bound.toLocaleString('id-ID')} - ${pred.upper_bound.toLocaleString('id-ID')}</td>
                    </tr>
                `;
                $('#prediction-table-body').append(row);
            });

            drawChart(predictions);
            $('#prediction-results').removeClass('hidden');
            
            $('html, body').animate({
                scrollTop: $('#prediction-results').offset().top - 100
            }, 500);
        }

        function drawChart(predictions) {
            const ctx = document.getElementById('predictionChart').getContext('2d');
            
            if (predictionChart) {
                predictionChart.destroy();
            }

            const labels = predictions.map(p => formatDate(p.tanggal));
            const dataPoints = predictions.map(p => p.prediksi);
            const lowerBounds = predictions.map(p => p.lower_bound);
            const upperBounds = predictions.map(p => p.upper_bound);

            predictionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Prediksi',
                            data: dataPoints,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'Batas Bawah',
                            data: lowerBounds,
                            borderColor: 'rgba(239, 68, 68, 0.5)',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0
                        },
                        {
                            label: 'Batas Atas',
                            data: upperBounds,
                            borderColor: 'rgba(34, 197, 94, 0.5)',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Grafik Prediksi Pengiriman Paket'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        function showLoading(text = 'Mohon tunggu...') {
            $('#loading-text').text(text);
            $('#loading').removeClass('hidden');
        }

        function hideLoading() {
            $('#loading').addClass('hidden');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    </script>
</body>
</html>