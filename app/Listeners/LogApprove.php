<?php

namespace App\Listeners;

use App\Models\AktivitasPengguna;
use App\Events\ApproveSubmitted;

class LogApprove
{
    public function handle(ApproveSubmitted $event)
    {
        AktivitasPengguna::create([
            'user_id'         => $event->user->id,
            'aktivitas'       => 'Approve',
            'waktu_aktivitas' => now(),
        ]);
    }
}
