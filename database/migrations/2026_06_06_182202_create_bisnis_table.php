<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bisnis', function (Blueprint $table) {
            $table->id();

            $table->string('periode');            // contoh: TW1 2025
            $table->string('judul');
            $table->string('coe')->nullable();
            $table->integer('target')->nullable();
            $table->integer('realisasi')->nullable();
            $table->string('file_pendukung')->nullable();

            // submitted  → menunggu asisten manager
            // reviewed   → diapprove asman, menunggu manager
            // approved   → final, tampil di dashboard
            // rejected_by_asman   → ditolak asisten manager
            // rejected_by_manager → ditolak manager
            $table->enum('status', [
                'submitted',
                'reviewed',
                'approved',
                'rejected_by_asman',
                'rejected_by_manager',
            ])->default('submitted');

            $table->foreignId('input_by')
                ->constrained('users')->onDelete('cascade');

            $table->foreignId('asisten_manager_approved_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asisten_manager_approved_at')->nullable();

            $table->foreignId('manager_approved_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();

            $table->text('catatan_reject')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bisnis');
    }
};
