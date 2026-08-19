<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match($user->role) {
            'superadmin' => view('dashboard.superadmin'),
            'owner' => view('dashboard.owner'),
            'driver' => view('dashboard.driver'),
            'user' => view('dashboard.user'),
            default => redirect()->route('home'),
        };
    }
}
