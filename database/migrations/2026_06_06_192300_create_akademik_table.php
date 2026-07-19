<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akademik', function (Blueprint $table) {
            $table->id();
            $table->string('periode');
            $table->string('judul');
            $table->string('coe')->nullable();
            $table->integer('target')->default(0);
            $table->integer('realisasi')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->string('status')->default('submitted');
            $table->unsignedBigInteger('input_by');
            $table->unsignedBigInteger('asisten_manager_approved_by')->nullable();
            $table->timestamp('asisten_manager_approved_at')->nullable();
            $table->unsignedBigInteger('manager_approved_by')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->text('catatan_reject')->nullable();
            $table->timestamps();

            $table->foreign('input_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('asisten_manager_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('manager_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akademik');
    }
};
