<?php

namespace App\Services;

use App\Models\ShipmentData;
use App\Models\WeeklyShipmentData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklyDataAggregationService
{
    /**
     * Agregasi data mingguan untuk kecamatan tertentu
     *
     * @param string $kecamatan
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function aggregateWeeklyData($kecamatan, $startDate = null, $endDate = null)
    {
        try {
            DB::beginTransaction();

            $query = ShipmentData::query()
                ->whereRaw("TRIM(SUBSTRING_INDEX(kota, ',', -1)) = ?", [$kecamatan]);

            // Filter berdasarkan tanggal jika disediakan
            if ($startDate) {
                $query->where('tgl_kirim', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('tgl_kirim', '<=', $endDate);
            }

            // Group by minggu dan hitung agregasi
            $weeklyData = $query
                ->select(
                    DB::raw('YEARWEEK(tgl_kirim, 3) as yearweek'),
                    DB::raw('YEAR(tgl_kirim) as year'),
                    DB::raw('WEEK(tgl_kirim, 3) as week_number'),
                    DB::raw('DATE(DATE_SUB(tgl_kirim, INTERVAL WEEKDAY(tgl_kirim) DAY)) as week_start'),
                    DB::raw('DATE(DATE_ADD(DATE_SUB(tgl_kirim, INTERVAL WEEKDAY(tgl_kirim) DAY), INTERVAL 6 DAY)) as week_end'),
                    DB::raw('COUNT(*) as total_paket'),
                    DB::raw('SUM(CASE WHEN posisi_saat_ini = "DELIVERED" THEN 1 ELSE 0 END) as delivered'),
                    DB::raw('SUM(CASE WHEN posisi_saat_ini = "RETURNED" THEN 1 ELSE 0 END) as returned'),
                    DB::raw('SUM(CASE WHEN posisi_saat_ini NOT IN ("DELIVERED", "RETURNED") THEN 1 ELSE 0 END) as failed'),
                    DB::raw('AVG(berat) as avg_berat')
                )
                ->groupBy('yearweek', 'year', 'week_number', 'week_start', 'week_end')
                ->orderBy('week_start')
                ->get();

            $insertedCount = 0;
            $updatedCount = 0;

            foreach ($weeklyData as $data) {
                $existing = WeeklyShipmentData::where('kecamatan', $kecamatan)
                    ->where('week_start', $data->week_start)
                    ->first();

                $weeklyRecord = [
                    'kecamatan' => $kecamatan,
                    'week_start' => $data->week_start,
                    'week_end' => $data->week_end,
                    'year' => $data->year,
                    'week_number' => $data->week_number,
                    'total_paket' => $data->total_paket,
                    'delivered' => $data->delivered,
                    'returned' => $data->returned,
                    'failed' => $data->failed,
                    'avg_berat' => round($data->avg_berat, 2),
                ];

                if ($existing) {
                    $existing->update($weeklyRecord);
                    $updatedCount++;
                } else {
                    WeeklyShipmentData::create($weeklyRecord);
                    $insertedCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data mingguan berhasil diagregasi',
                'kecamatan' => $kecamatan,
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'total' => $insertedCount + $updatedCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal agregasi data: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Agregasi data untuk semua kecamatan
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function aggregateAllKecamatan($startDate = null, $endDate = null)
    {
        $kecamatans = ['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN'];
        $results = [];

        foreach ($kecamatans as $kecamatan) {
            $results[$kecamatan] = $this->aggregateWeeklyData($kecamatan, $startDate, $endDate);
        }

        return [
            'success' => true,
            'message' => 'Agregasi semua kecamatan selesai',
            'results' => $results,
        ];
    }

    /**
     * Hapus dan rebuild semua data mingguan untuk kecamatan
     *
     * @param string $kecamatan
     * @return array
     */
    public function rebuildWeeklyData($kecamatan)
    {
        try {
            DB::beginTransaction();

            // Hapus data lama
            WeeklyShipmentData::where('kecamatan', $kecamatan)->delete();

            // Agregasi ulang semua data
            $result = $this->aggregateWeeklyData($kecamatan);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal rebuild data: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
