<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];  // Kolom yang boleh diisi

    // Relasi satu role bisa dimiliki oleh banyak user
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');  // Menyebutkan kolom 'role_id' jika ada di tabel users
    }
}
