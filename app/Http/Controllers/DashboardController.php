<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'manager') {
            return view('manager.dashboard', [
                'name'     => $user->name,
                'position' => $user->role->name,
            ]);
        } elseif ($user->role->name === 'staff') {
            return view('staff.dashboard', [
                'name'     => $user->name,
                'position' => $user->role->name,
            ]);
        } elseif ($user->role->name === 'dekan') {
            return view('dekan.dashboard', [
                'name'     => $user->name,
                'position' => $user->role->name,
            ]);
        } else {
            return view('staff.dashboard', [
                'name'     => $user->name,
                'position' => $user->role->name,
            ]);
        }
    }
}
