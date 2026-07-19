<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAsistenManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && optional($user->role)->name === 'asisten_manager') {
            return $next($request);
        }

        // Kalau bukan asisten manager, arahkan balik
        return redirect('/login')->withErrors([
            'access' => 'Anda tidak memiliki akses ke halaman asisten manager.'
        ]);
    }
}
