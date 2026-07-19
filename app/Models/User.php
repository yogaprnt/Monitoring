<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'username', 'email', 'password', 'role_id'];

    // Relasi dengan tabel roles
    public function role()
    {
        return $this->belongsTo(Role::class);  // Pastikan Anda memiliki tabel 'roles' yang berhubungan
    }
}
