<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bisnis extends Model
{
    use HasFactory;

    protected $table = 'bisnis';

    protected $fillable = [
        'periode',
        'judul',
        'coe',
        'target',
        'realisasi',
        'file_pendukung',
        'status',
        'input_by',
        'asisten_manager_approved_by',
        'asisten_manager_approved_at',
        'manager_approved_by',
        'manager_approved_at',
        'catatan_reject',
    ];

    protected $casts = [
        'target'                      => 'integer',
        'realisasi'                   => 'integer',
        'asisten_manager_approved_at' => 'datetime',
        'manager_approved_at'         => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function penginput()
    {
        return $this->belongsTo(User::class, 'input_by')->withTrashed();
    }

    public function asistenManagerApprover()
    {
        return $this->belongsTo(User::class, 'asisten_manager_approved_by');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper status
    |--------------------------------------------------------------------------
    */

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return in_array($this->status, ['rejected_by_asman', 'rejected_by_manager'], true);
    }

    public function canBeModifiedByStaff(): bool
    {
        return in_array($this->status, ['submitted', 'rejected_by_asman', 'rejected_by_manager'], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope query
    |--------------------------------------------------------------------------
    */

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }
    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('status', 'reviewed');
    }
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->whereIn('status', ['rejected_by_asman', 'rejected_by_manager']);
    }
}
