<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Inspection::with('rental', 'vehicle', 'inspector');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('overall_condition')) {
            $query->where('overall_condition', $request->overall_condition);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('vehicle', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%");
                })->orWhereHas('rental', function ($q2) use ($search) {
                    $q2->where('rental_code', 'like', "%{$search}%");
                });
            });
        }

        if ($user->isDriver()) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->whereHas('rental', function ($q) use ($driver) {
                    $q->where('driver_id', $driver->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $inspections = $query->latest()->paginate(15)->withQueryString();

        return view('inspections.index', compact('inspections'));
    }

    public function create($rentalId)
    {
        $user = Auth::user();

        $rental = Rental::with('vehicle')->findOrFail($rentalId);

        if ($user->isDriver()) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if (!$driver || $rental->driver_id !== $driver->id) {
                abort(403, 'Anda tidak memiliki akses ke sewa ini.');
            }
        }

        return view('inspections.create', compact('rental'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rental_id'             => 'required|exists:rentals,id',
            'type'                  => 'required|in:pre_rental,post_rental,periodic',
            'checklist'             => 'required|array|min:1',
            'checklist.*.item'      => 'required|string|max:255',
            'checklist.*.status'    => 'required|in:good,fair,poor',
            'overall_condition'     => 'required|in:good,fair,poor',
            'notes'                 => 'nullable|string|max:2000',
            'next_inspection_date'  => 'nullable|date|after:today',
        ]);

        try {
            $rental = Rental::findOrFail($request->rental_id);

            $inspection = Inspection::create([
                'rental_id'             => $rental->id,
                'vehicle_id'            => $rental->vehicle_id,
                'inspector_id'          => Auth::id(),
                'type'                  => $request->type,
                'checklist'             => $request->checklist,
                'overall_condition'     => $request->overall_condition,
                'notes'                 => $request->notes,
                'next_inspection_date'  => $request->next_inspection_date,
            ]);

            return redirect()->route('inspections.show', $inspection->id)
                ->with('success', 'Inspeksi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan inspeksi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $inspection = Inspection::with('rental', 'vehicle', 'inspector')
            ->findOrFail($id);

        return view('inspections.show', compact('inspection'));
    }
}
