<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PrediksiController extends Controller
{
    private $pythonPath;
    private $scriptsPath;

    public function __construct()
    {
        $this->pythonPath = base_path('python/venv/Scripts/python.exe');
        $this->scriptsPath = base_path('python/scripts');
    }

    /**
     * Mendapatkan list kecamatan yang tersedia
     */
    public function getAvailableKecamatan()
    {
        try {
            $command = [
                $this->pythonPath,
                $this->scriptsPath . '/predict.py',
                '--list-kecamatan'
            ];

            $result = Process::run($command);

            if ($result->successful()) {
                $output = json_decode($result->output(), true);
                return response()->json($output);
            } else {
                return response()->json([
                    'error' => 'Failed to get available kecamatan',
                    'details' => $result->errorOutput()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error executing Python script',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melakukan prediksi untuk kecamatan tertentu
     */
    public function predict(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|string',
            'weeks' => 'integer|min:1|max:52'
        ]);

        $kecamatan = $request->input('kecamatan');
        $weeks = $request->input('weeks', 12);

        try {
            $command = [
                $this->pythonPath,
                $this->scriptsPath . '/predict.py',
                '--kecamatan', $kecamatan,
                '--weeks', $weeks
            ];

            $result = Process::run($command);

            if ($result->successful()) {
                $output = json_decode($result->output(), true);
                return response()->json($output);
            } else {
                return response()->json([
                    'error' => 'Prediction failed',
                    'details' => $result->errorOutput()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error executing Python script',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Train model (jalankan sekali saja)
     */
    public function trainModel()
    {
        try {
            $command = [
                $this->pythonPath,
                $this->scriptsPath . '/train_prophet.py'
            ];

            $result = Process::run($command, timeout: 300); // 5 minutes timeout

            if ($result->successful()) {
                return response()->json([
                    'message' => 'Model training completed successfully',
                    'output' => $result->output()
                ]);
            } else {
                return response()->json([
                    'error' => 'Training failed',
                    'details' => $result->errorOutput()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error executing training script',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
