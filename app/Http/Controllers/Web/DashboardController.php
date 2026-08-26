<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'user') {
            return redirect()->route('home');
        }

        return match($user->role) {
            'superadmin' => $this->superadminDashboard(),
            'owner' => view('dashboard.owner'),
            'driver' => view('dashboard.driver'),
            'inspector' => $this->inspectorDashboard(),
            default => redirect()->route('home'),
        };
    }

    private function superadminDashboard()
    {
        // Monitoring scheduler data
        $overdueBookings = \App\Models\Booking::where('status', 'ongoing')
            ->where('end_date', '<', now())
            ->with(['user', 'vehicle', 'bookingItems'])
            ->limit(10)
            ->get();

        $pendingPayments = \App\Models\Payment::where('status', 'pending')
            ->with(['invoice.booking', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $upcomingBookings = \App\Models\Booking::whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('start_date', [now(), now()->addDays(3)])
            ->with(['user', 'vehicle', 'category'])
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        $maintenanceVehicles = \App\Models\Vehicle::where('status', 'maintenance')
            ->with('category')
            ->limit(10)
            ->get();

        $driverOngoingCount = \App\Models\Driver::where('status', 'on_trip')->count();
        $pendingReplacementCount = \App\Models\VehicleReplacement::where('status', 'pending')->count();
        $pendingItemReplacementCount = \App\Models\ItemReplacement::where('status', 'pending')->count();

        return view('dashboard.superadmin', compact(
            'overdueBookings', 'pendingPayments', 'upcomingBookings',
            'maintenanceVehicles', 'driverOngoingCount', 'pendingReplacementCount', 'pendingItemReplacementCount'
        ));
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diperbarui');
    }

    private function inspectorDashboard()
    {
        $user = Auth::user();

        $totalInspections = \App\Models\Inspection::where('inspector_id', $user->id)->count();
        $thisMonthInspections = \App\Models\Inspection::where('inspector_id', $user->id)->whereMonth('created_at', now()->month)->count();

        $damageFindings = \App\Models\Inspection::where('inspector_id', $user->id)
            ->whereNotNull('damage_items')
            ->where('damage_items', '!=', '[]')
            ->count();

        $pendingPre = \App\Models\Booking::where('status', 'confirmed')
            ->whereDoesntHave('inspection', fn($q) => $q->where('type', 'pre_rental'))
            ->count();

        $pendingPost = \App\Models\Booking::where('status', 'ongoing')
            ->whereDoesntHave('inspection', fn($q) => $q->where('type', 'post_rental'))
            ->count();

        $preQueue = \App\Models\Booking::where('status', 'confirmed')
            ->whereDoesntHave('inspection', fn($q) => $q->where('type', 'pre_rental'))
            ->with(['user', 'vehicle', 'category', 'bookingItems'])
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        $postQueue = \App\Models\Booking::where('status', 'ongoing')
            ->whereDoesntHave('inspection', fn($q) => $q->where('type', 'post_rental'))
            ->with(['user', 'vehicle', 'category', 'bookingItems'])
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        $recentInspections = \App\Models\Inspection::where('inspector_id', $user->id)
            ->with(['booking.user', 'vehicle'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.inspector', compact(
            'totalInspections', 'thisMonthInspections', 'damageFindings',
            'pendingPre', 'pendingPost', 'preQueue', 'postQueue', 'recentInspections'
        ));
    }
}
