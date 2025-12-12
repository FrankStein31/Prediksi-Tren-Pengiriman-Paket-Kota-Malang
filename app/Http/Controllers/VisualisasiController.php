<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;

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
            'weeks_historical' => 'integer|min:12|max:104', // Default 52 weeks
            'weeks_forecast' => 'integer|min:1|max:52',    // Default 4 weeks
        ]);
        
        $kecamatan = $request->input('kecamatan');
        $weeksHistorical = $request->input('weeks_historical', 52);
        $weeksForecast = $request->input('weeks_forecast', 4);
        
        try {
            // Flask API URL
            $apiUrl = 'http://127.0.0.1:5000/api/predict';
            
            // Prepare request data
            $requestData = [
                'kecamatan' => $kecamatan,
                'weeks_historical' => (int)$weeksHistorical,
                'weeks_forecast' => (int)$weeksForecast
            ];
            
            // Make HTTP request to Flask API
            $client = new \GuzzleHttp\Client([
                'timeout' => 120, // 2 minutes timeout
                'connect_timeout' => 10
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
            $data = json_decode($body, true);
            
            if ($statusCode !== 200) {
                \Log::error('Flask API error', [
                    'status_code' => $statusCode,
                    'response' => $data
                ]);
                
                return response()->json([
                    'error' => 'Failed to generate prediction',
                    'message' => $data['error'] ?? $data['message'] ?? 'Unknown error',
                    'details' => 'Pastikan Flask API sedang berjalan'
                ], 500);
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
                return response()->json([
                    'error' => $data['error'] ?? 'Unknown error',
                    'message' => $data['message'] ?? ''
                ], 500);
            }
            
            return response()->json($data);
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            \Log::error('Flask API connection error', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Cannot connect to Flask API',
                'message' => 'Pastikan Flask API server sedang berjalan di http://127.0.0.1:5000',
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
}
