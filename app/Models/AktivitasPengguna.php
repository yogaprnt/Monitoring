<?php

// app/Models/AktivitasPengguna.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasPengguna extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_pengguna'; // Pastikan nama tabel sudah benar

    protected $fillable = [
        'user_id',
        'aktivitas',
        'waktu_aktivitas',
    ];

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);  // Menghubungkan dengan model User
    }
}
