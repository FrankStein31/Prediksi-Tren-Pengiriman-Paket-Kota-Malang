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
     */
    public function ringkasanPage()
    {
        return view('ringkasan-mingguan');
    }

    /**
     * Get weekly summary by kecamatan
     */
    public function getRingkasan(Request $request)
    {
        $date = $request->input('date');
        
        if (!$date) {
            return response()->json(['error' => 'Tanggal tidak valid'], 400);
        }
        
        $dateObj = new \DateTime($date);
        
        // Get week number (ISO 8601 week)
        $weekNumber = (int) $dateObj->format('W');
        $year = (int) $dateObj->format('Y');
        
        // Get start and end of week
        $dto = new \DateTime();
        $dto->setISODate($year, $weekNumber);
        $weekStart = $dto->format('Y-m-d');
        
        $dto->modify('+6 days');
        $weekEnd = $dto->format('Y-m-d');
        
        // Get data for that week, grouped by kecamatan
        $data = ShipmentData::selectRaw("
            TRIM(SUBSTRING_INDEX(kota, ',', -1)) as kecamatan,
            COUNT(*) as total_paket
        ")
        ->whereBetween('tgl_kirim', [$weekStart, $weekEnd])
        ->whereRaw("TRIM(SUBSTRING_INDEX(kota, ',', -1)) != ''")
        ->groupBy('kecamatan')
        ->orderBy('total_paket', 'desc')
        ->get();
        
        // Add minggu_ke to each row
        $data = $data->map(function($item) use ($weekNumber) {
            $item->minggu_ke = $weekNumber;
            return $item;
        });
        
        $totalPaket = $data->sum('total_paket');
        $totalKecamatan = $data->count();
        $avgPaket = $totalKecamatan > 0 ? $totalPaket / $totalKecamatan : 0;
        
        return response()->json([
            'week_number' => $weekNumber,
            'week_start' => \Carbon\Carbon::parse($weekStart)->format('d M Y'),
            'week_end' => \Carbon\Carbon::parse($weekEnd)->format('d M Y'),
            'total_paket' => $totalPaket,
            'total_kecamatan' => $totalKecamatan,
            'avg_paket' => $avgPaket,
            'data' => $data
        ]);
    }

    /**
     * Get total summary for all districts from all years
     */
    public function getRingkasanTotal()
    {
        // Get total data for each kecamatan from all years
        $data = ShipmentData::selectRaw("
            TRIM(SUBSTRING_INDEX(kota, ',', -1)) as kecamatan,
            COUNT(*) as total_paket
        ")
        ->whereRaw("TRIM(SUBSTRING_INDEX(kota, ',', -1)) != ''")
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
     */
    public function getRingkasanBreakdown(Request $request)
    {
        $kecamatan = $request->input('kecamatan');
        $year = $request->input('year');
        
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        
        // Create cache key based on filter
        $cacheKey = 'ringkasan_breakdown_' . $kecamatan . '_' . ($year ?? 'all');
        
        // Try to get from cache (5 minutes)
        $data = Cache::remember($cacheKey, 300, function() use ($kecamatan, $year) {
            // Build query with proper aggregation
            $baseQuery = ShipmentData::whereRaw("TRIM(SUBSTRING_INDEX(kota, ',', -1)) = ?", [$kecamatan]);
            
            // If year is provided, filter by that specific year
            if ($year && $year !== '') {
                $baseQuery->whereYear('tgl_kirim', $year);
            }
            
            // Get all data grouped by year and week
            return $baseQuery
                ->selectRaw("
                    YEAR(tgl_kirim) as tahun,
                    WEEK(tgl_kirim, 3) as minggu_ke,
                    MIN(tgl_kirim) as min_date,
                    MAX(tgl_kirim) as max_date,
                    COUNT(*) as total_paket
                ")
                ->groupBy('tahun', 'minggu_ke')
                ->orderBy('tahun', 'desc')
                ->orderBy('minggu_ke', 'asc')
                ->get();
        });
        
        // Process and add holiday information (optimized)
        $data = $data->map(function($item) {
            $startDate = \Carbon\Carbon::parse($item->min_date);
            $endDate = \Carbon\Carbon::parse($item->max_date);
            
            // Safety check: if range > 7 days, calculate proper boundaries
            $daysDiff = $startDate->diffInDays($endDate);
            if ($daysDiff > 7) {
                // Use ISO 8601 week calculation
                $jan4 = \Carbon\Carbon::create($item->tahun, 1, 4);
                $weekStart = $jan4->startOfWeek()->addWeeks($item->minggu_ke - 1);
                $startDate = $weekStart->copy();
                $endDate = $weekStart->copy()->addDays(6);
            }
            
            // Format dates first
            $item->tanggal_mulai = $startDate->format('d/m/Y');
            $item->tanggal_akhir = $endDate->format('d/m/Y');
            
            // Get holiday (with try-catch for safety)
            try {
                $item->hari_libur = IndonesianHoliday::getHolidaySummary(
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                );
            } catch (\Exception $e) {
                $item->hari_libur = '-';
            }
            
            // Remove temporary fields
            unset($item->min_date);
            unset($item->max_date);
            
            return $item;
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
     * Get available years from shipment data
     */
    public function getRingkasanYears()
    {
        $years = ShipmentData::selectRaw('DISTINCT YEAR(tgl_kirim) as year')
            ->whereNotNull('tgl_kirim')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return response()->json([
            'years' => $years
        ]);
    }
}
