<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatWebController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Get all conversations for current user
        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        // Check if a specific conversation or user is requested
        $activeConversation = null;
        if ($request->conversation_id) {
            $activeConversation = Conversation::where('id', $request->conversation_id)
                ->where(fn($q) => $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId))
                ->with(['userOne', 'userTwo', 'messages.sender'])
                ->first();
        } elseif ($request->user_id) {
            $targetUser = User::findOrFail($request->user_id);
            if ($targetUser->id !== $userId) {
                $activeConversation = Conversation::findOrCreateBetween($userId, $targetUser->id);
                $activeConversation->load(['userOne', 'userTwo', 'messages.sender']);
            }
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $activeConversation->load(['userOne', 'userTwo', 'messages.sender']);
        }

        // Mark messages in active conversation as read
        if ($activeConversation) {
            ChatMessage::where('conversation_id', $activeConversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        // Available contacts to start new chat
        $contacts = User::where('id', '!=', $userId)
            ->orderBy('name')
            ->get();

        return view('chat.index', compact('conversations', 'activeConversation', 'contacts'));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'is_me' => true,
                ]
            ]);
        }

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }

    public function fetchMessages(Conversation $conversation)
    {
        $userId = Auth::id();
        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark unread as read
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i'),
                    'is_me' => $msg->sender_id === $userId,
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}
