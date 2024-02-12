<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Events\BotNotification;
use App\Models\Chatroom;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            $quotes = [
                'Bot: Detect typing and Seen message are available in Private Chat. Select an user from The right Sidebar and start Private Chat',
                'Bot: You can react to other user\'s message (Love, Haha, Angry,...)',
                'Bot: Try type \'Chuc mung\', \'Congrats\' or \'Congratulations\' to see animation',
                'Bot: You can change your message color in Private chat'
            ];

            $chatrooms = Chatroom::all();

            foreach($chatrooms as $cr) {
                $randIndex = array_rand($quotes);
                $message = $quotes[$randIndex];
                $room = strval($cr->id);
                broadcast(new BotNotification($message, $room));
            }

            Log::info('Bot notification sent');
        })->everyMinute();

        $schedule->command('telescope:prune')->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
