<?php

namespace App\Listeners;

use App\Models\AktivitasPengguna;
use Illuminate\Auth\Events\Login;

class LogUserActivity
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // Mencatat aktivitas login
        AktivitasPengguna::create([
            'user_id' => $event->user->id, // ID pengguna yang login
            'aktivitas' => 'Login', // Jenis aktivitas
            'waktu_aktivitas' => now(), // Waktu login
        ]);
    }
}
