<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InboxMessage;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailWebController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $folder = $request->query('folder', 'inbox');

        $query = InboxMessage::with(['sender', 'receiver']);

        if ($folder === 'sent') {
            $query->where('sender_id', $userId)->where('is_trash_sender', false);
        } elseif ($folder === 'starred') {
            $query->where(function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('receiver_id', $userId)
                        ->where('is_starred_receiver', true)
                        ->where('is_trash_receiver', false);
                })->orWhere(function ($sub) use ($userId) {
                    $sub->where('sender_id', $userId)
                        ->where('is_starred_sender', true)
                        ->where('is_trash_sender', false);
                });
            });
        } elseif ($folder === 'trash') {
            $query->where(function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('receiver_id', $userId)->where('is_trash_receiver', true);
                })->orWhere(function ($sub) use ($userId) {
                    $sub->where('sender_id', $userId)->where('is_trash_sender', true);
                });
            });
        } else {
            // Default: inbox
            $folder = 'inbox';
            $query->where('receiver_id', $userId)->where('is_trash_receiver', false);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhere('body', 'like', "%{$request->search}%");
            });
        }

        $messages = $query->latest()->paginate(15);

        // Counts for sidebar badges
        $unreadInboxCount = InboxMessage::where('receiver_id', $userId)
            ->where('is_read', false)
            ->where('is_trash_receiver', false)
            ->count();

        $unreadContactCount = ContactMessage::where('is_read', false)->count();

        return view('mail.index', compact('messages', 'folder', 'unreadInboxCount', 'unreadContactCount'));
    }

    public function compose(Request $request)
    {
        $users = User::where('id', '!=', Auth::id())->orderBy('name')->get();
        $selectedUser = null;

        if ($request->to) {
            $selectedUser = User::find($request->to);
        }

        $replyTo = null;
        if ($request->reply_to) {
            $replyTo = InboxMessage::find($request->reply_to);
        }

        return view('mail.compose', compact('users', 'selectedUser', 'replyTo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['sender_id'] = Auth::id();

        InboxMessage::create($validated);

        return redirect()->route('mail.index', ['folder' => 'sent'])->with('success', 'Pesan berhasil dikirim!');
    }

    public function show(InboxMessage $message)
    {
        $userId = Auth::id();
        if ($message->receiver_id !== $userId && $message->sender_id !== $userId) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Mark as read if user is receiver
        if ($message->receiver_id === $userId && !$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('mail.show', compact('message'));
    }

    public function toggleStar(InboxMessage $message)
    {
        $userId = Auth::id();
        if ($message->receiver_id === $userId) {
            $message->update(['is_starred_receiver' => !$message->is_starred_receiver]);
        } elseif ($message->sender_id === $userId) {
            $message->update(['is_starred_sender' => !$message->is_starred_sender]);
        }

        return back()->with('success', 'Status bintang diperbarui.');
    }

    public function toggleTrash(InboxMessage $message)
    {
        $userId = Auth::id();
        if ($message->receiver_id === $userId) {
            $message->update(['is_trash_receiver' => !$message->is_trash_receiver]);
        } elseif ($message->sender_id === $userId) {
            $message->update(['is_trash_sender' => !$message->is_trash_sender]);
        }

        return back()->with('success', 'Pesan dipindahkan.');
    }

    public function destroy(InboxMessage $message)
    {
        $userId = Auth::id();
        if ($message->receiver_id === $userId || $message->sender_id === $userId) {
            $message->delete();
        }

        return redirect()->route('mail.index')->with('success', 'Pesan berhasil dihapus permanen.');
    }
}
