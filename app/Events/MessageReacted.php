<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reaction;
use Illuminate\Support\Facades\Log;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $msg_id;
    public $user_id;
    public $emoji_id;
    public $chatroom;

    /**
     * Create a new event instance.
     */
    public function __construct(array $data)
    {
        $this->msg_id = $data['msg_id'];
        $this->user_id = $data['user_id'];
        $this->emoji_id = $data['emoji_id'];
        $this->chatroom = $data['chatroom'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        Log::info('3333MessageReacted broadcastOn');
        if ($this->chatroom->private_room_id) {
            return [
                new PrivateChannel('room.'.$this->chatroom->id)
            ];
        }

        return [new PresenceChannel('room.'.$this->chatroom->id)];
    }

    public function broadcastWith()
    {
        return [
            'msg_id' => $this->msg_id,
            'user_id' => $this->user_id,
            'emoji_id' => $this->emoji_id,
        ];
    }
}
