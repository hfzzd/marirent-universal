<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceWebController extends Controller
{
    public function index()
    {
        $driver = Driver::where('user_id', Auth::id())->firstOrFail();

        $attendances = Attendance::where('driver_id', $driver->id)
            ->latest('date')
            ->paginate(15);

        $today = now()->toDateString();
        $todayAttendance = Attendance::where('driver_id', $driver->id)
            ->where('date', $today)
            ->first();

        return view('attendances.index', compact('driver', 'attendances', 'todayAttendance'));
    }

    public function checkIn(Request $request)
    {
        $driver = Driver::where('user_id', Auth::id())->firstOrFail();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['driver_id' => $driver->id, 'date' => $today],
            ['status' => 'absent']
        );

        if ($attendance->isCheckedin()) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini');
        }

        $attendance->update([
            'check_in' => now(),
            'check_in_notes' => $request->notes,
            'check_in_location' => $request->location ?? 'Kantor',
            'status' => 'checked_in',
        ]);

        $driver->update(['status' => 'active']);

        return back()->with('success', 'Absen masuk berhasil pada ' . now()->format('H:i'));
    }

    public function checkOut(Request $request)
    {
        $driver = Driver::where('user_id', Auth::id())->firstOrFail();
        $today = now()->toDateString();

        $attendance = Attendance::where('driver_id', $driver->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->isCheckedin()) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini');
        }

        $attendance->update([
            'check_out' => now(),
            'check_out_notes' => $request->notes,
            'check_out_location' => $request->location ?? 'Kantor',
            'status' => 'checked_out',
        ]);

        $driver->update(['status' => 'off_duty']);

        return back()->with('success', 'Absen keluar berhasil pada ' . now()->format('H:i'));
    }
}
