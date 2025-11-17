<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengiriman Paket</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Prediksi Tren Pengiriman Paket</h1>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 text-gray-700">
                <li><a href="/dashboard" class="block py-4 hover:border-b-2 hover:border-blue-600">Dashboard</a></li>
                <li><a href="/data-pengiriman" class="block py-4 border-b-2 border-blue-600 font-semibold">Data Pengiriman Paket</a></li>
                <li><a href="/visualisasi" class="block py-4 hover:border-b-2 hover:border-blue-600">Visualisasi</a></li>
                <li><a href="/upload" class="block py-4 hover:border-b-2 hover:border-blue-600">Upload Data Terbaru</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">📦 Data Pengiriman Paket</h2>
                <div class="flex gap-4">
                    <input type="text" id="search-box" placeholder="🔍 Cari data..." class="border border-gray-300 rounded-md px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Kiriman</label>
                    <select id="filter-status" class="w-full border border-gray-300 rounded-md p-2">
                        <option value="">Semua Status</option>
                        <option value="Normal">Normal</option>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Rendah">Rendah</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SLA</label>
                    <select id="filter-sla" class="w-full border border-gray-300 rounded-md p-2">
                        <option value="">Semua SLA</option>
                        <option value="On Time">On Time</option>
                        <option value="Delay">Delay</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kantor Kirim</label>
                    <input type="text" id="filter-kantor" placeholder="Nama kantor..." class="w-full border border-gray-300 rounded-md p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kirim</label>
                    <input type="date" id="filter-tanggal" class="w-full border border-gray-300 rounded-md p-2">
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table id="dataTable" class="display w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nopi</th>
                            <th>Status Kiriman</th>
                            <th>Produk</th>
                            <th>SLA</th>
                            <th>Kantor Kirim</th>
                            <th>Tanggal Kirim</th>
                            <th>Tanggal Antaran Pertama</th>
                            <th>Tanggal Update</th>
                            <th>Alamat</th>
                            <th>Kota</th>
                            <th>Alasan Gagal</th>
                            <th>Status SWP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Data -->
                        <tr>
                            <td>PKT001234</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Delivered</span></td>
                            <td>Express</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">On Time</span></td>
                            <td>Kantor Pos Malang</td>
                            <td>2024-01-05</td>
                            <td>2024-01-06</td>
                            <td>2024-01-06 14:30</td>
                            <td>Jl. Soekarno Hatta No. 123</td>
                            <td>MALANG, BLIMBING</td>
                            <td>-</td>
                            <td><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Selesai</span></td>
                        </tr>
                        <tr>
                            <td>PKT001235</td>
                            <td><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">In Transit</span></td>
                            <td>Regular</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">On Time</span></td>
                            <td>Kantor Pos Kedungkandang</td>
                            <td>2024-01-05</td>
                            <td>-</td>
                            <td>2024-01-05 16:00</td>
                            <td>Jl. Raya Kedungkandang No. 45</td>
                            <td>MALANG, KEDUNGKANDANG</td>
                            <td>-</td>
                            <td><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Proses</span></td>
                        </tr>
                        <tr>
                            <td>PKT001236</td>
                            <td><span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Failed</span></td>
                            <td>Express</td>
                            <td><span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Delay</span></td>
                            <td>Kantor Pos Klojen</td>
                            <td>2024-01-04</td>
                            <td>2024-01-05</td>
                            <td>2024-01-05 10:15</td>
                            <td>Jl. Merdeka No. 78</td>
                            <td>MALANG, KLOJEN</td>
                            <td>Alamat tidak lengkap</td>
                            <td><span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Gagal</span></td>
                        </tr>
                        <tr>
                            <td>PKT001237</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Delivered</span></td>
                            <td>Regular</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">On Time</span></td>
                            <td>Kantor Pos Lowokwaru</td>
                            <td>2024-01-03</td>
                            <td>2024-01-04</td>
                            <td>2024-01-04 11:20</td>
                            <td>Jl. MT Haryono No. 156</td>
                            <td>MALANG, LOWOKWARU</td>
                            <td>-</td>
                            <td><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Selesai</span></td>
                        </tr>
                        <tr>
                            <td>PKT001238</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Delivered</span></td>
                            <td>Express</td>
                            <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">On Time</span></td>
                            <td>Kantor Pos Sukun</td>
                            <td>2024-01-03</td>
                            <td>2024-01-03</td>
                            <td>2024-01-03 15:45</td>
                            <td>Jl. Raya Sukun No. 89</td>
                            <td>MALANG, SUKUN</td>
                            <td>-</td>
                            <td><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Selesai</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 text-sm">Total Pengiriman</p>
                <h3 class="text-3xl font-bold text-blue-600">5,750</h3>
                <p class="text-xs text-gray-400 mt-1">Minggu ini</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 text-sm">Berhasil Dikirim</p>
                <h3 class="text-3xl font-bold text-green-600">5,234</h3>
                <p class="text-xs text-gray-400 mt-1">91% sukses</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 text-sm">Dalam Proses</p>
                <h3 class="text-3xl font-bold text-yellow-600">398</h3>
                <p class="text-xs text-gray-400 mt-1">7% proses</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 text-sm">Gagal</p>
                <h3 class="text-3xl font-bold text-red-600">118</h3>
                <p class="text-xs text-gray-400 mt-1">2% gagal</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-8">
        <p>Copyright @frankie</p>
    </footer>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#dataTable').DataTable({
                pageLength: 10,
                order: [[5, 'desc']], // Sort by Tanggal Kirim descending
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ada data yang cocok"
                }
            });

            // Custom search box
            $('#search-box').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filter by Status Kiriman
            $('#filter-status').on('change', function() {
                var status = this.value;
                if (status) {
                    table.column(1).search(status).draw();
                } else {
                    table.column(1).search('').draw();
                }
            });

            // Filter by SLA
            $('#filter-sla').on('change', function() {
                var sla = this.value;
                if (sla) {
                    table.column(3).search(sla).draw();
                } else {
                    table.column(3).search('').draw();
                }
            });

            // Filter by Kantor Kirim
            $('#filter-kantor').on('keyup', function() {
                table.column(4).search(this.value).draw();
            });

            // Filter by Tanggal
            $('#filter-tanggal').on('change', function() {
                table.column(5).search(this.value).draw();
            });
        });
    </script>
</body>
</html>
