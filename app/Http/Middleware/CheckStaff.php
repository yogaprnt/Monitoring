<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStaff
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && optional($user->role)->name === 'staff') {
            return $next($request);
        }
        return redirect('/'); // atau abort(403)
    }
}
