<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentData extends Model
{
    protected $table = 'shipment_data';
    
    protected $fillable = [
        'nosi',
        'posisi_saat_ini',
        'status_kiriman',
        'produk',
        'sla',
        'kantor_kirim',
        'tgl_kirim',
        'tgl_antaran_pertama',
        'tgl_update',
        'petugas',
        'nama_penerima',
        'alamat',
        'kota',
        'alasan_gagal',
        'alasan_irregulitas',
        'status_swp',
        'berat',
        'cek',
    ];
    
    protected $casts = [
        'tgl_kirim' => 'date',
        'tgl_antaran_pertama' => 'date',
        'tgl_update' => 'date',
        'berat' => 'decimal:2',
        'cek' => 'integer',
    ];
}
