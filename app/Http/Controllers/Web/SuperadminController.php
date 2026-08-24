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
        $scheduledQuery = \App\Models\Booking::with(['user', 'vehicle', 'category', 'item'])
            ->whereNotNull('payment_due_date')
            ->whereBetween('payment_due_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('payment_due_date');

        $scheduledBookings = (clone $scheduledQuery)->get();

        // Info pembayaran: persen DP & sisa tagihan
        $payInfo = function ($b): array {
            if ($b->payment_status === 'paid') {
                return [100, 0.0];
            }
            if ($b->payment_status === 'partial') {
                $inv = \App\Models\Invoice::where('booking_id', $b->id)->where('type', 'rental')->first();
                if ($inv && (float) $inv->total_amount > 0) {
                    return [
                        min(99, (int) round((float) $inv->paid_amount / (float) $inv->total_amount * 100)),
                        max((float) $inv->due_amount, 0.0),
                    ];
                }
                return [50, round((float) $b->final_price / 2, 2)];
            }
            return [0, (float) $b->final_price];
        };

        // Event per tanggal untuk kalender
        $eventsByDay = [];
        foreach ($scheduledBookings as $booking) {
            [$dp, $remaining] = $payInfo($booking);
            $due = \Carbon\Carbon::parse($booking->payment_due_date);
            $eventsByDay[$due->day][] = [
                'id' => $booking->id,
                'code' => $booking->booking_code,
                'item' => $booking->vehicle?->name ?? $booking->item?->name ?? ($booking->category?->name ?? 'Unit Sewa'),
                'customer' => $booking->user?->name ?? '-',
                'amount' => (float) $booking->final_price,
                'remaining' => $remaining,
                'dp' => $dp,
                'status' => $booking->payment_status,
                'overdue' => $booking->payment_status !== 'paid' && $due->isPast(),
                'days_late' => $booking->payment_status !== 'paid' && $due->isPast() ? $due->startOfDay()->diffInDays(today()) : 0,
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
            'dp_this_month' => $scheduledBookings->where('payment_status', 'partial')->count(),
            'dp_this_month_amount' => $scheduledBookings->where('payment_status', 'partial')->sum('final_price'),
            'outstanding_all_amount' => (clone $allOutstanding)->sum('final_price'),
        ];

        // Daftar jatuh tempo terdekat (7 hari ke depan dari hari ini)
        $upcomingDues = \App\Models\Booking::with(['user', 'vehicle', 'category', 'item'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('payment_due_date')
            ->whereBetween('payment_due_date', [today(), today()->copy()->addDays(7)->endOfDay()])
            ->orderBy('payment_due_date')
            ->limit(10)
            ->get()
            ->map(function ($b) use ($payInfo) {
                [$b->dp_percent, $b->remaining] = $payInfo($b);
                return $b;
            });

        // Daftar tagihan terlambat
        $overdueList = \App\Models\Booking::with(['user', 'vehicle', 'category', 'item'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('payment_due_date')
            ->where('payment_due_date', '<', today())
            ->orderBy('payment_due_date')
            ->limit(10)
            ->get()
            ->map(function ($b) use ($payInfo) {
                [$b->dp_percent, $b->remaining] = $payInfo($b);
                return $b;
            });

        return view('superadmin.monitoring', compact(
            'month', 'eventsByDay', 'summary', 'upcomingDues', 'overdueList'
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
