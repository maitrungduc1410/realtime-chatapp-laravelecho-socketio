<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chatroom;
use Auth;
use App\Emoji;

class AppController extends Controller
{
    public function index () {
        $data = ['user' => Auth::user(), 'rooms' => Chatroom::all(), 'emojis' => Emoji::all()];
        return view('app', ['data' => $data]);
    }
}
