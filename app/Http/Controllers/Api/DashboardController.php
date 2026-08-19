<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match($user->role) {
            'superadmin' => $this->superAdminDashboard(),
            'owner' => $this->ownerDashboard($user),
            'driver' => $this->driverDashboard($user),
            default => $this->userDashboard($user),
        };
    }

    private function superAdminDashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::count(),
                'total_vehicles' => Vehicle::count(),
                'total_bookings' => Booking::count(),
                'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
                'recent_bookings' => Booking::with(['user', 'vehicle'])->latest()->limit(5)->get(),
                'bookings_by_status' => Booking::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            ],
        ]);
    }

    private function ownerDashboard(User $user)
    {
        $vehicleIds = $user->vehicles()->pluck('id');

        return response()->json([
            'success' => true,
            'data' => [
                'total_vehicles' => $user->vehicles()->count(),
                'available_vehicles' => $user->vehicles()->where('status', 'available')->count(),
                'total_drivers' => $user->ownedDrivers()->count(),
                'active_drivers' => $user->ownedDrivers()->where('status', 'on_trip')->count(),
                'total_bookings' => Booking::whereIn('vehicle_id', $vehicleIds)->count(),
                'pending_bookings' => Booking::whereIn('vehicle_id', $vehicleIds)->where('status', 'pending')->count(),
                'total_revenue' => Invoice::where('owner_id', $user->id)->where('status', 'paid')->sum('total_amount'),
                'monthly_revenue' => Invoice::where('owner_id', $user->id)->where('status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount'),
                'recent_bookings' => Booking::whereIn('vehicle_id', $vehicleIds)
                    ->with(['user', 'vehicle'])->latest()->limit(5)->get(),
            ],
        ]);
    }

    private function driverDashboard(User $user)
    {
        $driver = Driver::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'driver_status' => $driver?->status ?? 'N/A',
                'total_trips' => $driver ? Booking::where('driver_id', $driver->id)->count() : 0,
                'active_trips' => $driver ? Booking::where('driver_id', $driver->id)->where('status', 'ongoing')->count() : 0,
                'upcoming_trips' => $driver ? Booking::where('driver_id', $driver->id)->where('status', 'confirmed')->get() : [],
                'recent_salary' => $driver ? $driver->salaries()->latest()->first() : null,
            ],
        ]);
    }

    private function userDashboard(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_bookings' => $user->bookings()->count(),
                'active_bookings' => $user->bookings()->where('status', 'ongoing')->count(),
                'pending_bookings' => $user->bookings()->where('status', 'pending')->count(),
                'total_spent' => $user->bookings()->where('status', 'completed')->sum('final_price'),
                'upcoming_bookings' => $user->bookings()->with(['vehicle', 'category'])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->latest()->limit(5)->get(),
                'recent_bookings' => $user->bookings()->with(['vehicle', 'category'])
                    ->latest()->limit(5)->get(),
            ],
        ]);
    }
}
