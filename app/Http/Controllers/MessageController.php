<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Message;
use App\Models\Reaction;
use App\Events\MessagePosted;
use App\Events\MessageReacted;

class MessageController extends Controller
{
    public function index (Request $request) {
        $messages = Message::with(['sender', 'receiver', 'reactions.user'])
			->where('room', $request->query('room', ''))
            ->latest()
            ->paginate(50);
        return $messages;
    }

    public function store (Request $request) {
        $message = new Message();
        $message->sender = Auth::user()->id;
        $message->content = $request->input('content', '');

        if ($request->has('receiver') && $request->input('receiver')) {
            $receiver = (int) $request->input('receiver');
            $message->receiver = $receiver;
            $message->room = $message->sender < $receiver ? $message->sender.'__'.$receiver : $receiver.'__'.$message->sender;
        } else {
            $message->room = $request->input('room');
        }

        $message->save();

        broadcast(new MessagePosted($message->load(['sender', 'reactions.user'])))->toOthers(); // send to others EXCEPT user who sent this message

        return response()->json(['message' => $message->load(['sender', 'reactions.user'])]);
    }

    public function react (Request $request) {
        $reaction = Reaction::where(['msg_id' => $request->input('msg_id'), 'user_id' => $request->input('user_id')])->first();

        if ($reaction) {
            if ($reaction->emoji_id === $request->input('emoji_id')) {
                $reaction->delete();
            } else {
                $reaction->emoji_id = $request->input('emoji_id');

                $reaction->save();
            }
        } else {
            $reaction = new Reaction();
            $reaction->msg_id = $request->input('msg_id');
            $reaction->user_id = $request->input('user_id');
            $reaction->emoji_id = $request->input('emoji_id');

            $reaction->save();
        }

        broadcast(new MessageReacted($reaction->load('user'), $reaction->message->room))->toOthers();

        return ['success' => true, 'reaction' => $reaction];
    }
}
