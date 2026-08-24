<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ContactDirectoryWebController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $query = User::query();

        if ($role && in_array($role, ['user', 'driver', 'owner', 'superadmin'])) {
            $query->where('role', $role);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $contacts = $query->orderBy('name')->paginate(16);

        $counts = [
            'all' => User::count(),
            'user' => User::where('role', 'user')->count(),
            'driver' => User::where('role', 'driver')->count(),
            'owner' => User::where('role', 'owner')->count(),
            'superadmin' => User::where('role', 'superadmin')->count(),
        ];

        return view('contacts.index', compact('contacts', 'counts', 'role'));
    }
}
