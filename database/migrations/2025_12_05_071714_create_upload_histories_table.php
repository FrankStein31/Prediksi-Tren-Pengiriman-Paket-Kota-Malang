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
        Schema::create('upload_histories', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Nama file yang diupload
            $table->string('file_extension', 10); // xlsx, xls, csv
            $table->bigInteger('file_size')->unsigned(); // Ukuran file dalam bytes
            $table->integer('total_rows')->unsigned(); // Total baris di file
            $table->integer('new_rows')->unsigned(); // Jumlah data baru yang masuk DB
            $table->integer('duplicate_rows')->unsigned(); // Jumlah data duplikat
            $table->integer('skipped_rows')->unsigned()->default(0); // Data yang diskip
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps(); // created_at = tanggal upload
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_histories');
    }
};
