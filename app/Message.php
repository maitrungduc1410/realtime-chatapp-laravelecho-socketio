<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Chatroom;

class Message extends Model
{
    protected $fillable = ['room', 'sender', 'content'];

    public function sender () {
        return $this->belongsTo(User::class, 'sender');
    }

    public function room () {
        return $this->belongsTo(Chatroom::class, 'room');
    }
}
