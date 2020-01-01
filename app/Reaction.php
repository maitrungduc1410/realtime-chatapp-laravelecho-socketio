<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Message;
use App\User;

class Reaction extends Model
{
    protected $fillable = ['msg_id', 'user_id', 'emoji_id'];

    public function user () {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function message () {
        return $this->belongsTo(Message::class, 'msg_id');
    }
}
