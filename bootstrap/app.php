<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * --------------------------------------------------------------------------
         * Middleware Global
         * --------------------------------------------------------------------------
         * Ini adalah middleware yang dijalankan di setiap request aplikasi.
         * Biasanya diisi dengan middleware standar Laravel.
         */
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class, // CORS bawaan Laravel 11
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        /**
         * --------------------------------------------------------------------------
         * Middleware Alias
         * --------------------------------------------------------------------------
         * Di bawah ini adalah alias middleware yang bisa kamu pakai di route.
         * Misalnya: ->middleware('check.manager')
         */
        $middleware->alias([
            // 'auth'          => \App\Http\Middleware\Authenticate::class,
            // 'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'check.manager' => \App\Http\Middleware\CheckManager::class,
            'check.staff'   => \App\Http\Middleware\CheckStaff::class,
            'check.dekan'           => \App\Http\Middleware\CheckDekan::class,
            'check.asisten_manager' => \App\Http\Middleware\CheckAsistenManager::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
