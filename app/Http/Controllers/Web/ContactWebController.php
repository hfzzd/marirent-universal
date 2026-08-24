<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactWebController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Pesan Anda berhasil dikirim! Tim kami akan segera menghubungi Anda kembali.');
    }

    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        if ($request->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->filter === 'replied') {
            $query->whereNotNull('replied_at');
        }

        $messages = $query->paginate(15);
        $unreadCount = ContactMessage::where('is_read', false)->count();

        return view('mail.contacts', compact('messages', 'unreadCount'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->is_read) {
            $contactMessage->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return view('mail.contact-show', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply_message' => 'required|string',
        ]);

        $contactMessage->update([
            'reply_message' => $request->reply_message,
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan berhasil disimpan dan dicatat.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return redirect()->route('mail.contacts')->with('success', 'Pesan kontak berhasil dihapus.');
    }
}
