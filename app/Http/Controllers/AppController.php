<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Chatroom;
use App\Models\Emoji;

class AppController extends Controller
{
    public function index () {
        $data = [
            'user' => Auth::user(),
            'rooms' => Chatroom::all(),
            'emojis' => Emoji::all(),
            'appName' => config('app.name'),
            'confettiWords' => env('APP_CONFETTI_WORDS')
        ];
        return view('app', ['data' => $data]);
    }
}
