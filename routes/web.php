<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\ShipmentDataController;
use App\Http\Controllers\UploadDataController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Main Pages
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/data-pengiriman', [ShipmentDataController::class, 'index'])->name('data.pengiriman');
Route::get('/data-pengiriman/getData', [ShipmentDataController::class, 'getData'])->name('data.pengiriman.getData');
Route::get('/data-pengiriman/stats', [ShipmentDataController::class, 'getStats'])->name('data.pengiriman.stats');

Route::get('/visualisasi', function () {
    return view('visualisasi');
})->name('visualisasi');

// Upload Data Routes
Route::get('/upload', [UploadDataController::class, 'index'])->name('upload');
Route::post('/upload/process', [UploadDataController::class, 'process'])->name('data.upload.process');
Route::get('/upload/preview-data', [UploadDataController::class, 'getPreviewData'])->name('data.upload.preview');
Route::post('/upload/import', [UploadDataController::class, 'import'])->name('data.upload.import');

Route::get('/prediksi-demo', function () {
    return view('prediksi');
});

// Routes untuk prediksi paket
Route::prefix('prediksi')->group(function () {
    Route::get('/kecamatan', [PrediksiController::class, 'getAvailableKecamatan']);
    Route::post('/predict', [PrediksiController::class, 'predict']);
    Route::post('/train', [PrediksiController::class, 'trainModel']);
});
