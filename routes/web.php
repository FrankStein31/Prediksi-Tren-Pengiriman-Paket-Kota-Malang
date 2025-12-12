<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\ShipmentDataController;
use App\Http\Controllers\UploadDataController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Main Pages
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/model-explanation', [DashboardController::class, 'modelExplanation'])->name('model.explanation');

Route::get('/data-pengiriman', [ShipmentDataController::class, 'index'])->name('data.pengiriman');
Route::get('/data-pengiriman/getData', [ShipmentDataController::class, 'getData'])->name('data.pengiriman.getData');
Route::get('/data-pengiriman/stats', [ShipmentDataController::class, 'getStats'])->name('data.pengiriman.stats');
Route::get('/data-pengiriman/ringkasan-mingguan', [ShipmentDataController::class, 'ringkasanPage'])->name('data-pengiriman.ringkasan-page');
Route::get('/data-pengiriman/ringkasan-total', [ShipmentDataController::class, 'getRingkasanTotal'])->name('data-pengiriman.ringkasan-total');
Route::get('/data-pengiriman/ringkasan-breakdown', [ShipmentDataController::class, 'getRingkasanBreakdown'])->name('data-pengiriman.ringkasan-breakdown');
Route::get('/data-pengiriman/ringkasan-years', [ShipmentDataController::class, 'getRingkasanYears'])->name('data-pengiriman.ringkasan-years');
Route::delete('/data-pengiriman/{id}', [ShipmentDataController::class, 'destroy'])->name('data.pengiriman.destroy');

// Visualisasi Routes
Route::get('/visualisasi', [App\Http\Controllers\VisualisasiController::class, 'index'])->name('visualisasi');
Route::post('/visualisasi/data', [App\Http\Controllers\VisualisasiController::class, 'getPredictionData'])->name('visualisasi.data');

// Upload Data Routes
Route::get('/upload', [UploadDataController::class, 'index'])->name('upload');
Route::get('/upload/history', [UploadDataController::class, 'history'])->name('upload.history');
Route::post('/upload/process', [UploadDataController::class, 'process'])->name('data.upload.process');
Route::get('/upload/progress', [UploadDataController::class, 'getProgress'])->name('data.upload.progress');
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
