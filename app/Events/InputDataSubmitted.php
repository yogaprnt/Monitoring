<?php

namespace App\Events;

use Illuminate\Foundation\Auth\User as Authenticatable;

class InputDataSubmitted
{
    public $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
