<?php

namespace App\Listeners;

use App\Models\AktivitasPengguna;
use App\Events\InputDataSubmitted;

class LogInputData
{
    public function handle(InputDataSubmitted $event)
    {
        AktivitasPengguna::create([
            'user_id'         => $event->user->id,
            'aktivitas'       => 'Input Data',
            'waktu_aktivitas' => now(),
        ]);
    }
}
