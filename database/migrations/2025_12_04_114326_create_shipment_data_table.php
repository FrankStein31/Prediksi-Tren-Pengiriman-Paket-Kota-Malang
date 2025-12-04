<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipment_data', function (Blueprint $table) {
            $table->id();
            $table->string('nosi')->nullable();
            $table->string('posisi_saat_ini')->nullable();
            $table->string('status_kiriman')->nullable();
            $table->string('produk')->nullable();
            $table->string('sla')->nullable();
            $table->string('kantor_kirim')->nullable();
            $table->date('tgl_kirim')->nullable();
            $table->date('tgl_antaran_pertama')->nullable();
            $table->date('tgl_update')->nullable();
            $table->string('petugas')->nullable();
            $table->string('nama_penerima')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('alasan_gagal')->nullable();
            $table->string('alasan_irregulitas')->nullable();
            $table->string('status_swp')->nullable();
            $table->decimal('berat', 10, 2)->nullable();
            $table->integer('cek')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_data');
    }
};
