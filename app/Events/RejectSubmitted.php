<?php

namespace App\Events;

use Illuminate\Foundation\Auth\User;

class RejectSubmitted
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}