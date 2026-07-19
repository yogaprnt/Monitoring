<?php

namespace App\Events;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ApproveSubmitted
{
    public $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
