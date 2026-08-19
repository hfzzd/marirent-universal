<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperadminController extends Controller
{
    public function monitoring()
    {
        return view('superadmin.monitoring');
    }

    public function finance()
    {
        return view('superadmin.finance');
    }

    public function absen()
    {
        return view('superadmin.absen');
    }

    public function monitoringVehicle()
    {
        return view('superadmin.monitoring-vehicle');
    }

    public function motor()
    {
        return view('superadmin.motor');
    }

    public function elektronik()
    {
        return redirect()->route('superadmin.elektronik.type', 'kamera');
    }
}
