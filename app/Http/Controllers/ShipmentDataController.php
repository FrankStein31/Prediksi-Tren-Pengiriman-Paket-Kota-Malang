<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentData;
use Yajra\DataTables\Facades\DataTables;

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
}
