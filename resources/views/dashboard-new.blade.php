@extends('layouts.app')

@section('title', 'Dashboard - Prediksi Pengiriman Paket')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-chart-line mr-3"></i>Dashboard Prediksi
                </h1>
                <p class="text-blue-100 text-lg">Sistem Prediksi Tren Pengiriman Paket Kota Malang</p>
                <p class="text-blue-200 text-sm mt-2">
                    <i class="far fa-calendar-alt mr-2"></i>{{ date('l, d F Y') }}
                </p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <i class="fas fa-box-open text-6xl mb-2"></i>
                    <p class="text-sm font-semibold">Total Prediksi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transform transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Data</p>
                    <h3 class="text-3xl font-bold text-gray-800">12,543</h3>
                    <p class="text-green-500 text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>+12.5%
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-database text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transform transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Kecamatan</p>
                    <h3 class="text-3xl font-bold text-gray-800">5</h3>
                    <p class="text-gray-400 text-sm mt-2">Area Prediksi</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-map-marked-alt text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transform transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Akurasi Model</p>
                    <h3 class="text-3xl font-bold text-gray-800">94.2%</h3>
                    <p class="text-green-500 text-sm mt-2">
                        <i class="fas fa-check-circle mr-1"></i>Excellent
                    </p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transform transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Prediksi Minggu Ini</p>
                    <h3 class="text-3xl font-bold text-gray-800">2,845</h3>
                    <p class="text-blue-500 text-sm mt-2">
                        <i class="fas fa-calendar-week mr-1"></i>7 hari
                    </p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-shipping-fast text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Aksi Cepat
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Action 1 -->
                <a href="{{ route('upload') }}" class="group bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white hover:from-blue-600 hover:to-blue-700 transform hover:scale-105 transition">
                    <i class="fas fa-cloud-upload-alt text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Upload Data Baru</h3>
                    <p class="text-blue-100 text-sm">Upload file Excel atau CSV untuk prediksi</p>
                    <div class="mt-4 flex items-center text-sm">
                        <span>Mulai Upload</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition"></i>
                    </div>
                </a>

                <!-- Action 2 -->
                <a href="{{ route('visualisasi') }}" class="group bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white hover:from-purple-600 hover:to-purple-700 transform hover:scale-105 transition">
                    <i class="fas fa-chart-area text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Lihat Prediksi</h3>
                    <p class="text-purple-100 text-sm">Visualisasi dan analisis prediksi</p>
                    <div class="mt-4 flex items-center text-sm">
                        <span>Mulai Analisis</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition"></i>
                    </div>
                </a>

                <!-- Action 3 -->
                <a href="{{ route('data.pengiriman') }}" class="group bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white hover:from-green-600 hover:to-green-700 transform hover:scale-105 transition">
                    <i class="fas fa-table text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Data Pengiriman</h3>
                    <p class="text-green-100 text-sm">Lihat semua data pengiriman</p>
                    <div class="mt-4 flex items-center text-sm">
                        <span>Lihat Data</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition"></i>
                    </div>
                </a>

                <!-- Action 4 -->
                <a href="#" class="group bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white hover:from-orange-600 hover:to-orange-700 transform hover:scale-105 transition">
                    <i class="fas fa-cog text-4xl mb-3"></i>
                    <h3 class="text-xl font-bold mb-2">Pengaturan Model</h3>
                    <p class="text-orange-100 text-sm">Konfigurasi parameter prediksi</p>
                    <div class="mt-4 flex items-center text-sm">
                        <span>Atur Model</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="space-y-6">
            <!-- Kecamatan List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Area Kecamatan
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="font-medium text-gray-700">Blimbing</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="font-medium text-gray-700">Kedungkandang</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="font-medium text-gray-700">Klojen</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="font-medium text-gray-700">Lowokwaru</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="font-medium text-gray-700">Sukun</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-history text-green-500 mr-2"></i>Aktivitas Terbaru
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start">
                        <div class="bg-green-100 rounded-full p-2 mr-3">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700">Model diperbarui</p>
                            <p class="text-gray-500 text-xs">2 jam yang lalu</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-100 rounded-full p-2 mr-3">
                            <i class="fas fa-upload text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700">Data baru diupload</p>
                            <p class="text-gray-500 text-xs">5 jam yang lalu</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-purple-100 rounded-full p-2 mr-3">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700">Prediksi dibuat</p>
                            <p class="text-gray-500 text-xs">1 hari yang lalu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
