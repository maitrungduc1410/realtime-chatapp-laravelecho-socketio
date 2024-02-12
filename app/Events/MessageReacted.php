<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reaction;
    public $channel;

    /**
     * Create a new event instance.
     */
    public function __construct($reaction, $channel)
    {
        $this->reaction = $reaction;
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        if (strpos($this->channel, '__') !== false) { // must use !== false
            return [new PrivateChannel('room.'.$this->channel)];
        } else {
            return [new PresenceChannel('room.'.$this->channel)];
        }
    }
}
