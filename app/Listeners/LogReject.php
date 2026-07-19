<?php

namespace App\Listeners;

use App\Models\AktivitasPengguna;
use App\Events\RejectSubmitted;

class LogReject
{
    public function handle(RejectSubmitted $event)
    {
        AktivitasPengguna::create([
            'user_id'         => $event->user->id,
            'aktivitas'       => 'Reject',
            'waktu_aktivitas' => now(),
        ]);
    }
}
