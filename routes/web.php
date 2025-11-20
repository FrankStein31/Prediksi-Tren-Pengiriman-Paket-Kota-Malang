<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrediksiController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Main Pages
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/data-pengiriman', function () {
    return view('data-pengiriman');
});

Route::get('/visualisasi', function () {
    return view('visualisasi');
});

Route::get('/upload', function () {
    return view('upload');
});

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
