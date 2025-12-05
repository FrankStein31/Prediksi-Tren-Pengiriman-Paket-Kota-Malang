@extends('layouts.app')

@section('title', 'Upload Data - Prediksi Pengiriman Paket')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
    .upload-zone {
        border: 2px dashed #d1d5db;
        transition: all 0.3s ease;
    }
    
    .upload-zone:hover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    
    .upload-zone.dragover {
        border-color: #3b82f6;
        background-color: #dbeafe;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
    
    /* Clean DataTables Styling - Same as Data Pengiriman */
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
    
    /* Status badge */
    .status-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-new { background-color: #d1fae5; color: #065f46; }
    .status-duplicate { background-color: #fef3c7; color: #92400e; }
    
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
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-1">
                    <i class="fas fa-upload mr-2"></i>Upload Data Pengiriman
                </h1>
                <p class="text-blue-100 text-sm">Upload file Excel atau CSV untuk menambah data baru</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center min-w-[140px]">
                    <div class="flex items-center justify-center gap-2 mb-1">
                        <i class="fas fa-file-import text-2xl"></i>
                    </div>
                    <p class="text-xs font-medium mb-1">Format</p>
                    <h3 class="text-lg font-bold">Excel / CSV</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">
                <i class="fas fa-cloud-upload-alt mr-2 text-blue-600"></i>Upload File
            </h2>
            <p class="text-gray-600 text-sm">Pilih file Excel (.xlsx, .xls) atau CSV (.csv) untuk diupload</p>
        </div>

        <!-- Upload Zone -->
        <div id="upload-zone" class="upload-zone rounded-xl p-12 text-center cursor-pointer mb-6">
            <input type="file" id="file-input" class="hidden" accept=".xlsx,.xls,.csv">
            <div id="upload-prompt">
                <i class="fas fa-cloud-upload-alt text-6xl text-gray-400 mb-4"></i>
                <p class="text-lg font-semibold text-gray-700 mb-2">Klik atau Drag & Drop File</p>
                <p class="text-sm text-gray-500">Mendukung: Excel (.xlsx, .xls) dan CSV (.csv)</p>
                <p class="text-xs text-gray-400 mt-2">Maksimal ukuran file: 500 MB</p>
            </div>
            <div id="file-info" class="hidden">
                <i class="fas fa-file-excel text-6xl text-green-600 mb-4"></i>
                <p class="text-lg font-semibold text-gray-700 mb-1" id="file-name"></p>
                <p class="text-sm text-gray-500" id="file-size"></p>
                <button type="button" onclick="removeFile()" class="mt-4 text-red-600 hover:text-red-700 text-sm font-medium">
                    <i class="fas fa-times mr-1"></i>Hapus File
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div id="progress-container" class="hidden mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700" id="progress-text">Memproses...</span>
                <span class="text-sm font-medium text-gray-700" id="progress-percent">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="progress-bar bg-blue-600 h-3 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <div class="flex gap-3">
                <button onclick="processFile()" id="process-btn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-cog mr-2"></i>Proses & Preview Data
                </button>
                <button onclick="importData()" id="import-btn" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium hidden">
                    <i class="fas fa-check mr-2"></i>Import ke Database
                </button>
                <button onclick="resetUpload()" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition font-medium">
                    <i class="fas fa-undo mr-2"></i>Reset
                </button>
            </div>
            
            <!-- History Button -->
            <a href="{{ route('upload.history') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-medium inline-flex items-center">
                <i class="fas fa-history mr-2"></i>Lihat History Upload
            </a>
        </div>
    </div>

    <!-- Preview Section -->
    <div id="preview-section" class="bg-white rounded-xl shadow-lg p-6 hidden">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-800 mb-2">
                <i class="fas fa-eye mr-2 text-purple-600"></i>Preview Data
            </h2>
            <div class="flex gap-4 text-sm mb-4">
                <span class="text-gray-600">Total Baris: <strong id="total-rows">0</strong></span>
                <span class="text-green-600">Data Baru: <strong id="new-rows">0</strong></span>
                <span class="text-yellow-600">Data Duplikat: <strong id="duplicate-rows">0</strong></span>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Catatan:</strong> Preview menampilkan maksimal 50 baris pertama agar tidak berat. Semua data akan diimport saat klik tombol Import.
            </div>
        </div>

        <!-- Preview Table with Yajra DataTables -->
        <div class="overflow-x-auto">
            <table id="preview-table" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 140px;">NOSI</th>
                        <th style="width: 100px;">Status Kirim</th>
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
                        <th style="width: 100px;">Status SWP</th>
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
let uploadedFile = null;
let previewData = null;

// Setup drag and drop
const uploadZone = document.getElementById('upload-zone');
const fileInput = document.getElementById('file-input');

uploadZone.addEventListener('click', () => fileInput.click());

uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFile(e.target.files[0]);
    }
});

function handleFile(file) {
    // Validate file type
    const validTypes = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv'
    ];
    
    const validExtensions = ['.xlsx', '.xls', '.csv'];
    const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    
    if (!validExtensions.includes(fileExtension)) {
        alert('Format file tidak valid! Gunakan Excel (.xlsx, .xls) atau CSV (.csv)');
        return;
    }
    
    // Validate file size (500 MB)
    if (file.size > 500 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 500 MB');
        return;
    }
    
    uploadedFile = file;
    
    // Show file info
    document.getElementById('upload-prompt').classList.add('hidden');
    document.getElementById('file-info').classList.remove('hidden');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('process-btn').disabled = false;
}

function removeFile() {
    uploadedFile = null;
    fileInput.value = '';
    document.getElementById('upload-prompt').classList.remove('hidden');
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('process-btn').disabled = true;
    document.getElementById('preview-section').classList.add('hidden');
    document.getElementById('import-btn').classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

async function processFile() {
    if (!uploadedFile) {
        alert('Pilih file terlebih dahulu!');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', uploadedFile);
    
    // Show progress
    document.getElementById('progress-container').classList.remove('hidden');
    updateProgress(10, 'Mengupload file...');
    
    try {
        const response = await fetch('{{ route("data.upload.process") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        updateProgress(50, 'Memproses data...');
        
        if (!response.ok) {
            throw new Error('Gagal memproses file');
        }
        
        const result = await response.json();
        
        updateProgress(100, 'Selesai!');
        
        // Hide progress after 1 second
        setTimeout(() => {
            document.getElementById('progress-container').classList.add('hidden');
        }, 1000);
        
        // Show preview
        previewData = result.data;
        displayPreview(result);
        
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal memproses file: ' + error.message);
        document.getElementById('progress-container').classList.add('hidden');
    }
}

function updateProgress(percent, text) {
    document.getElementById('progress-bar').style.width = percent + '%';
    document.getElementById('progress-percent').textContent = percent + '%';
    document.getElementById('progress-text').textContent = text;
}

let previewTable = null;

function displayPreview(result) {
    document.getElementById('preview-section').classList.remove('hidden');
    document.getElementById('total-rows').textContent = result.total_rows.toLocaleString('id-ID');
    document.getElementById('new-rows').textContent = result.new_rows.toLocaleString('id-ID');
    document.getElementById('duplicate-rows').textContent = result.duplicate_rows.toLocaleString('id-ID');
    
    // Show import button only if there's new data
    if (result.new_rows > 0) {
        document.getElementById('import-btn').classList.remove('hidden');
    } else {
        document.getElementById('import-btn').classList.add('hidden');
        alert('Tidak ada data baru untuk diimport. Semua data sudah ada di database.');
    }
    
    // Destroy existing DataTable if exists
    if (previewTable) {
        previewTable.destroy();
    }
    
    // Initialize Yajra DataTables (Server-Side) - Same style as Data Pengiriman
    previewTable = $('#preview-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("data.upload.preview") }}',
        columns: [
            { 
                data: 'status_badge',
                name: 'status_badge',
                orderable: false, // Already sorted by backend
                searchable: false,
                width: '80px'
            },
            { data: 'nosi', name: 'nosi', width: '140px' },
            { data: 'status_kiriman', name: 'status_kiriman', width: '100px' },
            { data: 'produk', name: 'produk', width: '80px' },
            { data: 'sla', name: 'sla', width: '60px' },
            { data: 'kantor_kirim', name: 'kantor_kirim', width: '100px' },
            { data: 'tgl_kirim', name: 'tgl_kirim', width: '90px' },
            { data: 'tgl_antaran_pertama', name: 'tgl_antaran_pertama', width: '90px' },
            { data: 'tgl_update', name: 'tgl_update', width: '90px' },
            { data: 'petugas', name: 'petugas', width: '120px' },
            { data: 'nama_penerima', name: 'nama_penerima', width: '150px' },
            { data: 'alamat', name: 'alamat', width: '200px' },
            { data: 'kota', name: 'kota', width: '120px' },
            { data: 'berat', name: 'berat', width: '70px' },
            { data: 'posisi_saat_ini', name: 'posisi_saat_ini', width: '120px' },
            { data: 'status_swp', name: 'status_swp', width: '100px' }
        ],
        
        // Performance settings - Same as Data Pengiriman
        deferRender: true,
        scroller: false,
        
        // Display options
        pageLength: 5,
        lengthMenu: [[5, 10, 15, 25, 50], [5, 10, 15, 25, 50]],
        
        // DOM layout
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        
        // Language
        language: {
            processing: '<div style="padding:20px;"><i class="fas fa-spinner fa-spin fa-2x text-blue-600"></i><br><br>Loading preview...</div>',
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
        
        order: [],
        
        // Scroll
        scrollX: true,
        scrollCollapse: true,
        
        // State saving disabled for better performance
        stateSave: false
    });
}

async function importData() {
    if (!confirm(`Yakin ingin import ${document.getElementById('new-rows').textContent} data baru ke database?`)) {
        return;
    }
    
    document.getElementById('progress-container').classList.remove('hidden');
    updateProgress(10, 'Memulai import...');
    document.getElementById('import-btn').disabled = true;
    
    try {
        const response = await fetch('{{ route("data.upload.import") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                data: previewData
            })
        });
        
        updateProgress(100, 'Import selesai!');
        
        if (!response.ok) {
            throw new Error('Gagal import data');
        }
        
        const result = await response.json();
        
        setTimeout(() => {
            alert(`Berhasil import ${result.imported} data baru!`);
            window.location.href = '{{ route("data.pengiriman") }}';
        }, 1000);
        
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal import data: ' + error.message);
        document.getElementById('import-btn').disabled = false;
        document.getElementById('progress-container').classList.add('hidden');
    }
}

function resetUpload() {
    if (confirm('Reset upload? Data preview akan hilang.')) {
        removeFile();
        previewData = null;
        
        // Destroy DataTable
        if (previewTable) {
            previewTable.destroy();
            previewTable = null;
        }
        
        document.getElementById('progress-container').classList.add('hidden');
    }
}
</script>
@endpush
