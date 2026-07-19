<?php

namespace App\Listeners;

use App\Models\AktivitasPengguna;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Logout $event
     * @return void
     */
    public function handle(Logout $event)
    {
        // Mencatat aktivitas logout
        AktivitasPengguna::create([
            'user_id' => $event->user->id, // ID pengguna yang logout
            'aktivitas' => 'Logout', // Jenis aktivitas
            'waktu_aktivitas' => now(), // Waktu logout
        ]);
    }
}
