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
        ini_set('memory_limit', '-1'); // No memory limit
        set_time_limit(0); // No time limit
        
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
            
            \Log::info('=== PROCESS COMPLETE ===');
            \Log::info('Returning result to frontend...');
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error('=== PROCESS FAILED ===');
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
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
     * Check for duplicates by checking NOSI only (OPTIMIZED - DISK BASED)
     */
    private function checkDuplicates($data)
    {
        // Use temporary file for large data instead of memory
        $sessionId = session()->getId();
        $tempFile = storage_path("app/upload_temp_{$sessionId}.json");
        
        $duplicateData = [];
        $previewNew = []; // Separate preview array
        $duplicateCount = 0;
        $newCount = 0;
        
        // Initialize temp file
        file_put_contents($tempFile, ''); // Empty file
        
        \Log::info('=== STARTING DUPLICATE CHECK (DISK-BASED) ===');
        \Log::info('Total rows to check: ' . count($data));
        \Log::info('Using temp file: ' . $tempFile);
        
        // Get session ID for progress tracking
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        
        // OPTIMIZATION: Get ALL existing NOSI from database (chunked to avoid placeholder limit)
        $allNosi = array_filter(array_column($data, 'nosi')); // Extract all NOSI from uploaded data
        $existingNosi = [];
        
        if (!empty($allNosi)) {
            \Log::info('Fetching existing NOSI from database...');
            
            // MySQL has limit of ~65k placeholders, so chunk the whereIn query
            $chunkSize = 50000; // Safe limit for MySQL
            $nosiChunks = array_chunk($allNosi, $chunkSize);
            
            \Log::info('Total NOSI to check: ' . count($allNosi) . ' in ' . count($nosiChunks) . ' chunks');
            
            foreach ($nosiChunks as $chunkIndex => $nosiChunk) {
                \Log::info('Fetching chunk ' . ($chunkIndex + 1) . '/' . count($nosiChunks) . ' (' . count($nosiChunk) . ' items)');
                
                $chunkResult = ShipmentData::whereIn('nosi', $nosiChunk)
                    ->pluck('nosi')
                    ->flip() // Convert to associative array for faster lookup
                    ->toArray();
                
                // Merge with existing results
                $existingNosi = array_merge($existingNosi, $chunkResult);
                
                \Log::info('Found ' . count($chunkResult) . ' existing NOSI in this chunk');
            }
            
            \Log::info('Total existing NOSI found: ' . count($existingNosi) . ' out of ' . count($allNosi));
        }
        
        // Initialize log array in progress file
        $logMessages = [];
        $totalRows = count($data);
        $updateInterval = max(1, (int)($totalRows / 100)); // Update progress every 1%
        
        // MEMORY OPTIMIZATION: Process in single pass, WRITE NEW DATA TO DISK
        foreach ($data as $index => $row) {
            // Check if NOSI exists in database (using in-memory lookup)
            $nosi = $row['nosi'] ?? null;
            $exists = false;
            
            if ($nosi && isset($existingNosi[$nosi])) {
                $exists = true;
            }
            
            // Separate based on duplicate status
            if ($exists) {
                $duplicateCount++;
                // Only store first 50 duplicates for preview
                if (count($duplicateData) < 50) {
                    $row['is_duplicate'] = true;
                    $row['status'] = 'Duplikat';
                    $duplicateData[] = $row;
                }
            } else {
                $newCount++;
                
                // ✅ WRITE TO DISK instead of storing in memory
                $row['is_duplicate'] = false;
                $row['status'] = 'Baru';
                file_put_contents($tempFile, json_encode($row) . "\n", FILE_APPEND);
                
                // Also store first 50 for preview
                if (count($previewNew) < 50) {
                    $previewNew[] = $row;
                }
            }
            
            // Log only every N rows or first/last 10 rows
            $shouldLog = ($index < 10) || ($index >= $totalRows - 10) || (($index + 1) % $updateInterval === 0);
            
            if ($shouldLog) {
                $status = $exists ? 'DUPLIKAT' : 'BARU';
                $logMessage = "Row " . ($index + 1) . ": NOSI={$nosi} | Status={$status}";
                \Log::info($logMessage);
                
                // Add to log array (keep last 30 logs to reduce memory)
                $logMessages[] = [
                    'message' => $logMessage,
                    'type' => $exists ? 'warning' : 'info',
                    'timestamp' => date('H:i:s')
                ];
                
                // Keep only last 30 logs (reduced from 50)
                if (count($logMessages) > 30) {
                    array_shift($logMessages);
                }
                
                // Update progress (every 1% or at key milestones)
                $progressData = [
                    'current' => $index + 1,
                    'total' => $totalRows,
                    'status' => 'processing',
                    'log' => "Checking: " . ($index + 1) . "/{$totalRows}",
                    'logs' => $logMessages,
                    'duplicate_count' => $duplicateCount,
                    'new_count' => $newCount,
                    'percentage' => round((($index + 1) / $totalRows) * 100, 1)
                ];
                
                if (file_exists($progressFile)) {
                    file_put_contents($progressFile, json_encode($progressData));
                }
            }
            
            // Memory cleanup every 500 rows (more frequent)
            if (($index + 1) % 500 === 0) {
                gc_collect_cycles();
            }
            
            // Free the original row from $data array to reduce memory
            unset($data[$index]);
        }
        
        \Log::info('=== DUPLICATE CHECK COMPLETE ===');
        \Log::info('Total: ' . ($duplicateCount + $newCount) . ' | New: ' . $newCount . ' | Duplicate: ' . $duplicateCount);
        
        // Merge preview data (max 100 rows total)
        $previewData = array_merge($previewNew, $duplicateData);
        
        session([
            'upload_preview' => $previewData, // Max 100 rows for preview
            'upload_temp_file' => $tempFile, // ✅ Path to temp file with all new data
            'upload_stats' => [
                'total_rows' => $duplicateCount + $newCount,
                'new_rows' => $newCount,
                'duplicate_rows' => $duplicateCount,
            ]
        ]);
        
        \Log::info('Session stored - Preview: ' . count($previewData) . ' rows, New data in temp file: ' . $newCount . ' rows');
        \Log::info('Temp file: ' . $tempFile . ' (Size: ' . (file_exists($tempFile) ? filesize($tempFile) : 0) . ' bytes)');
        
        // Force final garbage collection
        unset($existingNosi, $allNosi, $logMessages, $data, $duplicateData, $previewNew, $previewData);
        gc_collect_cycles();
        
        return [
            'total_rows' => $duplicateCount + $newCount,
            'new_rows' => $newCount,
            'duplicate_rows' => $duplicateCount,
            'temp_file' => $tempFile, // Return temp file path
        ];
    }
    
    /**
     * Get preview data (max 10 rows, no pagination)
     * Now using pre-filtered preview data from session (max 100 rows)
     */
    public function getPreviewData(Request $request)
    {
        $data = session('upload_preview', []);
        
        // Already pre-filtered (50 new + 50 duplicate max)
        // Just show first 10
        $previewData = array_slice($data, 0, 10);
        
        \Log::info('Preview request - Showing: ' . count($previewData) . ' rows from ' . count($data) . ' preview data');
        
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
     * Import new data to database (OPTIMIZED)
     */
    public function import(Request $request)
    {
        // ✅ Get temp file path and stats from session
        $tempFile = session('upload_temp_file');
        $stats = session('upload_stats', []);
        
        if (empty($tempFile) || !file_exists($tempFile)) {
            return response()->json(['error' => 'Tidak ada data untuk diimport'], 400);
        }
        
        // Initialize progress tracking for import
        $sessionId = session()->getId();
        $progressFile = storage_path("app/upload_progress_{$sessionId}.json");
        
        // Increase memory limit and execution time
        ini_set('memory_limit', '-1'); // No memory limit
        set_time_limit(0); // No time limit
        
        try {
            $imported = 0;
            $skipped = 0;
            $totalRows = $stats['new_rows'] ?? 0;
            
            \Log::info('=== STARTING IMPORT ===');
            \Log::info('Total rows to import: ' . $totalRows);
            \Log::info('Reading from temp file: ' . $tempFile);
            
            file_put_contents($progressFile, json_encode([
                'status' => 'importing',
                'current' => 0,
                'total' => $totalRows,
                'log' => 'Memulai import data...'
            ]));
            
            // Get file info from session
            $fileInfo = session('upload_file_info', []);
            
            // ✅ READ FROM TEMP FILE IN CHUNKS (to avoid memory issues)
            $handle = fopen($tempFile, 'r');
            if (!$handle) {
                throw new \Exception('Cannot open temp file for reading');
            }
            
            $processedRows = 0;
            $logMessages = [];
            $updateInterval = max(1, (int)($totalRows / 100)); // Update every 1%
            $batchInsert = [];
            $batchSize = 1000;
            
            while (($line = fgets($handle)) !== false) {
                $row = json_decode($line, true);
                if (!$row) continue; // Skip invalid lines
                
                $processedRows++;
                
                // Remove flags if exists
                unset($row['is_duplicate']);
                unset($row['status']);
                
                // Add timestamps for batch insert
                $row['created_at'] = now();
                $row['updated_at'] = now();
                $batchInsert[] = $row;
                
                // Insert when batch is full
                if (count($batchInsert) >= $batchSize) {
                    try {
                        DB::table('shipment_data')->insert($batchInsert);
                        $imported += count($batchInsert);
                        \Log::info("Batch inserted: " . count($batchInsert) . " rows");
                    } catch (\Exception $e) {
                        \Log::error("Batch insert failed: " . $e->getMessage());
                        $skipped += count($batchInsert);
                    }
                    
                    // Clear batch
                    $batchInsert = [];
                    gc_collect_cycles();
                }
                
                // Log progress
                $shouldLog = ($processedRows % $updateInterval === 0) || ($processedRows === $totalRows);
                
                if ($shouldLog) {
                    $detailedLog = "Processing: {$processedRows}/{$totalRows}";
                    \Log::info($detailedLog);
                    
                    // Add to log array
                    $logMessages[] = [
                        'message' => $detailedLog,
                        'type' => 'info',
                        'timestamp' => date('H:i:s')
                    ];
                    
                    // Keep only last 50 logs
                    if (count($logMessages) > 50) {
                        array_shift($logMessages);
                    }
                    
                    // Summary log message
                    $summaryLog = "Import progress: {$processedRows}/{$totalRows} | Inserted: {$imported}";
                    
                    // Update progress file with logs array
                    file_put_contents($progressFile, json_encode([
                        'status' => 'importing',
                        'current' => $processedRows,
                        'total' => $totalRows,
                        'log' => $summaryLog,
                        'logs' => $logMessages,
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'percentage' => round(($processedRows / $totalRows) * 100, 1)
                    ]));
                }
            }
            
            // Insert remaining rows in batch
            if (!empty($batchInsert)) {
                try {
                    DB::table('shipment_data')->insert($batchInsert);
                    $imported += count($batchInsert);
                    \Log::info("Final batch inserted: " . count($batchInsert) . " rows");
                } catch (\Exception $e) {
                    \Log::error("Final batch insert failed: " . $e->getMessage());
                    $skipped += count($batchInsert);
                }
            }
            
            fclose($handle);
            
            \Log::info('=== IMPORT COMPLETE ===');
            \Log::info("Total: {$totalRows} | Imported: {$imported} | Skipped: {$skipped}");
            
            // Save upload history to database
            UploadHistory::create([
                'filename' => $fileInfo['filename'] ?? 'unknown',
                'file_extension' => $fileInfo['extension'] ?? 'unknown',
                'file_size' => $fileInfo['size'] ?? 0,
                'total_rows' => $stats['total_rows'] ?? 0,
                'new_rows' => $imported,
                'duplicate_rows' => $stats['duplicate_rows'] ?? 0,
                'skipped_rows' => $skipped,
                'notes' => "Import berhasil: {$imported} data baru ditambahkan, {$skipped} data duplikat diskip.",
            ]);
            
            // ✅ Delete temp file after successful import
            if (file_exists($tempFile)) {
                unlink($tempFile);
                \Log::info('Temp file deleted: ' . $tempFile);
            }
            
            // Clear session
            session()->forget('upload_preview');
            session()->forget('upload_temp_file');
            session()->forget('upload_stats');
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
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // ✅ Cleanup temp file on error too
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            
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
