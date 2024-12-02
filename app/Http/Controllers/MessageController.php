<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\Chatroom;
use App\Events\MessagePosted;
use App\Events\MessageReacted;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index (Request $request) {
        $messages = Message::with(['user', 'reactions'])
			->where('room_id', (int) $request->query('room_id', ''))
            ->latest()
            ->paginate(50);
        return $messages;
    }

    public function store (Request $request) {
        $request->validate([
            'content' => 'required|max:2000',
        ]);

        $message = new Message();
        $message->room_id = $request->input('room_id', -1);
        $message->user_id = Auth::user()->id;
        $message->content = trim(strip_tags($request->input('content', '')));

        $message->save();

        $loadedMessage = $message->load(['user', 'reactions', 'chatroom']);
        broadcast(new MessagePosted($loadedMessage))->toOthers(); // send to others EXCEPT user who sent this message

        return response()->json(['message' => $loadedMessage]);
    }

    public function react (Request $request) {
        $user_id = Auth::user()->id;
        $reaction = Reaction::where(['msg_id' => $request->input('msg_id'), 'user_id' => $user_id])->first();
        $msg_id = $request->input('msg_id');
        $emoji_id = $request->input('emoji_id');

        if ($reaction) {
            if ($reaction->emoji_id === $request->input('emoji_id')) {
                // If same emoji is clicked, delete the reaction
                $chatroom = Chatroom::where('id', $reaction->message->room_id)->first();
                $reaction->delete();
            } else {
                // Update the emoji_id if different
                $reaction->emoji_id = $emoji_id;

                $reaction->save();

                $loaded = $reaction->load(['user', 'message.chatroom']);
                $chatroom = $loaded->message->chatroom;
            }
        } else {
            // Create new reaction if none exists
            $reaction = new Reaction();
            $reaction->msg_id = $msg_id;
            $reaction->user_id = $user_id;
            $reaction->emoji_id = $emoji_id;

            $reaction->save();

            $loaded = $reaction->load(['user', 'message.chatroom']);
            $chatroom = $loaded->message->chatroom;
        }

        broadcast(new MessageReacted([
            'msg_id' => $msg_id,
            'user_id' => $user_id,
            'emoji_id' => $emoji_id,
            'chatroom' => $chatroom,
        ]))->toOthers();
        

        return ['success' => true, 'reaction' => $reaction];
    }

    public function startChat(Request $request) {
        $user_id = $request->input('receiver_id');
        $current_user_id = Auth::user()->id;

        // create private room id, which is a combination of two user ids, pattern is: smaller id-larger id
        $private_room_id = $current_user_id < $user_id ? $current_user_id . '-' . $user_id : $user_id . '-' . $current_user_id;

        // find chat room by private_room_id if it exists
        $chatroom = Chatroom::where('private_room_id', $private_room_id)->first();

        if ($chatroom) {
            return $chatroom;
        }

        $chatroom = new Chatroom();
        $chatroom->private_room_id = $private_room_id;
        $chatroom->save();

        return $chatroom;
    }
}
