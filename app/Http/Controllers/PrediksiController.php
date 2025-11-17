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

    /**
     * Upload dan preview data Excel
     */
    public function uploadPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240' // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $filename = 'upload_' . time() . '.' . $file->getClientOriginalExtension();
            $uploadPath = storage_path('app/uploads');
            
            // Buat folder jika belum ada
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Simpan file
            $file->move($uploadPath, $filename);
            $filePath = $uploadPath . '/' . $filename;

            // Baca Excel menggunakan Python
            $command = [
                $this->pythonPath,
                $this->scriptsPath . '/preprocess.py',
                '--preview',
                '--file', $filePath
            ];

            $result = Process::run($command, timeout: 60);

            if ($result->successful()) {
                $output = json_decode($result->output(), true);
                
                // Simpan path file di session untuk proses selanjutnya
                session(['uploaded_file' => $filePath]);

                return response()->json([
                    'success' => true,
                    'preview' => $output,
                    'message' => 'File uploaded successfully'
                ]);
            } else {
                // Hapus file jika gagal
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to process file',
                    'details' => $result->errorOutput()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error uploading file',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses data yang sudah diupload (preprocessing + training)
     */
    public function processUpload(Request $request)
    {
        try {
            $filePath = session('uploaded_file');
            
            if (!$filePath || !file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No uploaded file found. Please upload a file first.'
                ], 400);
            }

            // Jalankan preprocessing
            $preprocessCommand = [
                $this->pythonPath,
                $this->scriptsPath . '/preprocess.py',
                '--process',
                '--file', $filePath
            ];

            $preprocessResult = Process::run($preprocessCommand, timeout: 120);

            if (!$preprocessResult->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Preprocessing failed',
                    'details' => $preprocessResult->errorOutput()
                ], 500);
            }

            // Training ulang model
            $trainCommand = [
                $this->pythonPath,
                $this->scriptsPath . '/train_prophet.py'
            ];

            $trainResult = Process::run($trainCommand, timeout: 600); // 10 minutes

            if ($trainResult->successful()) {
                // Hapus file upload setelah berhasil
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                session()->forget('uploaded_file');

                return response()->json([
                    'success' => true,
                    'message' => 'Data processed and models trained successfully',
                    'output' => [
                        'preprocessing' => $preprocessResult->output(),
                        'training' => $trainResult->output()
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Training failed',
                    'details' => $trainResult->errorOutput()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error processing upload',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
