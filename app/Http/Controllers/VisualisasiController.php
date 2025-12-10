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
            'weeks_historical' => 'integer|min:1|max:104', // Default 52 weeks
            'weeks_forecast' => 'integer|min:1|max:52',    // Default 4 weeks
        ]);
        
        $kecamatan = $request->input('kecamatan');
        $weeksHistorical = $request->input('weeks_historical', 52);
        $weeksForecast = $request->input('weeks_forecast', 4);
        
        try {
            // Path to Python script
            $pythonScript = base_path('python/scripts/visualize_prophet.py');
            $pythonPath = 'python'; // Or full path if needed
            
            // Run Python script
            $result = Process::run([
                $pythonPath,
                $pythonScript,
                '--kecamatan', $kecamatan,
                '--weeks_historical', $weeksHistorical,
                '--weeks_forecast', $weeksForecast
            ]);
            
            if (!$result->successful()) {
                return response()->json([
                    'error' => 'Failed to generate prediction',
                    'message' => $result->errorOutput()
                ], 500);
            }
            
            // Parse JSON output from Python
            $output = $result->output();
            $data = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON response from Python script',
                    'raw_output' => $output
                ], 500);
            }
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generating prediction',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
