<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDekan
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && optional($user->role)->name === 'dekan') {
            return $next($request);
        }

        // Kalau bukan dekan, arahkan balik
        return redirect('/login')->withErrors([
            'access' => 'Anda tidak memiliki akses ke halaman dekan.'
        ]);
    }
}
