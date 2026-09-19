<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->notifications();

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where('data', 'like', $search);
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
