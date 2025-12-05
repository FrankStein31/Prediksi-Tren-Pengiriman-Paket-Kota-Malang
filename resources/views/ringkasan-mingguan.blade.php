@extends('layouts.app')

@section('title', 'Ringkasan Mingguan - Prediksi Pengiriman Paket')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
    /* Clean DataTables Styling */
    .dataTables_wrapper {
        padding: 0;
        font-size: 14px;
    }
    
    table.dataTable {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 13px;
    }
    
    table.dataTable thead th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 600;
        padding: 10px 8px;
        border: 1px solid #e5e7eb;
        font-size: 13px;
        white-space: nowrap;
        text-align: left;
    }
    
    table.dataTable tbody td {
        padding: 8px;
        border: 1px solid #e5e7eb;
        font-size: 13px;
        vertical-align: middle;
    }
    
    table.dataTable tbody tr:hover {
        background-color: #f9fafb;
    }
    
    /* Controls */
    .dataTables_length select,
    .dataTables_filter input {
        border: 1px solid #d1d5db;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .dataTables_filter input {
        width: 250px;
    }
    
    /* Pagination */
    .dataTables_paginate .paginate_button {
        padding: 4px 10px !important;
        margin: 0 2px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 4px !important;
        font-size: 13px !important;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-1">
                    <i class="fas fa-chart-bar mr-2"></i>Ringkasan Pengiriman Mingguan
                </h1>
                <p class="text-purple-100 text-sm">Data pengiriman per kecamatan berdasarkan minggu</p>
            </div>
            <div class="hidden md:flex gap-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center min-w-[120px]">
                    <p class="text-xs font-medium mb-1">Total Paket</p>
                    <h3 class="text-2xl font-bold" id="header-total-paket">-</h3>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center min-w-[120px]">
                    <p class="text-xs font-medium mb-1">Kecamatan</p>
                    <h3 class="text-2xl font-bold" id="header-total-kecamatan">-</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div>
        <a href="{{ route('data.pengiriman') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Pengiriman
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="space-y-4">
            <!-- Filter Kecamatan -->
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                <label class="text-sm font-medium text-gray-700 min-w-[120px]">
                    <i class="fas fa-map-marker-alt mr-2"></i>Filter Kecamatan:
                </label>
                <select id="filter-kecamatan" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" onchange="loadRingkasanData()">
                    <option value="">Semua Kecamatan (Total)</option>
                    <option value="BLIMBING">Blimbing</option>
                    <option value="KEDUNGKANDANG">Kedungkandang</option>
                    <option value="KLOJEN">Klojen</option>
                    <option value="LOWOKWARU">Lowokwaru</option>
                    <option value="SUKUN">Sukun</option>
                </select>
            </div>

            <!-- Year Filter (only shown when kecamatan is selected) -->
            <div id="year-filter-section" class="hidden">
                <div class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700 min-w-[120px]">
                        <i class="fas fa-calendar mr-2"></i>Pilih Tahun:
                    </label>
                    <select id="filter-year" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" onchange="loadRingkasanData()">
                        <option value="">Semua Tahun</option>
                        <!-- Options will be populated dynamically -->
                    </select>
                    <span id="ringkasan-year-info" class="text-sm text-gray-600 font-medium bg-purple-50 px-4 py-2 rounded-lg border border-purple-200">
                        <!-- Year info akan ditampilkan di sini -->
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="ringkasanLoading" class="bg-white rounded-xl shadow-lg p-12 text-center">
        <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-4"></i>
        <p class="text-gray-600">Memuat data...</p>
    </div>

    <!-- Content Section -->
    <div id="ringkasanContent" class="hidden space-y-6">
        <!-- Summary Cards -->
        <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium mb-2">Total Paket Minggu Ini</p>
                        <h4 class="text-3xl font-bold text-blue-700" id="total-paket-minggu">0</h4>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-box text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium mb-2">Rata-rata per Kecamatan</p>
                        <h4 class="text-3xl font-bold text-green-700" id="avg-paket-kecamatan">0</h4>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-chart-line text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium mb-2">Jumlah Kecamatan</p>
                        <h4 class="text-3xl font-bold text-purple-700" id="total-kecamatan">0</h4>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-map-marked-alt text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-table mr-2 text-purple-600"></i>Detail Ringkasan
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table id="ringkasan-table" class="display nowrap" style="width:100%">
                    <thead>
                        <tr id="table-header-row">
                            <!-- Header will be dynamically generated -->
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let ringkasanTable;
let currentMode = 'total'; // 'total' or 'breakdown'

$(document).ready(function() {
    // Auto load data on page load
    loadRingkasanData();
    
    // Show/hide year filter based on kecamatan selection
    $('#filter-kecamatan').on('change', function() {
        if ($(this).val() !== '') {
            $('#year-filter-section').removeClass('hidden');
            populateYearDropdown(); // Load available years
        } else {
            $('#year-filter-section').addClass('hidden');
        }
        loadRingkasanData(); // Reload data when kecamatan changes
    });
});

function loadRingkasanData() {
    const selectedKecamatan = $('#filter-kecamatan').val();
    const selectedYear = $('#filter-year').val();
    
    // Show loading
    $('#ringkasanLoading').removeClass('hidden');
    $('#ringkasanContent').addClass('hidden');
    
    // Get data based on filter
    if (selectedKecamatan === '') {
        // Load default: all districts total from all years
        loadAllDistrictsTotal();
    } else {
        // Load specific district breakdown by year and week
        loadDistrictBreakdown(selectedKecamatan, selectedYear);
    }
}

function populateYearDropdown() {
    // Get available years from the database
    $.ajax({
        url: '{{ route("data-pengiriman.ringkasan-years") }}',
        type: 'GET',
        success: function(response) {
            const yearSelect = $('#filter-year');
            yearSelect.find('option:not(:first)').remove(); // Clear existing options except "Semua Tahun"
            
            if (response.years && response.years.length > 0) {
                response.years.forEach(year => {
                    yearSelect.append(`<option value="${year}">${year}</option>`);
                });
            }
        },
        error: function() {
            showNotification('error', 'Gagal memuat daftar tahun');
        }
    });
}

function loadAllDistrictsTotal() {
    $.ajax({
        url: '{{ route("data-pengiriman.ringkasan-total") }}',
        type: 'GET',
        success: function(response) {
            // Hide loading
            $('#ringkasanLoading').addClass('hidden');
            $('#ringkasanContent').removeClass('hidden');
            
            // Show summary cards for total view
            $('#summary-cards').removeClass('hidden');
            
            // Update header stats
            $('#header-total-paket').text(response.total_paket.toLocaleString('id-ID'));
            $('#header-total-kecamatan').text(response.total_kecamatan);
            
            // Update summary cards
            $('#total-paket-minggu').text(response.total_paket.toLocaleString('id-ID'));
            $('#avg-paket-kecamatan').text(Math.round(response.avg_paket).toLocaleString('id-ID'));
            $('#total-kecamatan').text(response.total_kecamatan);
            
            // Update card labels for total view
            $('#total-paket-minggu').closest('.bg-white').find('p').first().text('Total Paket (Semua Tahun)');
            
            currentMode = 'total';
            
            // Destroy existing DataTable if exists
            if ($.fn.DataTable.isDataTable('#ringkasan-table')) {
                ringkasanTable.destroy();
            }
            
            // Build table header for total view
            $('#table-header-row').html(`
                <th style="width: 60px;">No</th>
                <th style="width: 150px;">Kecamatan</th>
                <th style="width: 150px;">Total Paket</th>
                <th style="width: 200px;">Persentase</th>
            `);
            
            // Initialize DataTable (CLIENT-SIDE for speed)
            ringkasanTable = $('#ringkasan-table').DataTable({
                data: response.data,
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'kecamatan',
                        render: function(data) {
                            return `<span class="font-semibold text-gray-900">${data}</span>`;
                        }
                    },
                    {
                        data: 'total_paket',
                        render: function(data) {
                            return `<span class="font-semibold text-gray-900">${parseInt(data).toLocaleString('id-ID')}</span>`;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'total_paket',
                        render: function(data) {
                            const percentage = response.total_paket > 0 ? ((data / response.total_paket) * 100).toFixed(2) : 0;
                            return `
                                <div class="flex items-center justify-center gap-3">
                                    <div class="w-32 bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-3 rounded-full" style="width: ${percentage}%"></div>
                                    </div>
                                    <span class="font-semibold text-gray-900 min-w-[60px]">${percentage}%</span>
                                </div>
                            `;
                        },
                        orderable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                order: [[2, 'desc']], // Sort by Total Paket descending
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ada data yang ditemukan"
                }
            });
            
            showNotification('success', 'Data berhasil dimuat!');
        },
        error: function(xhr) {
            $('#ringkasanLoading').addClass('hidden');
            const message = xhr.responseJSON?.message || 'Gagal memuat data ringkasan!';
            showNotification('error', message);
        }
    });
}

function loadDistrictBreakdown(kecamatan, year) {
    $.ajax({
        url: '{{ route("data-pengiriman.ringkasan-breakdown") }}',
        type: 'GET',
        data: {
            kecamatan: kecamatan,
            year: year
        },
        success: function(response) {
            // Hide loading
            $('#ringkasanLoading').addClass('hidden');
            $('#ringkasanContent').removeClass('hidden');
            
            // Hide summary cards for breakdown view
            $('#summary-cards').addClass('hidden');
            
            // Update year info if year is provided
            if (year && year !== '') {
                $('#ringkasan-year-info').html('<i class="fas fa-calendar mr-2"></i>Tahun ' + year);
            } else {
                $('#ringkasan-year-info').html('<i class="fas fa-calendar-alt mr-2"></i>Semua Tahun');
            }
            
            // Update header stats
            $('#header-total-paket').text(response.total_paket.toLocaleString('id-ID'));
            $('#header-total-kecamatan').text('1');
            
            currentMode = 'breakdown';
            
            // Destroy existing DataTable if exists
            if ($.fn.DataTable.isDataTable('#ringkasan-table')) {
                ringkasanTable.destroy();
            }
            
            // Build table header for breakdown view
            $('#table-header-row').html(`
                <th style="width: 60px;">No</th>
                <th style="width: 100px;">Tahun</th>
                <th style="width: 120px;">Minggu Ke</th>
                <th style="width: 180px;">Tanggal</th>
                <th style="width: 250px;">Hari Libur / Hari Raya</th>
                <th style="width: 130px;">Total Paket</th>
            `);
            
            // Initialize DataTable (CLIENT-SIDE for speed)
            ringkasanTable = $('#ringkasan-table').DataTable({
                data: response.data,
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'tahun',
                        render: function(data) {
                            return `<span class="font-semibold text-gray-900">${data}</span>`;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'minggu_ke',
                        type: 'num', // Force numeric sorting
                        render: function(data) {
                            return `<span class="font-semibold text-gray-900">Minggu ${data}</span>`;
                        },
                        className: 'text-center'
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `<span class="text-gray-900">${data.tanggal_mulai} s/d ${data.tanggal_akhir}</span>`;
                        },
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'hari_libur',
                        render: function(data) {
                            if (data === '-' || !data) {
                                return `<span class="text-gray-400 italic">-</span>`;
                            }
                            return `<span class="text-red-600 font-medium">
                                ${data}
                            </span>`;
                        },
                        orderable: false,
                        className: 'text-left'
                    },
                    {
                        data: 'total_paket',
                        render: function(data) {
                            return `<span class="font-semibold text-gray-900">${parseInt(data).toLocaleString('id-ID')}</span>`;
                        },
                        className: 'text-center'
                    }
                ],
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
                order: [[2, 'asc']], // Sort by Minggu ascending (1, 2, 3, ...)
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ada data yang ditemukan"
                }
            });
            
            showNotification('success', 'Data berhasil dimuat!');
        },
        error: function(xhr) {
            $('#ringkasanLoading').addClass('hidden');
            const message = xhr.responseJSON?.message || 'Gagal memuat data ringkasan!';
            showNotification('error', message);
        }
    });
}

function showNotification(type, message) {
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
    
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-6 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    notification.innerHTML = `<i class="fas ${icon} mr-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
