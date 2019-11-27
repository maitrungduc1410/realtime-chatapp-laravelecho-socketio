<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chatroom;

class ChatroomController extends Controller
{
    public function index () {
        return Chatroom::all();
    }
}
