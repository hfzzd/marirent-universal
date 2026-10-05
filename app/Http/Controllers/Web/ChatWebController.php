<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatWebController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $conversations = $conversations->filter(function ($conv) use ($search, $userId) {
                $other = $conv->otherUser($userId);
                return str_contains(strtolower($other->name ?? ''), $search);
            })->values();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['conversations' => $conversations]);
        }

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
            $activeConversation = Conversation::where('id', $conversations->first()->id)
                ->with(['userOne', 'userTwo', 'messages.sender'])
                ->first();
        }

        if ($activeConversation) {
            ChatMessage::where('conversation_id', $activeConversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $contacts = User::where('id', '!=', $userId)->orderBy('name')->get();

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

        // Broadcast & Notify
        event(new \App\Events\ChatMessageSent($message));
        $receiver = $conversation->otherUser($userId);
        $receiver->notify(new \App\Notifications\NewChatMessage($message));

        // Auto-responder: if receiver is offline, send quick reply
        $presenceKey = 'user_presence_' . $receiver->id;
        $isOnline = Cache::get($presenceKey);
        
        if (!$isOnline && $receiver->isMerchantStaff()) {
            $autoReply = \App\Models\QuickReply::where('user_id', $receiver->id)
                ->where('shortcut', 'auto')
                ->first();
            
            if ($autoReply) {
                $autoMessage = ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $receiver->id,
                    'message' => $autoReply->reply,
                    'is_read' => false,
                ]);
                
                $conversation->update(['last_message_at' => now()]);
                event(new \App\Events\ChatMessageSent($autoMessage));
            }
        }

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

    public function quickReplies(Request $request)
    {
        $userId = Auth::id();
        $replies = \App\Models\QuickReply::where('user_id', $userId)
            ->orderBy('shortcut')
            ->get(['shortcut', 'reply']);

        return response()->json(['quick_replies' => $replies]);
    }

    public function storeQuickReply(Request $request)
    {
        $userId = Auth::id();
        $validated = $request->validate([
            'shortcut' => 'required|string|max:50|unique:quick_replies,shortcut,NULL,id,user_id,'.$userId,
            'reply' => 'required|string|max:500',
        ]);

        $reply = \App\Models\QuickReply::create([
            'user_id' => $userId,
            'shortcut' => $validated['shortcut'],
            'reply' => $validated['reply'],
        ]);

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    public function updateQuickReply(Request $request, \App\Models\QuickReply $reply)
    {
        $userId = Auth::id();
        if ($reply->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'shortcut' => 'sometimes|required|string|max:50|unique:quick_replies,shortcut,'.$reply->id.',id,user_id,'.$userId,
            'reply' => 'sometimes|required|string|max:500',
        ]);

        $reply->update($validated);

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    public function deleteQuickReply(\App\Models\QuickReply $reply)
    {
        $userId = Auth::id();
        if ($reply->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reply->delete();

        return response()->json(['success' => true]);
    }
}
