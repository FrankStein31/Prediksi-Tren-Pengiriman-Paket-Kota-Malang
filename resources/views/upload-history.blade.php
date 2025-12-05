@extends('layouts.app')

@section('title', 'History Upload - Prediksi Pengiriman Paket')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-1">
                    <i class="fas fa-history mr-2"></i>History Upload Data
                </h1>
                <p class="text-purple-100 text-sm">Riwayat upload dan import data pengiriman</p>
            </div>
            <div class="hidden md:flex gap-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center min-w-[120px]">
                    <p class="text-xs font-medium mb-1">Total Upload</p>
                    <h3 class="text-2xl font-bold">{{ $histories->total() }}</h3>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center min-w-[120px]">
                    <p class="text-xs font-medium mb-1">Total Data</p>
                    <h3 class="text-2xl font-bold">{{ number_format($histories->sum('new_rows'), 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div>
        <a href="{{ route('upload') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Upload
        </a>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-list mr-2 text-purple-600"></i>Riwayat Upload
            </h2>
        </div>

        @if($histories->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Upload
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama File
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ukuran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Total Rows
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Data Baru
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Duplikat
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($histories as $index => $history)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $histories->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="text-gray-900 font-medium">
                                {{ $history->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-gray-500 text-xs">
                                {{ $history->created_at->format('H:i:s') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                @if($history->file_extension === 'xlsx' || $history->file_extension === 'xls')
                                    <i class="fas fa-file-excel text-green-600 mr-2 text-lg"></i>
                                @else
                                    <i class="fas fa-file-csv text-blue-600 mr-2 text-lg"></i>
                                @endif
                                <div>
                                    <div class="text-gray-900 font-medium">{{ $history->filename }}</div>
                                    <div class="text-gray-500 text-xs uppercase">.{{ $history->file_extension }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $history->formatted_file_size }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-semibold">
                            {{ number_format($history->total_rows, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ number_format($history->new_rows, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ number_format($history->duplicate_rows, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($history->new_rows > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Berhasil
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-minus mr-1"></i>
                                    Tidak Ada
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $histories->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada History Upload</h3>
            <p class="text-gray-500 mb-6">Mulai upload file untuk melihat riwayat di sini</p>
            <a href="{{ route('upload') }}" class="inline-flex items-center bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-medium">
                <i class="fas fa-upload mr-2"></i>Upload Sekarang
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
