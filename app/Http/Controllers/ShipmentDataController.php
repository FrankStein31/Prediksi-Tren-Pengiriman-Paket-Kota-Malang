<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentData;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\IndonesianHoliday;
use Illuminate\Support\Facades\Cache;

class ShipmentDataController extends Controller
{
    /**
     * Display the data pengiriman page
     */
    public function index()
    {
        return view('data-pengiriman');
    }
    
    /**
     * Get data for DataTables - Optimized for Yajra v12.0
     * Reference: https://yajrabox.com/docs/laravel-datatables/12.0
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Use query builder for better performance
            $data = ShipmentData::query();
            
            // Apply filters
            if ($request->filled('start_date')) {
                $data->whereDate('tgl_kirim', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $data->whereDate('tgl_kirim', '<=', $request->end_date);
            }
            
            if ($request->filled('status_swp')) {
                $data->where('status_swp', $request->status_swp);
            }
            
            if ($request->filled('kecamatan')) {
                $data->where('kota', 'LIKE', '%' . $request->kecamatan . '%');
            }
            
            return DataTables::of($data)
                ->addIndexColumn()
                
                // Format date columns
                ->editColumn('tgl_kirim', function($row) {
                    return $row->tgl_kirim ? $row->tgl_kirim->format('d/m/Y') : '-';
                })
                ->editColumn('tgl_antaran_pertama', function($row) {
                    return $row->tgl_antaran_pertama ? $row->tgl_antaran_pertama->format('d/m/Y') : '-';
                })
                ->editColumn('tgl_update', function($row) {
                    return $row->tgl_update ? $row->tgl_update->format('d/m/Y') : '-';
                })
                
                // Format status with clean badge
                ->editColumn('status_kiriman', function($row) {
                    if (!$row->status_kiriman) return '-';
                    
                    $badges = [
                        'DELIVERED' => 'status-delivered',
                        'ON PROCESS' => 'status-process',
                        'RETURN' => 'status-return',
                        'PENDING' => 'status-pending',
                    ];
                    
                    $class = $badges[$row->status_kiriman] ?? '';
                    
                    return '<span class="status-badge '.$class.'">'.e($row->status_kiriman).'</span>';
                })
                
                // Format berat
                ->editColumn('berat', function($row) {
                    return $row->berat ? number_format($row->berat, 2) . ' kg' : '-';
                })
                
                // Handle null values
                ->editColumn('nosi', function($row) {
                    return $row->nosi ?? '-';
                })
                ->editColumn('produk', function($row) {
                    return $row->produk ?? '-';
                })
                ->editColumn('sla', function($row) {
                    return $row->sla ?? '-';
                })
                ->editColumn('kantor_kirim', function($row) {
                    return $row->kantor_kirim ?? '-';
                })
                ->editColumn('petugas', function($row) {
                    return $row->petugas ?? '-';
                })
                ->editColumn('nama_penerima', function($row) {
                    return $row->nama_penerima ?? '-';
                })
                ->editColumn('alamat', function($row) {
                    return $row->alamat ?? '-';
                })
                ->editColumn('kota', function($row) {
                    return $row->kota ?? '-';
                })
                ->editColumn('posisi_saat_ini', function($row) {
                    return $row->posisi_saat_ini ?? '-';
                })
                ->editColumn('alasan_gagal', function($row) {
                    return $row->alasan_gagal ?? '-';
                })
                ->editColumn('alasan_irregulitas', function($row) {
                    return $row->alasan_irregulitas ?? '-';
                })
                ->editColumn('status_swp', function($row) {
                    return $row->status_swp ?? '-';
                })
                ->editColumn('cek', function($row) {
                    return $row->cek ?? '-';
                })
                
                // Allow HTML for status badge
                ->rawColumns(['status_kiriman'])
                ->make(true);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }
    
    /**
     * Delete shipment data
     */
    public function destroy($id)
    {
        try {
            $shipment = ShipmentData::findOrFail($id);
            $nosi = $shipment->nosi;
            $shipment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data dengan NOSI ' . $nosi . ' berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        $totalData = ShipmentData::count();
        $delivered = ShipmentData::where('status_kiriman', 'DELIVERED')->count();
        $onProcess = ShipmentData::where('status_kiriman', 'ON PROCESS')->count();
        $returned = ShipmentData::where('status_kiriman', 'RETURN')->count();
        
        return response()->json([
            'total' => $totalData,
            'delivered' => $delivered,
            'on_process' => $onProcess,
            'returned' => $returned,
        ]);
    }

    /**
     * Display ringkasan mingguan page
     * NEW LOGIC: Auto-aggregate if new data exists, then display from weekly_shipment_data table
     */
    public function ringkasanPage()
    {
        // Auto-check and aggregate new data for all kecamatan
        $this->autoAggregateIfNeeded();
        
        return view('ringkasan-mingguan');
    }

    /**
     * Auto-aggregate if there's new shipment data that hasn't been aggregated yet
     */
    private function autoAggregateIfNeeded()
    {
        $kecamatans = ['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN'];
        
        foreach ($kecamatans as $kecamatan) {
            // Get latest date in weekly_shipment_data for this kecamatan
            $latestWeekly = \App\Models\WeeklyShipmentData::where('kecamatan', $kecamatan)
                ->orderBy('week_end', 'desc')
                ->first();
            
            // Get latest date in shipment_data for this kecamatan
            $latestShipment = ShipmentData::whereRaw("TRIM(SUBSTRING_INDEX(kota, ',', -1)) = ?", [$kecamatan])
                ->orderBy('tgl_kirim', 'desc')
                ->first();
            
            // If no weekly data yet OR shipment data is newer, trigger aggregation
            if (!$latestWeekly || ($latestShipment && $latestShipment->tgl_kirim > $latestWeekly->week_end)) {
                // Call aggregation service
                $service = new \App\Services\WeeklyDataAggregationService();
                $service->aggregateWeeklyData($kecamatan);
            }
        }
    }



    /**
     * Get total summary for all districts from all years
     * NEW LOGIC: Read from weekly_shipment_data table (optimized)
     */
    public function getRingkasanTotal()
    {
        // Get total data for each kecamatan from weekly_shipment_data table
        $data = \App\Models\WeeklyShipmentData::selectRaw("
            kecamatan,
            SUM(total_paket) as total_paket
        ")
        ->groupBy('kecamatan')
        ->orderBy('total_paket', 'desc')
        ->get();
        
        $totalPaket = $data->sum('total_paket');
        $totalKecamatan = $data->count();
        $avgPaket = $totalKecamatan > 0 ? $totalPaket / $totalKecamatan : 0;
        
        return response()->json([
            'total_paket' => $totalPaket,
            'total_kecamatan' => $totalKecamatan,
            'avg_paket' => $avgPaket,
            'data' => $data
        ]);
    }

    /**
     * Get breakdown by year and week for specific district
     * NEW LOGIC: Read from weekly_shipment_data table (optimized, no heavy query)
     */
    public function getRingkasanBreakdown(Request $request)
    {
        $kecamatan = $request->input('kecamatan');
        $year = $request->input('year');
        
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        
        // Build query from weekly_shipment_data table
        $query = \App\Models\WeeklyShipmentData::where('kecamatan', $kecamatan);
        
        // If year is provided, filter by that specific year
        if ($year && $year !== '') {
            $query->where('year', $year);
        }
        
        // Get all data ordered by year and week
        $data = $query
            ->orderBy('year', 'desc')
            ->orderBy('week_number', 'asc')
            ->get();
        
        // Format data to match old format
        $data = $data->map(function($item) {
            // Get holiday info if available
            $hariLibur = '-';
            try {
                $hariLibur = IndonesianHoliday::getHolidaySummary(
                    $item->week_start->format('Y-m-d'),
                    $item->week_end->format('Y-m-d')
                );
            } catch (\Exception $e) {
                // Keep default if error
            }
            
            return [
                'tahun' => $item->year,
                'minggu_ke' => $item->week_number,
                'tanggal_mulai' => $item->week_start->format('d/m/Y'),
                'tanggal_akhir' => $item->week_end->format('d/m/Y'),
                'total_paket' => $item->total_paket,
                'hari_libur' => $hariLibur
            ];
        });
        
        $totalPaket = $data->sum('total_paket');
        $totalWeeks = $data->count();
        $avgPaket = $totalWeeks > 0 ? $totalPaket / $totalWeeks : 0;
        
        return response()->json([
            'kecamatan' => $kecamatan,
            'year' => $year,
            'total_paket' => $totalPaket,
            'total_weeks' => $totalWeeks,
            'avg_paket' => $avgPaket,
            'data' => $data
        ]);
    }

    /**
     * Get available years from weekly_shipment_data (optimized)
     */
    public function getRingkasanYears()
    {
        $years = \App\Models\WeeklyShipmentData::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return response()->json([
            'years' => $years
        ]);
    }
}
