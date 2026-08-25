<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class SuperadminController extends Controller
{
    public function monitoring()
    {
        return view('superadmin.monitoring');
    }

    public function scheduler()
    {
        return view('superadmin.scheduler');
    }

    public function schedulerEvents(Request $request)
    {
        $start = $request->input('start') ? \Carbon\Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? \Carbon\Carbon::parse($request->input('end')) : now()->endOfMonth();

        $events = [];
        $bookings = Booking::with(['user', 'vehicle', 'invoice', 'invoice.payments'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->whereNotNull('actual_start_date')
                         ->whereBetween('actual_start_date', [$start, $end]);
                  })
                  ->orWhere(function ($q3) use ($start, $end) {
                      $q3->whereNotNull('actual_end_date')
                         ->whereBetween('actual_end_date', [$start, $end]);
                  });
            })
            ->get();

        foreach ($bookings as $b) {
            $userName = $b->user->name ?? '-';
            $vehicleName = $b->vehicle->name ?? ($b->category->name ?? '-');
            $code = $b->booking_code;
            $label = $code . ' - ' . $userName;

            if ($b->start_date && $b->start_date->between($start, $end)) {
                $events[] = [
                    'id' => 'start-' . $b->id,
                    'title' => $label . ' Mulai',
                    'start' => $b->start_date->toIso8601String(),
                    'color' => '#3b82f6',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'mulai',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'start_date' => $b->start_date->format('d M Y'),
                        'end_date' => $b->end_date ? $b->end_date->format('d M Y') : '-',
                    ],
                ];
            }

            if ($b->end_date && $b->end_date->between($start, $end)) {
                $events[] = [
                    'id' => 'end-' . $b->id,
                    'title' => $label . ' Selesai',
                    'start' => $b->end_date->toIso8601String(),
                    'color' => '#6366f1',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'selesai',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                    ],
                ];
            }

            if ($b->actual_start_date && $b->actual_start_date->between($start, $end)) {
                $events[] = [
                    'id' => 'pickup-' . $b->id,
                    'title' => $label . ' Penjemputan',
                    'start' => $b->actual_start_date->toIso8601String(),
                    'color' => '#06b6d4',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'penjemputan',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'pickup_location' => $b->pickup_location ?? '-',
                    ],
                ];
            }

            if ($b->actual_end_date && $b->actual_end_date->between($start, $end)) {
                $events[] = [
                    'id' => 'return-' . $b->id,
                    'title' => $label . ' Pemulangan',
                    'start' => $b->actual_end_date->toIso8601String(),
                    'color' => '#8b5cf6',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'pemulangan',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'dropoff_location' => $b->dropoff_location ?? '-',
                    ],
                ];
            }

            if ($b->invoice) {
                $invoice = $b->invoice;
                $paidAmount = $invoice->payments->where('status', 'verified')->sum('amount');

                if ($invoice->status === 'paid') {
                    $events[] = [
                        'id' => 'paid-' . $b->id,
                        'title' => $label . ' Lunas',
                        'start' => ($invoice->paid_at ?? $b->start_date)->toIso8601String(),
                        'color' => '#22c55e',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'lunas',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                        ],
                    ];
                } elseif ($invoice->status === 'partial' || ($paidAmount > 0 && $paidAmount < $invoice->total_amount)) {
                    $events[] = [
                        'id' => 'partial-' . $b->id,
                        'title' => $label . ' Sebagian',
                        'start' => ($invoice->due_date ?? $b->start_date)->toIso8601String(),
                        'color' => '#f59e0b',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'sebagian',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $invoice->total_amount - $paidAmount,
                        ],
                    ];
                } elseif ($invoice->status !== 'paid' && $invoice->due_date && $invoice->due_date->isPast()) {
                    $events[] = [
                        'id' => 'overdue-' . $b->id,
                        'title' => $label . ' Terlambat',
                        'start' => $invoice->due_date->toIso8601String(),
                        'color' => '#ef4444',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'terlambat',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $invoice->total_amount - $paidAmount,
                            'due_date' => $invoice->due_date->format('d M Y'),
                        ],
                    ];
                }
            }
        }

        // Maintenance Events
        $maintenances = Maintenance::with(['vehicle'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->whereNotNull('completed_date')
                         ->whereBetween('completed_date', [$start, $end]);
                  });
            })
            ->get();

        foreach ($maintenances as $m) {
            $vehicleName = $m->vehicle->name ?? '-';
            $typeLabels = ['routine' => 'Rutin', 'repair' => 'Perbaikan', 'inspection' => 'Inspeksi', 'emergency' => 'Darurat'];
            $priorityColors = ['low' => '#22c55e', 'medium' => '#f59e0b', 'high' => '#f97316', 'urgent' => '#ef4444'];
            $priorityColor = $priorityColors[$m->priority] ?? '#6b7280';

            $events[] = [
                'id' => 'maintenance-' . $m->id,
                'title' => '🔧 ' . $m->title,
                'start' => $m->scheduled_date->toIso8601String(),
                'color' => $priorityColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'maintenance',
                    'maintenance_code' => $m->maintenance_code,
                    'vehicle_name' => $vehicleName,
                    'maintenance_type' => $typeLabels[$m->type] ?? $m->type,
                    'priority' => $m->priority,
                    'status' => $m->status,
                    'estimated_cost' => $m->estimated_cost,
                    'technician' => $m->technician ?? '-',
                ],
            ];
        }

        return response()->json($events);
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
