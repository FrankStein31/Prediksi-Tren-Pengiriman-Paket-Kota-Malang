<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data Terbaru</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
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
                <li><a href="/visualisasi" class="block py-4 hover:border-b-2 hover:border-blue-600">Visualisasi</a></li>
                <li><a href="/upload" class="block py-4 border-b-2 border-blue-600 font-semibold">Upload Data Terbaru</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="max-w-3xl mx-auto">
            <!-- Upload Section -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">📤 Upload Data Terbaru</h2>
                
                <!-- Instructions -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Format File:</strong> Excel (.xlsx, .xls)<br>
                                <strong>Kolom Required:</strong> Kota, Cek, Tgl_Kirim<br>
                                <strong>Contoh:</strong> data_kiriman.xlsx
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih File Excel
                        </label>
                        <div class="flex items-center justify-center w-full">
                            <label for="file-upload" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag & drop</p>
                                    <p class="text-xs text-gray-500">Excel files (.xlsx, .xls)</p>
                                    <p id="file-name" class="mt-2 text-sm text-blue-600 font-semibold"></p>
                                </div>
                                <input id="file-upload" name="file" type="file" class="hidden" accept=".xlsx,.xls" />
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" id="btn-upload" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300">
                            Upload dan Preview
                        </button>
                        <button type="button" id="btn-process" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300 hidden">
                            Proses Prediksi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Data -->
            <div id="preview-section" class="bg-white rounded-lg shadow-md p-8 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4">👁️ Preview Data</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="preview-header">
                            </tr>
                        </thead>
                        <tbody id="preview-body" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <p><strong>Total Rows:</strong> <span id="total-rows">0</span></p>
                    <p><strong>Menampilkan:</strong> 10 baris pertama</p>
                </div>
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
        let uploadedData = null;

        $(document).ready(function() {
            $('#file-upload').change(function() {
                const fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#file-name').text('File: ' + fileName);
                }
            });

            $('#uploadForm').submit(function(e) {
                e.preventDefault();
                
                const fileInput = $('#file-upload')[0];
                if (!fileInput.files.length) {
                    alert('Pilih file terlebih dahulu!');
                    return;
                }

                uploadFile(fileInput.files[0]);
            });

            $('#btn-process').click(function() {
                if (confirm('Proses ini akan:\n1. Preprocessing data\n2. Training ulang model\n3. Membuat prediksi baru\n\nProses akan memakan waktu 5-10 menit. Lanjutkan?')) {
                    processData();
                }
            });
        });

        function uploadFile(file) {
            showLoading('Uploading dan membaca file...');

            const formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: '/upload/preview',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        uploadedData = response.data;
                        displayPreview(response.preview);
                        $('#btn-process').removeClass('hidden');
                        alert('File berhasil diupload! Review data preview di bawah.');
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    alert('Error uploading file: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        }

        function displayPreview(preview) {
            if (!preview || !preview.columns || !preview.rows) {
                return;
            }

            let headerHtml = '';
            preview.columns.forEach(col => {
                headerHtml += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">${col}</th>`;
            });
            $('#preview-header').html(headerHtml);

            let bodyHtml = '';
            preview.rows.forEach((row, idx) => {
                let rowHtml = `<tr class="${idx % 2 === 0 ? 'bg-gray-50' : ''}">`;
                preview.columns.forEach(col => {
                    rowHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[col] || '-'}</td>`;
                });
                rowHtml += '</tr>';
                bodyHtml += rowHtml;
            });
            $('#preview-body').html(bodyHtml);

            $('#total-rows').text(preview.total_rows.toLocaleString('id-ID'));
            $('#preview-section').removeClass('hidden');

            $('html, body').animate({
                scrollTop: $('#preview-section').offset().top - 100
            }, 500);
        }

        function processData() {
            showLoading('Memproses data... Ini akan memakan waktu beberapa menit.');

            $.ajax({
                url: '/upload/process',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 600000,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        alert('✅ Proses berhasil!\n\n' + 
                              'Data telah dipreprocess dan model telah di-train ulang.\n' +
                              'Anda sekarang bisa membuat prediksi baru dengan data terbaru.');
                        
                        setTimeout(() => {
                            window.location.href = '/dashboard';
                        }, 2000);
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    alert('Error processing data: ' + (xhr.responseJSON?.error || 'Unknown error'));
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
    </script>
</body>
</html>