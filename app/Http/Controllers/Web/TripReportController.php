<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\TripReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = TripReport::with('rental', 'driver.user', 'reviewer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('rental', function ($q2) use ($search) {
                    $q2->where('rental_code', 'like', "%{$search}%");
                })->orWhereHas('driver.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $tripReports = $query->latest()->paginate(15)->withQueryString();

        return view('trip-reports.index', compact('tripReports'));
    }

    public function create($rentalId)
    {
        $user = Auth::user();

        $rental = Rental::with('vehicle', 'driver')->findOrFail($rentalId);

        if ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            if (!$driver || $rental->driver_id !== $driver->id) {
                abort(403, 'Anda tidak memiliki akses ke sewa ini.');
            }
        }

        return view('trip-reports.create', compact('rental'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rental_id'         => 'required|exists:rentals,id',
            'start_km'          => 'required|integer|min:0',
            'end_km'            => 'required|integer|min:0|gt:start_km',
            'start_time'        => 'required|date',
            'end_time'          => 'required|date|after:start_time',
            'fuel_cost'         => 'nullable|numeric|min:0',
            'toll_cost'         => 'nullable|numeric|min:0',
            'parking_cost'      => 'nullable|numeric|min:0',
            'other_cost'        => 'nullable|numeric|min:0',
            'route_description' => 'nullable|string|max:1000',
            'incidents'         => 'nullable|string|max:1000',
        ]);

        try {
            $rental = Rental::with('driver')->findOrFail($request->rental_id);

            $totalKm = $request->end_km - $request->start_km;
            $totalCost = ($request->fuel_cost ?? 0)
                + ($request->toll_cost ?? 0)
                + ($request->parking_cost ?? 0)
                + ($request->other_cost ?? 0);

            $tripReport = TripReport::create([
                'rental_id'         => $rental->id,
                'driver_id'         => $rental->driver_id,
                'start_km'          => $request->start_km,
                'end_km'            => $request->end_km,
                'total_km'          => $totalKm,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'fuel_cost'         => $request->fuel_cost ?? 0,
                'toll_cost'         => $request->toll_cost ?? 0,
                'parking_cost'      => $request->parking_cost ?? 0,
                'other_cost'        => $request->other_cost ?? 0,
                'total_cost'        => $totalCost,
                'route_description' => $request->route_description,
                'incidents'         => $request->incidents,
                'status'            => 'submitted',
            ]);

            return redirect()->route('trip-reports.show', $tripReport->id)
                ->with('success', 'Laporan perjalanan berhasil dikirim.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengirim laporan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tripReport = TripReport::with('rental', 'driver.user', 'reviewer')
            ->findOrFail($id);

        return view('trip-reports.show', compact('tripReport'));
    }

    public function approve($id, Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $tripReport = TripReport::findOrFail($id);

        if ($tripReport->status !== 'submitted') {
            return back()->with('error', 'Hanya laporan dengan status submitted yang dapat di-review.');
        }

        $request->validate([
            'status'       => 'required|in:reviewed,approved',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $tripReport->update([
                'status'       => $request->status,
                'reviewed_by'  => Auth::id(),
                'review_notes' => $request->review_notes,
            ]);

            return redirect()->route('trip-reports.show', $tripReport->id)
                ->with('success', 'Laporan perjalanan berhasil di-review.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereview laporan: ' . $e->getMessage());
        }
    }
}
