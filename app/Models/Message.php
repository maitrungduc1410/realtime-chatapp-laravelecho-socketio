<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Chatroom;
use App\Models\Reaction;

class Message extends Model
{
    use HasFactory;

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
