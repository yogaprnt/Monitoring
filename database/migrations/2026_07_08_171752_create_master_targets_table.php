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
        Schema::create('master_targets', function (Blueprint $table) {
            $table->id();
            $table->string('periode');               // "TW1 2025"
            $table->string('kategori');              // Riset / Bisnis / Akademik / Pengabdian / Inovasi
            $table->string('judul');                 // indikator/kegiatan
            $table->integer('target')->default(0);   // angka target RI
            $table->text('keterangan')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->unsignedBigInteger('input_by');
            $table->timestamps();

            $table->foreign('input_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_targets');
    }
};
