<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && optional($user->role)->name === 'admin') {
            return $next($request);
        }

        // Kalau bukan manager, arahkan balik
        return redirect('/login')->withErrors([
            'access' => 'Anda tidak memiliki akses ke halaman admin.'
        ]);
    }
}
