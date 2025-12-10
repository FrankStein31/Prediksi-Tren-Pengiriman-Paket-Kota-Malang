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
            
            // Use Laragon Python path
            $pythonPath = 'C:\\laragon\\bin\\python\\python-3.10\\python.exe';
            
            // Fallback to PATH if Laragon python not found
            if (!file_exists($pythonPath)) {
                $pythonPath = 'python';
            }
            
            // Build command
            $command = sprintf(
                '"%s" "%s" --kecamatan "%s" --weeks_historical %d --weeks_forecast %d',
                $pythonPath,
                $pythonScript,
                $kecamatan,
                $weeksHistorical,
                $weeksForecast
            );
            
            // Run Python script with timeout
            $result = Process::timeout(120) // 2 minutes timeout
                ->run($command);
            
            if (!$result->successful()) {
                return response()->json([
                    'error' => 'Failed to generate prediction',
                    'message' => $result->errorOutput(),
                    'command' => $command // For debugging
                ], 500);
            }
            
            // Parse JSON output from Python
            $output = $result->output();
            
            // Remove any potential debug output before JSON
            $jsonStart = strpos($output, '{');
            if ($jsonStart !== false) {
                $output = substr($output, $jsonStart);
            }
            
            $data = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON response from Python script',
                    'json_error' => json_last_error_msg(),
                    'raw_output' => $output
                ], 500);
            }
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generating prediction',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
