<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentData;
use App\Models\UploadHistory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\ChunkReadFilter;
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
     * Get upload progress for real-time updates
     */
    public function getProgress()
    {
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        
        if (file_exists($progressFile)) {
            $content = file_get_contents($progressFile);
            return response()->json(json_decode($content, true));
        }
        
        return response()->json([
            'status' => 'idle',
            'current' => 0,
            'total' => 0,
            'log' => ''
        ]);
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
        
        // Initialize progress tracking
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        file_put_contents($progressFile, json_encode([
            'status' => 'reading',
            'current' => 0,
            'total' => 0,
            'log' => 'Memulai membaca file...'
        ]));
        
        // Increase memory limit and execution time
        ini_set('memory_limit', '1024M');
        set_time_limit(300); // 5 minutes
        
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
            
            // Cleanup progress file
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            // Cleanup progress file on error
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            
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
        $chunkSize = 1000; // Read 1000 rows at a time
        $data = [];
        
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
        
        try {
            // Step 1: Read headers only (first row)
            $reader = IOFactory::createReader(IOFactory::identify($file->getPathname()));
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            
            $headerFilter = new ChunkReadFilter(1, 1);
            $reader->setReadFilter($headerFilter);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = $worksheet->toArray()[0] ?? [];
            
            if (empty($headers)) {
                throw new \Exception('File kosong atau tidak memiliki header');
            }
            
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
            
            // Free memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $worksheet);
            gc_collect_cycles();
            
            // Step 2: Read data in chunks
            $startRow = 2; // Skip header
            $hasMoreRows = true;
            
            while ($hasMoreRows) {
                $chunkFilter = new ChunkReadFilter($startRow, $chunkSize);
                $reader = IOFactory::createReader(IOFactory::identify($file->getPathname()));
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
                $reader->setReadFilter($chunkFilter);
                
                $spreadsheet = $reader->load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $chunkRows = $worksheet->toArray();
                
                $rowCount = 0;
                foreach ($chunkRows as $row) {
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
                    $rowCount++;
                }
                
                // Free memory after each chunk
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $worksheet, $chunkRows);
                gc_collect_cycles();
                
                // Check if we got fewer rows than chunk size (last chunk)
                if ($rowCount < $chunkSize) {
                    $hasMoreRows = false;
                } else {
                    $startRow += $chunkSize;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Chunk reading failed: ' . $e->getMessage());
            throw $e;
        }
        
        return $data;
    }
    
    /**
     * Read CSV file
     */
    private function readCsv($file)
    {
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
            'alamat' => ['alamat', 'alamat ', 'address'], // Note: includes 'alamat ' with trailing space
            'kota' => ['kota', 'kecamatan', 'city'],
            'alasan_gagal' => ['alasan gagal', 'alasan_gagal'],
            'alasan_irregulitas' => ['alasan irregulitas', 'alasan_irregulitas'],
            'status_swp' => ['status swp', 'status_swp'],
            'berat' => ['berat', 'weight'],
            'cek' => ['cek', 'check'],
        ];
        
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        
        // Get raw headers
        $rawHeaders = $csv->getHeader();
        
        // Normalize headers
        $normalizedHeaders = [];
        foreach ($rawHeaders as $header) {
            $normalizedHeader = strtolower(trim($header));
            $mappedColumn = null;
            
            // Find matching database column
            foreach ($columnMapping as $dbColumn => $variants) {
                if (in_array($normalizedHeader, $variants)) {
                    $mappedColumn = $dbColumn;
                    break;
                }
            }
            
            $normalizedHeaders[$header] = $mappedColumn ?: $normalizedHeader;
        }
        
        $records = $csv->getRecords();
        $data = [];
        
        foreach ($records as $record) {
            $normalizedRecord = [];
            
            // Map each column to normalized name
            foreach ($record as $originalColumn => $value) {
                $normalizedColumn = $normalizedHeaders[$originalColumn] ?? strtolower(trim($originalColumn));
                
                // Convert date formats
                if (in_array($normalizedColumn, ['tgl_kirim', 'tgl_antaran_pertama', 'tgl_update']) && $value) {
                    $value = $this->convertDate($value);
                }
                
                $normalizedRecord[$normalizedColumn] = $value;
            }
            
            $data[] = $normalizedRecord;
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
            // Handle Excel serial date numbers (e.g., 45887)
            if (is_numeric($date)) {
                // Check if it's a valid Excel date (between 1900 and 2100)
                if ($date > 1 && $date < 73050) {
                    $unixTimestamp = Date::excelToTimestamp($date);
                    return date('Y-m-d', $unixTimestamp);
                }
            }
            
            // Handle DateTime objects from PhpSpreadsheet
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
            
            // Try various string date formats
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
            
            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat($format, $date);
                if ($parsed !== false && $parsed->format($format) === $date) {
                    return $parsed->format('Y-m-d');
                }
            }
            
            // Try strtotime as fallback
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::warning('Date conversion failed: ' . $e->getMessage());
            return null;
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
        
        // Get session ID for progress tracking
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        
        // Initialize log array in progress file
        $logMessages = [];
        
        foreach ($data as $index => $row) {
            // Check if NOSI exists in database
            $nosi = $row['nosi'] ?? null;
            $exists = false;
            
            if ($nosi) {
                $exists = ShipmentData::where('nosi', $nosi)->exists();
            }
            
            // Log info untuk SETIAP baris
            $status = $exists ? 'DUPLIKAT' : 'BARU';
            $logMessage = "Row " . ($index + 1) . ": NOSI={$nosi} | Status={$status}";
            \Log::info($logMessage);
            
            // Add to log array (keep last 100 logs to avoid memory issues)
            $logMessages[] = [
                'message' => $logMessage,
                'type' => $exists ? 'warning' : 'info',
                'timestamp' => date('H:i:s')
            ];
            
            // Keep only last 100 logs
            if (count($logMessages) > 100) {
                array_shift($logMessages);
            }
            
            // Update progress with log
            $progressData = [
                'current' => $index + 1,
                'total' => count($data),
                'status' => 'processing',
                'log' => $logMessage, // Current log for status bar
                'logs' => $logMessages, // Array of recent logs for terminal
                'duplicate_count' => $duplicateCount,
                'new_count' => count($newData)
            ];
            
            if (file_exists($progressFile)) {
                file_put_contents($progressFile, json_encode($progressData));
            }
            
            // Small delay to allow frontend to poll
            usleep(10000); // 10ms delay
            
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
     * Get preview data (max 10 rows, no pagination)
     * Sorted: Data Baru (New) first, then by date (newest first)
     */
    public function getPreviewData(Request $request)
    {
        $data = session('upload_preview', []);
        
        // Separate new and duplicate data
        $newData = [];
        $duplicateData = [];
        
        foreach ($data as $row) {
            $isDuplicate = isset($row['is_duplicate']) ? $row['is_duplicate'] : false;
            if ($isDuplicate) {
                $duplicateData[] = $row;
            } else {
                $newData[] = $row;
            }
        }
        
        // Sort each group by tgl_kirim descending (newest first)
        usort($newData, function($a, $b) {
            $dateA = isset($a['tgl_kirim']) ? strtotime($a['tgl_kirim']) : 0;
            $dateB = isset($b['tgl_kirim']) ? strtotime($b['tgl_kirim']) : 0;
            return $dateB - $dateA; // Descending
        });
        
        usort($duplicateData, function($a, $b) {
            $dateA = isset($a['tgl_kirim']) ? strtotime($a['tgl_kirim']) : 0;
            $dateB = isset($b['tgl_kirim']) ? strtotime($b['tgl_kirim']) : 0;
            return $dateB - $dateA; // Descending
        });
        
        // Merge: New data first, then duplicate data
        $sortedData = array_merge($newData, $duplicateData);
        
        // Limit to 10 rows for preview to keep it light
        $previewData = array_slice($sortedData, 0, 10);
        
        \Log::info('Preview request - Total: ' . count($data) . ' | New: ' . count($newData) . ' | Duplicate: ' . count($duplicateData) . ' | Showing: ' . count($previewData) . ' (Sorted: Baru first, then by tgl_kirim desc)');
        
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
            ->skipPaging() // No pagination
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
        
        // Initialize progress tracking for import
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        
        try {
            $imported = 0;
            $skipped = 0;
            $totalRows = count($data);
            
            \Log::info('=== STARTING IMPORT ===');
            \Log::info('Total rows to import: ' . $totalRows);
            
            file_put_contents($progressFile, json_encode([
                'status' => 'importing',
                'current' => 0,
                'total' => $totalRows,
                'log' => 'Memulai import data...'
            ]));
            
            // Get file info from session
            $fileInfo = session('upload_file_info', []);
            $allData = session('upload_preview', []);
            
            // Import in chunks for better performance
            $chunks = array_chunk($data, 100); // Process 100 rows at a time
            $processedRows = 0;
            $logMessages = [];
            
            foreach ($chunks as $chunkIndex => $chunk) {
                foreach ($chunk as $row) {
                    $processedRows++;
                    
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
                        $status = 'INSERTED';
                        $logType = 'success';
                    } else {
                        $skipped++;
                        $status = 'SKIPPED';
                        $logType = 'warning';
                    }
                    
                    // Create detailed log message for each row
                    $detailedLog = "Row {$processedRows}: NOSI={$nosi} | Status={$status}";
                    \Log::info($detailedLog);
                    
                    // Add to log array
                    $logMessages[] = [
                        'message' => $detailedLog,
                        'type' => $logType,
                        'timestamp' => date('H:i:s')
                    ];
                    
                    // Keep only last 100 logs
                    if (count($logMessages) > 100) {
                        array_shift($logMessages);
                    }
                    
                    // Summary log message
                    $summaryLog = "Import progress: {$processedRows}/{$totalRows} | Inserted: {$imported} | Skipped: {$skipped}";
                    
                    // Update progress file with logs array
                    file_put_contents($progressFile, json_encode([
                        'status' => 'importing',
                        'current' => $processedRows,
                        'total' => $totalRows,
                        'log' => $summaryLog,
                        'logs' => $logMessages,
                        'imported' => $imported,
                        'skipped' => $skipped
                    ]));
                    
                    // Small delay to allow frontend polling
                    usleep(5000); // 5ms delay
                }
                
                // Memory cleanup after each chunk
                gc_collect_cycles();
            }
            
            \Log::info('=== IMPORT COMPLETE ===');
            \Log::info("Total: {$totalRows} | Imported: {$imported} | Skipped: {$skipped}");
            
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
            
            // Cleanup progress file
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            
            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Import failed: ' . $e->getMessage());
            
            // Cleanup progress file on error
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            
            return response()->json([
                'error' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }
}
