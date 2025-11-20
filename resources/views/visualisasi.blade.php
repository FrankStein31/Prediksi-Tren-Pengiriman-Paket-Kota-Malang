<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisasi - Prediksi Tren Pengiriman Paket</title>
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
                <li><a href="/dashboard" class="block py-4 hover:border-b-2 hover:border-blue-600">Dashboard</a></li>
                <li><a href="/data-pengiriman" class="block py-4 hover:border-b-2 hover:border-blue-600">Data Pengiriman Paket</a></li>
                <li><a href="/visualisasi" class="block py-4 border-b-2 border-blue-600 font-semibold">Visualisasi</a></li>
                <li><a href="/upload" class="block py-4 hover:border-b-2 hover:border-blue-600">Upload Data Terbaru</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Visualisasi Grafik dan Statistik</h2>
            
            <!-- Filter -->
            <div class="flex gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kecamatan</label>
                    <select id="filter-kecamatan" class="border border-gray-300 rounded-md p-2">
                        <option value="">Semua Kecamatan</option>
                        <option value="BLIMBING">BLIMBING</option>
                        <option value="KEDUNGKANDANG">KEDUNGKANDANG</option>
                        <option value="KLOJEN" selected>KLOJEN</option>
                        <option value="LOWOKWARU">LOWOKWARU</option>
                        <option value="SUKUN">SUKUN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="filter-date" class="border border-gray-300 rounded-md p-2" value="2026-01-04">
                </div>
                <div class="flex items-end">
                    <button id="btn-refresh" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md">
                        Refresh Data
                    </button>
                </div>
            </div>

            <!-- Chart: Pemisahan Data Latih dan Uji -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Pemisahan Data Latih dan Uji untuk Kecamatan KLOJEN</h3>
                <canvas id="trainTestChart" height="100"></canvas>
            </div>
        </div>

        <!-- Table: Prediction Results -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">📋 Hasil Prediksi Detail</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kecamatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Minggu Ke</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prediksi Jumlah Paket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Perbandingan Kenaikan/Penurunan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Klojen</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">4 Januari 2026</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">2000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">↑ 2 - 4</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Klojen</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">11 Januari 2026</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">2400</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">↑ 3 - 5</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-8">
        <p>Copyright @frankie</p>
    </footer>

    <script>
        const trainData = {
            labels: ['2021-01', '2021-07', '2022-01', '2022-07', '2023-01', '2023-07', '2024-01'],
            values: [750, 920, 1100, 1450, 1850, 2100, 2350]
        };

        const testData = {
            labels: ['2024-07', '2025-01'],
            values: [2650, 2850]
        };

        const ctx = document.getElementById('trainTestChart').getContext('2d');
        const trainTestChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [...trainData.labels, ...testData.labels],
                datasets: [
                    {
                        label: 'Data Latih',
                        data: [...trainData.values, null, null],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.4
                    },
                    {
                        label: 'Data Uji',
                        data: [null, null, null, null, null, null, null, ...testData.values],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.4
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
                        text: 'Tren Pengiriman Paket - Train vs Test Data'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Paket'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal Kirim'
                        }
                    }
                }
            }
        });

        $('#btn-refresh').click(function() {
            const kecamatan = $('#filter-kecamatan').val();
            const date = $('#filter-date').val();
            alert(`Refresh data untuk ${kecamatan || 'Semua Kecamatan'} pada ${date}`);
        });

        $('#filter-kecamatan').change(function() {
            const kecamatan = $(this).val();
            console.log('Filter changed to:', kecamatan);
        });
    </script>
</body>
</html>