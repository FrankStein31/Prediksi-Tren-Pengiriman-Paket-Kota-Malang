<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyShipmentData extends Model
{
    protected $table = 'weekly_shipment_data';

    protected $fillable = [
        'kecamatan',
        'week_start',
        'week_end',
        'year',
        'week_number',
        'total_paket',
        'delivered',
        'returned',
        'failed',
        'avg_berat',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'year' => 'integer',
        'week_number' => 'integer',
        'total_paket' => 'integer',
        'delivered' => 'integer',
        'returned' => 'integer',
        'failed' => 'integer',
        'avg_berat' => 'decimal:2',
    ];

    // Scope untuk filter berdasarkan kecamatan
    public function scopeByKecamatan($query, $kecamatan)
    {
        return $query->where('kecamatan', $kecamatan);
    }

    // Scope untuk filter berdasarkan range tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('week_start', [$startDate, $endDate]);
    }

    // Scope untuk filter berdasarkan tahun
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    // Scope untuk mendapatkan data terbaru
    public function scopeLatestWeeks($query, $limit = 52)
    {
        return $query->orderBy('week_start', 'desc')->limit($limit);
    }
}
