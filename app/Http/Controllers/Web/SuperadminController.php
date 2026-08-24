<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperadminController extends Controller
{
    public function monitoring(Request $request)
    {
        // Navigasi bulan scheduler (default bulan ini)
        $month = now()->copy();
        if ($request->filled('bulan')) {
            try {
                $month = \Carbon\Carbon::createFromFormat('Y-m', $request->input('bulan'))->startOfMonth();
            } catch (\Throwable $e) {
                $month = now()->copy()->startOfMonth();
            }
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // Semua booking dengan jadwal pembayaran pada bulan terpilih
        $scheduledQuery = \App\Models\Booking::with(['user', 'vehicle', 'category'])
            ->whereNotNull('payment_due_date')
            ->whereBetween('payment_due_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('payment_due_date');

        $scheduledBookings = (clone $scheduledQuery)->get();

        // Event per tanggal untuk kalender
        $eventsByDay = [];
        foreach ($scheduledBookings as $booking) {
            $day = \Carbon\Carbon::parse($booking->payment_due_date)->day;
            $eventsByDay[$day][] = [
                'id' => $booking->id,
                'code' => $booking->booking_code,
                'item' => $booking->vehicle?->name ?? ($booking->category?->name ?? 'Unit Sewa'),
                'customer' => $booking->user?->name ?? '-',
                'amount' => (float) $booking->final_price,
                'status' => $booking->payment_status,
                'overdue' => $booking->payment_status !== 'paid' && \Carbon\Carbon::parse($booking->payment_due_date)->isPast(),
            ];
        }

        // Ringkasan
        $allOutstanding = \App\Models\Booking::whereIn('payment_status', ['unpaid', 'partial']);
        $summary = [
            'due_this_month' => (clone $scheduledQuery)->whereIn('payment_status', ['unpaid', 'partial'])->count(),
            'due_this_month_amount' => (clone $scheduledQuery)->whereIn('payment_status', ['unpaid', 'partial'])->sum('final_price'),
            'overdue_total' => \App\Models\Booking::whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNotNull('payment_due_date')->where('payment_due_date', '<', today())->count(),
            'overdue_amount' => \App\Models\Booking::whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNotNull('payment_due_date')->where('payment_due_date', '<', today())->sum('final_price'),
            'paid_this_month' => $scheduledBookings->where('payment_status', 'paid')->count(),
            'outstanding_all_amount' => (clone $allOutstanding)->sum('final_price'),
        ];

        // Daftar jatuh tempo terdekat (7 hari ke depan dari hari ini)
        $upcomingDues = \App\Models\Booking::with(['user', 'vehicle', 'category'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('payment_due_date')
            ->whereBetween('payment_due_date', [today(), today()->copy()->addDays(7)->endOfDay()])
            ->orderBy('payment_due_date')
            ->limit(10)
            ->get();

        return view('superadmin.monitoring', compact(
            'month', 'eventsByDay', 'summary', 'upcomingDues'
        ));
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
        return redirect()->route('motors.index');
    }

    public function elektronik()
    {
        return redirect()->route('superadmin.elektronik.type', 'kamera');
    }
}
