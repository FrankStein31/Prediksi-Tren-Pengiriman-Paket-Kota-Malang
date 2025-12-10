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
        Schema::create('weekly_shipment_data', function (Blueprint $table) {
            $table->id();
            $table->string('kecamatan', 50)->index();
            $table->date('week_start')->index(); // Awal minggu (Senin)
            $table->date('week_end'); // Akhir minggu (Minggu)
            $table->integer('year');
            $table->integer('week_number'); // ISO week number (1-53)
            $table->integer('total_paket')->default(0);
            $table->integer('delivered')->default(0);
            $table->integer('returned')->default(0);
            $table->integer('failed')->default(0);
            $table->decimal('avg_berat', 10, 2)->default(0); // Rata-rata berat paket
            $table->timestamps();
            
            // Unique constraint: satu kecamatan hanya punya satu record per minggu
            $table->unique(['kecamatan', 'week_start'], 'unique_kecamatan_week');
            
            // Index untuk query cepat
            $table->index(['kecamatan', 'year', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_shipment_data');
    }
};
