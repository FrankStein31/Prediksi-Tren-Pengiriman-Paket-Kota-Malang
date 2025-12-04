@extends('layouts.app')

@section('title', 'Data Pengiriman - Prediksi Pengiriman Paket')

@push('styles')
<!-- DataTables CSS - Clean Design -->
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
    
    /* Status badge - minimal */
    .status-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-delivered { background-color: #d1fae5; color: #065f46; }
    .status-process { background-color: #dbeafe; color: #1e40af; }
    .status-return { background-color: #fee2e2; color: #991b1b; }
    .status-pending { background-color: #fef3c7; color: #92400e; }
    
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
    
    /* Processing */
    .dataTables_processing {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-database mr-3"></i>Data Pengiriman Paket
                </h1>
                <p class="text-blue-100 text-lg">Semua data mentah pengiriman paket Kota Malang</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center min-w-[180px]">
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <i class="fas fa-database text-4xl"></i>
                    </div>
                    <p class="text-sm font-medium mb-1">Total Data</p>
                    <h3 class="text-3xl font-bold" id="total-data">
                        <i class="fas fa-spinner fa-spin text-white"></i>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-list mr-2 text-purple-600"></i>Tabel Data Lengkap
            </h2>
            <div class="flex gap-2">
                <button onclick="refreshTable()" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 transition">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Filter Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Mulai
                    </label>
                    <input type="date" id="filter-start-date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Akhir
                    </label>
                    <input type="date" id="filter-end-date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Filter Status SWP -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-check-circle mr-1"></i>Status SWP
                    </label>
                    <select id="filter-status-swp" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="ONTIME">ONTIME</option>
                        <option value="OVER SLA">OVER SLA</option>
                    </select>
                </div>

                <!-- Filter Kecamatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i>Kecamatan
                    </label>
                    <select id="filter-kecamatan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kecamatan</option>
                        <option value="BLIMBING">Blimbing</option>
                        <option value="KEDUNGKANDANG">Kedungkandang</option>
                        <option value="KLOJEN">Klojen</option>
                        <option value="LOWOKWARU">Lowokwaru</option>
                        <option value="SUKUN">Sukun</option>
                    </select>
                </div>

                <!-- Tombol Reset -->
                <div class="flex items-end">
                    <button onclick="resetFilters()" class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm font-medium">
                        <i class="fas fa-undo mr-2"></i>Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="shipment-table" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 140px;">Nosi</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 80px;">Produk</th>
                        <th style="width: 60px;">SLA</th>
                        <th style="width: 100px;">Kantor Kirim</th>
                        <th style="width: 90px;">Tgl Kirim</th>
                        <th style="width: 90px;">Tgl Antaran</th>
                        <th style="width: 90px;">Tgl Update</th>
                        <th style="width: 120px;">Petugas</th>
                        <th style="width: 150px;">Penerima</th>
                        <th style="width: 200px;">Alamat</th>
                        <th style="width: 120px;">Kota</th>
                        <th style="width: 70px;">Berat</th>
                        <th style="width: 120px;">Posisi</th>
                        <th style="width: 120px;">Alasan Gagal</th>
                        <th style="width: 120px;">Irregulitas</th>
                        <th style="width: 100px;">Status SWP</th>
                        <th style="width: 60px;">Cek</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Load Statistics
    loadStats();
    
    // Initialize DataTable with optimal settings
    var table = $('#shipment-table').DataTable({
        // Server-side processing for performance
        processing: true,
        serverSide: true,
        
        // Ajax configuration
        ajax: {
            url: "{{ route('data.pengiriman.getData') }}",
            type: 'GET',
            data: function(d) {
                // Add filter parameters
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.status_swp = $('#filter-status-swp').val();
                d.kecamatan = $('#filter-kecamatan').val();
            },
            error: function(xhr, error, code) {
                console.error('DataTables error:', error, code);
                alert('Error loading data. Please refresh the page.');
            }
        },
        
        // Column definitions
        columns: [
            {
                data: 'DT_RowIndex', 
                name: 'DT_RowIndex', 
                orderable: false, 
                searchable: false,
                width: '40px'
            },
            {data: 'nosi', name: 'nosi', width: '140px'},
            {data: 'status_kiriman', name: 'status_kiriman', width: '100px'},
            {data: 'produk', name: 'produk', width: '80px'},
            {data: 'sla', name: 'sla', width: '60px'},
            {data: 'kantor_kirim', name: 'kantor_kirim', width: '100px'},
            {data: 'tgl_kirim', name: 'tgl_kirim', width: '90px'},
            {data: 'tgl_antaran_pertama', name: 'tgl_antaran_pertama', width: '90px'},
            {data: 'tgl_update', name: 'tgl_update', width: '90px'},
            {data: 'petugas', name: 'petugas', width: '120px'},
            {data: 'nama_penerima', name: 'nama_penerima', width: '150px'},
            {data: 'alamat', name: 'alamat', width: '200px'},
            {data: 'kota', name: 'kota', width: '120px'},
            {data: 'berat', name: 'berat', width: '70px'},
            {data: 'posisi_saat_ini', name: 'posisi_saat_ini', width: '120px'},
            {data: 'alasan_gagal', name: 'alasan_gagal', width: '120px'},
            {data: 'alasan_irregulitas', name: 'alasan_irregulitas', width: '120px'},
            {data: 'status_swp', name: 'status_swp', width: '100px'},
            {data: 'cek', name: 'cek', width: '60px'}
        ],
        
        // Performance settings
        deferRender: true,
        scroller: false,
        
        // Display options
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100, 250], [10, 15, 25, 50, 100, 250]],

        // DOM layout - No buttons, just length, filter, table, info, pagination
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        
        // Language
        language: {
            processing: '<div style="padding:20px;"><i class="fas fa-spinner fa-spin fa-2x text-blue-600"></i><br><br>Loading data...</div>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(filter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            },
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data tersedia"
        },
        
        // Default sorting - Tanggal Kirim terbaru (descending)
        order: [[6, 'desc']],
        
        // Scroll
        scrollX: true,
        scrollCollapse: true,
        
        // State saving disabled for better performance
        stateSave: false
    });
    
    // Search optimization - debounce
    var searchDelay = null;
    $('#shipment-table_filter input').unbind().bind('keyup', function(e) {
        var self = this;
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.search(self.value).draw();
        }, 500);
    });

    // Filter event handlers
    $('#filter-start-date, #filter-end-date, #filter-status-swp, #filter-kecamatan').on('change', function() {
        table.ajax.reload();
    });
});

function loadStats() {
    $.ajax({
        url: "{{ route('data.pengiriman.stats') }}",
        method: 'GET',
        success: function(response) {
            $('#total-data').text(response.total.toLocaleString('id-ID'));
        },
        error: function() {
            $('#total-data').html('<span class="text-red-500">Error</span>');
        }
    });
}

function refreshTable() {
    $('#shipment-table').DataTable().ajax.reload();
    loadStats();
    
    // Show notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-20 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Data berhasil direfresh!';
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function resetFilters() {
    // Clear all filter inputs
    $('#filter-start-date').val('');
    $('#filter-end-date').val('');
    $('#filter-status-swp').val('');
    $('#filter-kecamatan').val('');
    
    // Reload table
    $('#shipment-table').DataTable().ajax.reload();
    
    // Show notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-20 right-6 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = '<i class="fas fa-undo mr-2"></i>Filter berhasil direset!';
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
