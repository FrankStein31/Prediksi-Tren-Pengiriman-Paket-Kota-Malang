<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentData;
use App\Models\UploadHistory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UploadDataController extends Controller
{
    /**
     * Display upload page
     */
    public function index()
    {
        return view('upload-data');
    }
    
    /**
     * Display upload history page
     */
    public function history()
    {
        $histories = UploadHistory::orderBy('created_at', 'desc')->paginate(15);
        
        return view('upload-history', compact('histories'));
    }
    
    /**
     * Process uploaded file and check for duplicates
     */
    public function process(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:512000', // 500MB
        ]);
        
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        try {
            // Store file info in session for later use in import
            session([
                'upload_file_info' => [
                    'filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'size' => $file->getSize(),
                ]
            ]);
            
            // Read file based on type
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = $this->readExcel($file);
            } else {
                $data = $this->readCsv($file);
            }
            
            // Check for duplicates
            $result = $this->checkDuplicates($data);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Read Excel file and convert to array
     */
    private function readExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        if (empty($rows)) {
            throw new \Exception('File kosong');
        }
        
        // Get headers (first row)
        $headers = array_shift($rows);
        
        // Map column headers to database columns
        $columnMapping = [
            'nosi' => ['nosi', 'no si', 'no_si'],
            'posisi_saat_ini' => ['posisi saat ini', 'posisi_saat_ini', 'posisi'],
            'status_kiriman' => ['status kiriman', 'status_kiriman', 'status'],
            'produk' => ['produk', 'product'],
            'sla' => ['sla'],
            'kantor_kirim' => ['kantor kirim', 'kantor_kirim'],
            'tgl_kirim' => ['tgl kirim', 'tgl_kirim', 'tanggal kirim'],
            'tgl_antaran_pertama' => ['tgl antaran pertama', 'tgl_antaran_pertama', 'tanggal antaran'],
            'tgl_update' => ['tgl update', 'tgl_update', 'tanggal update'],
            'petugas' => ['petugas', 'kurir'],
            'nama_penerima' => ['nama penerima', 'nama_penerima', 'penerima'],
            'alamat' => ['alamat', 'address'],
            'kota' => ['kota', 'kecamatan', 'city'],
            'alasan_gagal' => ['alasan gagal', 'alasan_gagal'],
            'alasan_irregulitas' => ['alasan irregulitas', 'alasan_irregulitas'],
            'status_swp' => ['status swp', 'status_swp'],
            'berat' => ['berat', 'weight'],
            'cek' => ['cek', 'check'],
        ];
        
        // Normalize headers
        $normalizedHeaders = array_map(function($header) use ($columnMapping) {
            $header = strtolower(trim($header));
            foreach ($columnMapping as $dbColumn => $variants) {
                if (in_array($header, $variants)) {
                    return $dbColumn;
                }
            }
            return $header;
        }, $headers);
        
        // Convert rows to associative array
        $data = [];
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue; // Skip empty rows
            
            $rowData = [];
            foreach ($normalizedHeaders as $index => $column) {
                $value = isset($row[$index]) ? $row[$index] : null;
                
                // Convert date formats
                if (in_array($column, ['tgl_kirim', 'tgl_antaran_pertama', 'tgl_update']) && $value) {
                    $value = $this->convertDate($value);
                }
                
                $rowData[$column] = $value;
            }
            $data[] = $rowData;
        }
        
        return $data;
    }
    
    /**
     * Read CSV file
     */
    private function readCsv($file)
    {
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        
        $records = $csv->getRecords();
        $data = [];
        
        foreach ($records as $record) {
            // Convert date formats
            if (isset($record['tgl_kirim'])) {
                $record['tgl_kirim'] = $this->convertDate($record['tgl_kirim']);
            }
            if (isset($record['tgl_antaran_pertama'])) {
                $record['tgl_antaran_pertama'] = $this->convertDate($record['tgl_antaran_pertama']);
            }
            if (isset($record['tgl_update'])) {
                $record['tgl_update'] = $this->convertDate($record['tgl_update']);
            }
            
            $data[] = $record;
        }
        
        return $data;
    }
    
    /**
     * Convert various date formats to Y-m-d
     */
    private function convertDate($date)
    {
        if (empty($date)) return null;
        
        try {
            // Try various formats
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
            
            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat($format, $date);
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d');
                }
            }
            
            // Try strtotime as fallback
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
            
            return $date;
        } catch (\Exception $e) {
            return $date;
        }
    }
    
    /**
     * Check for duplicates by checking NOSI only
     */
    private function checkDuplicates($data)
    {
        $newData = [];
        $duplicateData = [];
        $duplicateCount = 0;
        
        \Log::info('=== STARTING DUPLICATE CHECK ===');
        \Log::info('Total rows to check: ' . count($data));
        \Log::info('Checking duplicates based on NOSI only');
        
        foreach ($data as $index => $row) {
            // Check if NOSI exists in database
            $nosi = $row['nosi'] ?? null;
            $exists = false;
            
            if ($nosi) {
                $exists = ShipmentData::where('nosi', $nosi)->exists();
            }
            
            // Log info untuk SETIAP baris
            $status = $exists ? 'DUPLIKAT' : 'BARU';
            \Log::info("Row " . ($index + 1) . ": NOSI={$nosi} | Status={$status}");
            
            // Add flags to the row (must be stored back to $data array)
            $data[$index]['is_duplicate'] = $exists;
            $data[$index]['status'] = $exists ? 'Duplikat' : 'Baru';
            
            if ($exists) {
                $duplicateCount++;
                $duplicateData[] = $data[$index];
            } else {
                $newData[] = $data[$index];
            }
        }
        
        \Log::info('=== DUPLICATE CHECK COMPLETE ===');
        \Log::info('Total: ' . count($data) . ' | New: ' . count($newData) . ' | Duplicate: ' . $duplicateCount);
        
        // Store for session or cache for DataTables
        session(['upload_preview' => $data]);
        
        return [
            'total_rows' => count($data),
            'new_rows' => count($newData),
            'duplicate_rows' => $duplicateCount,
            'data' => $newData,
            'all_data' => $data, // All data with duplicate status
        ];
    }
    
    /**
     * Get preview data for Yajra DataTables (server-side, max 50 rows)
     */
    public function getPreviewData(Request $request)
    {
        $data = session('upload_preview', []);
        
        // Sort: Status Baru duluan (false = Baru, true = Duplikat), lalu Tanggal Kirim descending
        usort($data, function($a, $b) {
            $isDuplicateA = isset($a['is_duplicate']) ? $a['is_duplicate'] : false;
            $isDuplicateB = isset($b['is_duplicate']) ? $b['is_duplicate'] : false;
            
            // Sort by status first (Baru = false comes first)
            if ($isDuplicateA !== $isDuplicateB) {
                return $isDuplicateA ? 1 : -1; // false (Baru) comes first
            }
            
            // If same status, sort by tgl_kirim descending (newest first)
            $dateA = isset($a['tgl_kirim']) ? strtotime($a['tgl_kirim']) : 0;
            $dateB = isset($b['tgl_kirim']) ? strtotime($b['tgl_kirim']) : 0;
            
            return $dateB - $dateA; // Descending (newest first)
        });
        
        // Limit to 50 rows for preview to keep it light
        $previewData = array_slice($data, 0, 50);
        
        \Log::info('Preview request - Total rows: ' . count($data) . ' | Showing: ' . count($previewData) . ' (Sorted: Baru first, then by tgl_kirim desc)');
        
        return datatables()
            ->of(collect($previewData))
            ->addColumn('status_badge', function($row) {
                $isDuplicate = isset($row['is_duplicate']) ? $row['is_duplicate'] : false;
                if ($isDuplicate) {
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Duplikat</span>';
                }
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Baru</span>';
            })
            ->addColumn('is_duplicate_flag', function($row) {
                return isset($row['is_duplicate']) ? $row['is_duplicate'] : false;
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }
    
    /**
     * Import new data to database
     */
    public function import(Request $request)
    {
        $data = $request->input('data', []);
        
        if (empty($data)) {
            return response()->json(['error' => 'Tidak ada data untuk diimport'], 400);
        }
        
        try {
            $imported = 0;
            $skipped = 0;
            
            // Get file info from session
            $fileInfo = session('upload_file_info', []);
            $allData = session('upload_preview', []);
            
            // Import in chunks for better performance
            $chunks = array_chunk($data, 500);
            
            foreach ($chunks as $chunk) {
                foreach ($chunk as $row) {
                    // Remove flags
                    unset($row['is_duplicate']);
                    unset($row['status']);
                    
                    // Only insert if NOSI not exists (double check)
                    $nosi = $row['nosi'] ?? null;
                    $exists = false;
                    
                    if ($nosi) {
                        $exists = ShipmentData::where('nosi', $nosi)->exists();
                    }
                    
                    if (!$exists) {
                        ShipmentData::create($row);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
                
                // Small delay to prevent memory issues
                usleep(10000); // 10ms
                gc_collect_cycles();
            }
            
            // Save upload history to database
            UploadHistory::create([
                'filename' => $fileInfo['filename'] ?? 'unknown',
                'file_extension' => $fileInfo['extension'] ?? 'unknown',
                'file_size' => $fileInfo['size'] ?? 0,
                'total_rows' => count($allData),
                'new_rows' => $imported,
                'duplicate_rows' => count($allData) - $imported,
                'skipped_rows' => $skipped,
                'notes' => "Import berhasil: {$imported} data baru ditambahkan, {$skipped} data duplikat diskip.",
            ]);
            
            // Clear session
            session()->forget('upload_preview');
            session()->forget('upload_file_info');
            
            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }
}
