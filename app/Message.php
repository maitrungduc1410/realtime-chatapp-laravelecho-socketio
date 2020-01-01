<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Chatroom;
use App\Reaction;

class Message extends Model
{
    protected $fillable = ['room', 'sender', 'receiver', 'content'];

    public function sender () {
        return $this->belongsTo(User::class, 'sender');
    }

    public function receiver () {
        return $this->belongsTo(User::class, 'receiver');
    }

    public function room () {
        return $this->belongsTo(Chatroom::class, 'room');
    }

    public function reactions () {
        return $this->hasMany(Reaction::class, 'msg_id');
    }
}
