<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogUserActivity;
use App\Listeners\LogUserLogout;
use App\Listeners\LogInputData;
use App\Listeners\LogApprove;
use App\Listeners\LogReject;
use App\Events\InputDataSubmitted;
use App\Events\ApproveSubmitted;
use App\Events\RejectSubmitted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogUserActivity::class,
        ],
        Logout::class => [
            LogUserLogout::class,
        ],
        InputDataSubmitted::class => [
            LogInputData::class,
        ],
        ApproveSubmitted::class => [
            LogApprove::class,
        ],
        RejectSubmitted::class => [
            LogReject::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
