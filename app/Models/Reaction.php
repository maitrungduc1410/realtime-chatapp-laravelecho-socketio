<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;
use App\Models\User;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = ['msg_id', 'user_id', 'emoji_id'];

    public function user () {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function message () {
        return $this->belongsTo(Message::class, 'msg_id');
    }
}
