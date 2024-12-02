<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Events\BotNotification;
use App\Models\Chatroom;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::call(function () {
    $quotes = [
        'Bot: Detect typing and Seen message are available in Private Chat. Select an user from The right Sidebar and start Private Chat',
        'Bot: You can react to other user\'s message (Love, Haha, Angry,...)',
        'Bot: Try type \'Chuc mung\', \'Congrats\' or \'Congratulations\' to see animation',
        'Bot: You can change your message color in Private chat'
    ];

    $chatrooms = Chatroom::all();
    Log::info('Bot notification sending' . count($chatrooms));

    foreach($chatrooms as $cr) {
        $randIndex = array_rand($quotes);
        $message = $quotes[$randIndex];

        Log::info('Bot notification sending to ' . $cr->id . ' ' . $message);
        broadcast(new BotNotification($message, $cr->id));
    }

    Log::info('Bot notification sent');
})->everyMinute();

Schedule::command('telescope:prune')->daily();
Schedule::command('pulse:clear')->weekly();