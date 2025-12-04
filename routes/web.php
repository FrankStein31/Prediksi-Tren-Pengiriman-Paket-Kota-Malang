<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\ShipmentDataController;

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

Route::get('/upload', function () {
    return view('upload');
})->name('upload');

Route::get('/prediksi-demo', function () {
    return view('prediksi');
});

// Routes untuk prediksi paket
Route::prefix('prediksi')->group(function () {
    Route::get('/kecamatan', [PrediksiController::class, 'getAvailableKecamatan']);
    Route::post('/predict', [PrediksiController::class, 'predict']);
    Route::post('/train', [PrediksiController::class, 'trainModel']);
});

// Routes untuk upload dan processing
Route::prefix('upload')->group(function () {
    Route::post('/preview', [PrediksiController::class, 'uploadPreview']);
    Route::post('/process', [PrediksiController::class, 'processUpload']);
});
