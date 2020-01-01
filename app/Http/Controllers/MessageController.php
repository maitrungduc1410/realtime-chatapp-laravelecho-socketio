<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;
use Auth;

class MessageController extends Controller
{
    public function index (Request $request) {
        $messages = Message::with(['sender'])->where('room', $request->query('room', ''))->orderBy('created_at', 'asc')->get();
        return $messages;
    }

    public function store (Request $request) {
        $message = new Message();
        $message->room = $request->input('room', '');
        $message->sender = Auth::user()->id;
        $message->content = $request->input('content', '');

        $message->save();

        return response()->json(['message' => $message->load('sender')]);
    }
}
