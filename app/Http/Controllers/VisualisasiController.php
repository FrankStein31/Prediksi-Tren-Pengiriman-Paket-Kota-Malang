<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;
use App\Helpers\IndonesianHoliday;

class VisualisasiController extends Controller
{
    /**
     * Display the visualization page
     */
    public function index()
    {
        $kecamatans = [
            'BLIMBING',
            'KEDUNGKANDANG', 
            'KLOJEN',
            'LOWOKWARU',
            'SUKUN'
        ];
        
        return view('visualisasi', compact('kecamatans'));
    }
    
    /**
     * Get prediction data for chart
     */
    public function getPredictionData(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|string|in:BLIMBING,KEDUNGKANDANG,KLOJEN,LOWOKWARU,SUKUN',
            'weeks_historical' => 'integer|min:4|max:52',  // Max 52 weeks
            'weeks_forecast' => 'integer|min:1|max:8',     // Max 8 weeks
            'date_mode' => 'required|string|in:realtime,custom',
            'custom_date' => 'nullable|date'
        ]);
        
        $kecamatan = $request->input('kecamatan');
        $weeksHistorical = $request->input('weeks_historical', 52);
        $weeksForecast = $request->input('weeks_forecast', 4);
        $dateMode = $request->input('date_mode', 'realtime');
        $customDate = $request->input('custom_date');
        
        try {
            // Check Flask API health first
            $flaskApiUrl = config('flask.api_url');
            $healthUrl = rtrim($flaskApiUrl, '/') . '/health';
            
            try {
                $healthClient = new \GuzzleHttp\Client(['timeout' => 5, 'http_errors' => false]);
                $healthResponse = $healthClient->get($healthUrl);
                
                if ($healthResponse->getStatusCode() === 200) {
                    $healthData = json_decode($healthResponse->getBody()->getContents(), true);
                    
                    // Check if specific model exists
                    if (isset($healthData['models'])) {
                        $modelFound = false;
                        foreach ($healthData['models'] as $model) {
                            if ($model['kecamatan'] === $kecamatan) {
                                $modelFound = true;
                                if (!$model['exists']) {
                                    return response()->json([
                                        'error' => 'Model tidak tersedia',
                                        'message' => 'Model untuk ' . $kecamatan . ' tidak ditemukan di server.',
                                        'details' => 'File prophet_model_' . $kecamatan . '.pkl tidak ada',
                                        'health_check' => $healthData
                                    ], 404);
                                }
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $healthError) {
                // Health check failed, but continue anyway
                \Log::warning('Health check failed, continuing with prediction request', [
                    'error' => $healthError->getMessage()
                ]);
            }
            
            // Flask API URL from config
            $apiUrl = rtrim($flaskApiUrl, '/') . config('flask.endpoints.predict');
            
            \Log::info('Calling Flask API', [
                'url' => $apiUrl,
                'kecamatan' => $kecamatan,
                'weeks_historical' => $weeksHistorical,
                'weeks_forecast' => $weeksForecast,
                'date_mode' => $dateMode
            ]);
            
            // Prepare request data
            $requestData = [
                'kecamatan' => $kecamatan,
                'weeks_historical' => (int)$weeksHistorical,
                'weeks_forecast' => (int)$weeksForecast,
                'date_mode' => $dateMode
            ];
            
            if ($dateMode === 'custom' && $customDate) {
                $requestData['custom_date'] = $customDate;
            }
            
            // Make HTTP request to Flask API
            $client = new \GuzzleHttp\Client([
                'timeout' => config('flask.timeout', 120),
                'connect_timeout' => config('flask.connect_timeout', 10),
                'http_errors' => false // Don't throw exception on 4xx/5xx
            ]);
            
            $response = $client->post($apiUrl, [
                'json' => $requestData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            \Log::info('Flask API response', [
                'status' => $statusCode,
                'body_length' => strlen($body),
                'body_preview' => substr($body, 0, 200)
            ]);
            
            $data = json_decode($body, true);
            
            if ($statusCode !== 200) {
                \Log::error('Flask API error response', [
                    'status_code' => $statusCode,
                    'response' => $data,
                    'kecamatan' => $kecamatan
                ]);
                
                // Jika 404, kemungkinan model tidak ada
                if ($statusCode === 404) {
                    return response()->json([
                        'error' => 'Model tidak tersedia',
                        'message' => 'Model prediksi untuk ' . $kecamatan . ' tidak ditemukan di server.',
                        'details' => $data['error'] ?? 'Model file not found',
                        'suggestion' => 'Pastikan semua file model (.pkl) sudah diupload ke server di folder python/models/',
                        'required_file' => 'prophet_model_' . $kecamatan . '.pkl'
                    ], 404);
                }
                
                return response()->json([
                    'error' => 'Failed to generate prediction',
                    'message' => $data['error'] ?? $data['message'] ?? 'Unknown error',
                    'details' => 'Flask API returned status ' . $statusCode
                ], $statusCode);
            }
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'body' => $body
                ]);
                
                return response()->json([
                    'error' => 'Invalid JSON response from API',
                    'json_error' => json_last_error_msg()
                ], 500);
            }
            
            // Check if API returned an error
            if (isset($data['error']) || !isset($data['success']) || !$data['success']) {
                // Log detailed error untuk debugging
                \Log::error('Flask API returned error', [
                    'error' => $data['error'] ?? 'Unknown error',
                    'message' => $data['message'] ?? '',
                    'full_response' => $data
                ]);
                
                $errorMessage = $data['error'] ?? 'Unknown error';
                
                // Check jika model not found
                if (strpos($errorMessage, 'Model file not found') !== false || 
                    strpos($errorMessage, 'Error loading model') !== false) {
                    return response()->json([
                        'error' => 'Model tidak ditemukan',
                        'message' => 'Model untuk kecamatan ' . $kecamatan . ' tidak tersedia di server. Hubungi administrator.',
                        'details' => $errorMessage,
                        'suggestion' => 'Pastikan file prophet_model_' . $kecamatan . '.pkl ada di folder python/models/ di server'
                    ], 404);
                }
                
                return response()->json([
                    'error' => $errorMessage,
                    'message' => $data['message'] ?? '',
                    'kecamatan' => $kecamatan
                ], 500);
            }
            
            // Add holiday information to forecast data
            if (isset($data['forecast']) && is_array($data['forecast'])) {
                \Log::info('Adding holiday info to ' . count($data['forecast']) . ' forecast items');
                
                foreach ($data['forecast'] as &$forecastItem) {
                    if (isset($forecastItem['date'])) {
                        try {
                            $date = Carbon::parse($forecastItem['date']);
                            
                            // Get week start (Sunday) and week end (Saturday) for this date
                            $weekStart = $date->copy()->startOfWeek(Carbon::SUNDAY);
                            $weekEnd = $date->copy()->endOfWeek(Carbon::SATURDAY);
                            
                            // Get holiday summary for the entire week
                            $holiday = IndonesianHoliday::getHolidaySummary(
                                $weekStart->format('Y-m-d'),
                                $weekEnd->format('Y-m-d')
                            );
                            
                            $forecastItem['holiday'] = $holiday;
                            $forecastItem['is_holiday'] = $holiday !== '-';
                            
                            \Log::info('Date: ' . $forecastItem['date'] . ' | Week: ' . $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d') . ' | Holiday: ' . $holiday);
                        } catch (\Exception $e) {
                            \Log::error('Error getting holiday for ' . $forecastItem['date'], [
                                'error' => $e->getMessage()
                            ]);
                            $forecastItem['holiday'] = '-';
                            $forecastItem['is_holiday'] = false;
                        }
                    }
                }
                unset($forecastItem); // Break reference
                
                \Log::info('Holiday info added. Sample:', [
                    'first_item' => $data['forecast'][0] ?? null
                ]);
            }
            
            return response()->json($data);
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            \Log::error('Flask API connection error', [
                'message' => $e->getMessage()
            ]);
            
            $flaskApiUrl = config('flask.api_url');
            
            return response()->json([
                'error' => 'Cannot connect to Flask API',
                'message' => "Pastikan Flask API server sedang berjalan di {$flaskApiUrl}",
                'details' => 'Jalankan: python app.py di folder python/'
            ], 503);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Flask API request error', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            
            return response()->json([
                'error' => 'Flask API request failed',
                'message' => $e->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Visualization error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error generating prediction',
                'message' => $e->getMessage(),
                'details' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
    
    /**
     * Get holiday information for a specific date
     */
    public function getHoliday($date)
    {
        try {
            // Validate date format
            $parsedDate = \Carbon\Carbon::parse($date);
            
            $holiday = IndonesianHoliday::getHoliday($parsedDate);
            
            return response()->json([
                'date' => $parsedDate->format('Y-m-d'),
                'holiday' => $holiday,
                'is_holiday' => $holiday !== null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid date format',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
