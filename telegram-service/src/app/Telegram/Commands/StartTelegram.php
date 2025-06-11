<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartTelegram extends Command
{
    protected $name = "start";
    protected $description = "Start interacting with the bot";

    public function handle()
    {
        // The start logic has been moved to App\Telegram\Handlers\StartHandler.
        // This command class is now deprecated or can be removed.
    }
}
