<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShipmentData;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ShipmentDataSeederCSV extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🗑️  Clearing existing data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ShipmentData::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('📥 Starting CSV import...');
        
        $csvPath = base_path('python/data/data_kiriman_converted.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error('❌ CSV file not found: ' . $csvPath);
            $this->command->info('💡 Run: cd python && python scripts/convert_to_csv.py');
            return;
        }
        
        try {
            // Open CSV file
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0); // First row is header
            
            $records = $csv->getRecords();
            
            // Count total (for progress bar)
            $totalRows = iterator_count($csv->getRecords());
            $this->command->info("📊 Found {$totalRows} rows to import");
            
            // Reset iterator
            $records = $csv->getRecords();
            
            $chunkSize = 500; // Process 500 rows at a time
            $chunk = [];
            $processedCount = 0;
            
            $bar = $this->command->getOutput()->createProgressBar($totalRows);
            $bar->start();
            
            foreach ($records as $index => $record) {
                try {
                    $chunk[] = [
                        'nosi' => $this->cleanValue($record['Nosi'] ?? null),
                        'posisi_saat_ini' => $this->cleanValue($record['Posisi_Saat_Ini'] ?? null),
                        'status_kiriman' => $this->cleanValue($record['Status Kiriman'] ?? null),
                        'produk' => $this->cleanValue($record['Produk'] ?? null),
                        'sla' => $this->cleanValue($record['SLA'] ?? null),
                        'kantor_kirim' => $this->cleanValue($record['Kantor_Kirim'] ?? null),
                        'tgl_kirim' => $this->parseDate($record['Tgl_Kirim'] ?? null),
                        'tgl_antaran_pertama' => $this->parseDate($record['Tgl_Antaran_Pertama'] ?? null),
                        'tgl_update' => $this->parseDate($record['Tgl_Update'] ?? null),
                        'petugas' => $this->cleanValue($record['Petugas'] ?? null),
                        'nama_penerima' => $this->cleanValue($record['Nama_Penerima'] ?? null),
                        'alamat' => $this->cleanValue($record['Alamat '] ?? null), // Note the space
                        'kota' => $this->cleanValue($record['Kota'] ?? null),
                        'alasan_gagal' => $this->cleanValue($record['Alasan_Gagal'] ?? null),
                        'alasan_irregulitas' => $this->cleanValue($record['Alasan_Irregulitas'] ?? null),
                        'status_swp' => $this->cleanValue($record['Status_SWP'] ?? null),
                        'berat' => $this->parseFloat($record['Berat'] ?? null),
                        'cek' => $this->parseInt($record['Cek'] ?? null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Insert when chunk is full
                    if (count($chunk) >= $chunkSize) {
                        DB::table('shipment_data')->insert($chunk);
                        $processedCount += count($chunk);
                        $bar->advance(count($chunk));
                        
                        // Clear chunk
                        $chunk = [];
                        
                        // Garbage collection
                        gc_collect_cycles();
                        
                        // Small pause to prevent system overload
                        usleep(10000); // 10ms
                    }
                    
                } catch (\Exception $e) {
                    $this->command->warn("⚠️  Skipping row " . ($index + 2) . ": " . $e->getMessage());
                    continue;
                }
            }
            
            // Insert remaining records
            if (!empty($chunk)) {
                DB::table('shipment_data')->insert($chunk);
                $processedCount += count($chunk);
                $bar->advance(count($chunk));
            }
            
            $bar->finish();
            $this->command->newLine(2);
            $this->command->info('✅ Import completed successfully!');
            $this->command->info('📊 Total records inserted: ' . number_format(ShipmentData::count()));
            
        } catch (\Exception $e) {
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('📍 Line: ' . $e->getLine());
            $this->command->error('📁 File: ' . $e->getFile());
        }
    }
    
    private function cleanValue($value)
    {
        if (is_null($value) || $value === '' || $value === '-') {
            return null;
        }
        return trim((string)$value);
    }
    
    private function parseDate($date)
    {
        if (empty($date) || $date === '-' || $date === 'nan') {
            return null;
        }
        
        try {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        } catch (\Exception $e) {
            // Ignore
        }
        
        return null;
    }
    
    private function parseFloat($value)
    {
        if (empty($value) || $value === '-' || $value === 'nan') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (float)$value;
        }
        
        return null;
    }
    
    private function parseInt($value)
    {
        if (empty($value) || $value === '-' || $value === 'nan') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        return null;
    }
}
