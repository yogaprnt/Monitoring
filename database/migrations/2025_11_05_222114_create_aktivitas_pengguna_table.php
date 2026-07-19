<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAktivitasPenggunaTable extends Migration
{
    public function up()
    {
        Schema::create('aktivitas_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke tabel users
            $table->enum('aktivitas', ['Login', 'Logout', 'Approve', 'Input Data', 'Reject']); // Menyimpan jenis aktivitas
            $table->timestamp('waktu_aktivitas')->useCurrent(); // Waktu aktivitas dilakukan
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('aktivitas_pengguna');
    }
}
