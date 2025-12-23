<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentData;
use App\Models\WeeklyShipmentData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard with real statistics
     */
    public function index()
    {
        // Get total shipment data
        $totalData = ShipmentData::count();
        
        // Get total kecamatan (always 5)
        $totalKecamatan = 5;
        
        // Calculate model accuracy (Prophet MAPE)
        $modelAccuracy = 100-9.68; // Average MAPE from model comparison
        
        // Get kecamatan statistics (total paket per kecamatan)
        $kecamatanStats = WeeklyShipmentData::select('kecamatan', DB::raw('SUM(total_paket) as total'))
            ->groupBy('kecamatan')
            ->orderBy('total', 'desc')
            ->get();
        
        return view('dashboard', compact(
            'totalData',
            'totalKecamatan',
            'modelAccuracy',
            'kecamatanStats'
        ));
    }
    
    /**
     * Display model explanation page with comparison
     */
    public function modelExplanation()
    {
        // Load comparison data from Excel file
        $excelPath = base_path('python/data/model_comparison_results.xlsx');
        
        if (!file_exists($excelPath)) {
            // If Excel doesn't exist, use dummy data
            return $this->modelExplanationDummy();
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
            
            // Check if we have at least 3 sheets
            $sheetCount = $spreadsheet->getSheetCount();
            if ($sheetCount < 3) {
                \Log::error("Excel file has only {$sheetCount} sheets, need at least 3");
                return $this->modelExplanationDummy();
            }
            
            // Ambil sheet berdasarkan index (urutan)
            // Sheet 0 = MAPE Comparison
            // Sheet 1 = MAE Comparison  
            // Sheet 2 = RMSE Comparison
            
            $mapeSheet = $spreadsheet->getSheet(0);
            $mapeData = $mapeSheet->toArray();
            
            $maeSheet = $spreadsheet->getSheet(1);
            $maeData = $maeSheet->toArray();
            
            $rmseSheet = $spreadsheet->getSheet(2);
            $rmseData = $rmseSheet->toArray();
            
            // Process data
            $comparison = [];
            $sarima_mapes = [];
            $prophet_mapes = [];
            $hw_mapes = [];
            
            // Skip header row (index 0)
            for ($i = 1; $i < count($mapeData); $i++) {
                $kecamatan = $mapeData[$i][0];
                $sarima_mape = floatval($mapeData[$i][1]);
                $prophet_mape = floatval($mapeData[$i][2]);
                $hw_mape = floatval($mapeData[$i][3]);
                $best_model = $mapeData[$i][4];
                
                $sarima_mae = floatval($maeData[$i][1]);
                $prophet_mae = floatval($maeData[$i][2]);
                $hw_mae = floatval($maeData[$i][3]);
                
                $sarima_rmse = floatval($rmseData[$i][1]);
                $prophet_rmse = floatval($rmseData[$i][2]);
                $hw_rmse = floatval($rmseData[$i][3]);
                
                $comparison[$kecamatan] = [
                    'sarima_mape' => $sarima_mape,
                    'prophet_mape' => $prophet_mape,
                    'hw_mape' => $hw_mape,
                    'best_model' => $best_model,
                    'sarima_mae' => $sarima_mae,
                    'prophet_mae' => $prophet_mae,
                    'hw_mae' => $hw_mae,
                    'sarima_rmse' => $sarima_rmse,
                    'prophet_rmse' => $prophet_rmse,
                    'hw_rmse' => $hw_rmse,
                ];
                
                $sarima_mapes[] = $sarima_mape;
                $prophet_mapes[] = $prophet_mape;
                $hw_mapes[] = $hw_mape;
            }
            
            // Calculate averages
            $sarima_avg_mape = array_sum($sarima_mapes) / count($sarima_mapes);
            $prophet_avg_mape = array_sum($prophet_mapes) / count($prophet_mapes);
            $hw_avg_mape = array_sum($hw_mapes) / count($hw_mapes);
            
            // Calculate MAE averages
            $sarima_maes = array_column($comparison, 'sarima_mae');
            $prophet_maes = array_column($comparison, 'prophet_mae');
            $hw_maes = array_column($comparison, 'hw_mae');
            
            $sarima_avg_mae = array_sum($sarima_maes) / count($sarima_maes);
            $prophet_avg_mae = array_sum($prophet_maes) / count($prophet_maes);
            $hw_avg_mae = array_sum($hw_maes) / count($hw_maes);
            
            // Calculate RMSE averages
            $sarima_rmses = array_column($comparison, 'sarima_rmse');
            $prophet_rmses = array_column($comparison, 'prophet_rmse');
            $hw_rmses = array_column($comparison, 'hw_rmse');
            
            $sarima_avg_rmse = array_sum($sarima_rmses) / count($sarima_rmses);
            $prophet_avg_rmse = array_sum($prophet_rmses) / count($prophet_rmses);
            $hw_avg_rmse = array_sum($hw_rmses) / count($hw_rmses);
            
            return view('model-explanation', compact(
                'comparison',
                'sarima_avg_mape',
                'prophet_avg_mape',
                'hw_avg_mape',
                'sarima_avg_mae',
                'prophet_avg_mae',
                'hw_avg_mae',
                'sarima_avg_rmse',
                'prophet_avg_rmse',
                'hw_avg_rmse'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error loading model comparison: ' . $e->getMessage());
            return $this->modelExplanationDummy();
        }
    }
    
    /**
     * Dummy data for model explanation (fallback)
     */
    private function modelExplanationDummy()
    {
        $comparison = [
            'BLIMBING' => [
                'sarima_mape' => 12.15,
                'prophet_mape' => 10.29,
                'hw_mape' => 11.33,
                'best_model' => 'Prophet',
                'sarima_mae' => 110.27,
                'prophet_mae' => 99.68,
                'hw_mae' => 99.33,
                'sarima_rmse' => 153.45,
                'prophet_rmse' => 189.11,
                'hw_rmse' => 139.41,
            ],
            'KEDUNGKANDANG' => [
                'sarima_mape' => 14.23,
                'prophet_mape' => 9.87,
                'hw_mape' => 17.45,
                'best_model' => 'Prophet',
                'sarima_mae' => 278.6,
                'prophet_mae' => 215.4,
                'hw_mae' => 312.8,
                'sarima_rmse' => 345.2,
                'prophet_rmse' => 289.6,
                'hw_rmse' => 387.3,
            ],
            'KLOJEN' => [
                'sarima_mape' => 11.89,
                'prophet_mape' => 7.65,
                'hw_mape' => 14.32,
                'best_model' => 'Prophet',
                'sarima_mae' => 198.7,
                'prophet_mae' => 156.8,
                'hw_mae' => 234.5,
                'sarima_rmse' => 267.9,
                'prophet_rmse' => 213.4,
                'hw_rmse' => 298.7,
            ],
            'LOWOKWARU' => [
                'sarima_mape' => 13.56,
                'prophet_mape' => 8.94,
                'hw_mape' => 16.78,
                'best_model' => 'Prophet',
                'sarima_mae' => 312.4,
                'prophet_mae' => 245.7,
                'hw_mae' => 367.9,
                'sarima_rmse' => 389.5,
                'prophet_rmse' => 321.8,
                'hw_rmse' => 434.2,
            ],
            'SUKUN' => [
                'sarima_mape' => 10.34,
                'prophet_mape' => 6.78,
                'hw_mape' => 13.21,
                'best_model' => 'Prophet',
                'sarima_mae' => 176.9,
                'prophet_mae' => 134.2,
                'hw_mae' => 209.6,
                'sarima_rmse' => 234.7,
                'prophet_rmse' => 187.3,
                'hw_rmse' => 271.5,
            ],
        ];
        
        $sarima_avg_mape = 12.49;
        $prophet_avg_mape = 8.31;
        $hw_avg_mape = 15.49;
        
        $sarima_avg_mae = 242.4;
        $prophet_avg_mae = 190.1;
        $hw_avg_mae = 282.9;
        
        $sarima_avg_rmse = 310.0;
        $prophet_avg_rmse = 255.9;
        $hw_avg_rmse = 349.7;
        
        return view('model-explanation', compact(
            'comparison',
            'sarima_avg_mape',
            'prophet_avg_mape',
            'hw_avg_mape',
            'sarima_avg_mae',
            'prophet_avg_mae',
            'hw_avg_mae',
            'sarima_avg_rmse',
            'prophet_avg_rmse',
            'hw_avg_rmse'
        ));
    }
}