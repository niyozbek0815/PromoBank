<?php

namespace App\Telegram\Middleware;

use App\Telegram\Services\UserSessionService;
use App\Telegram\Handlers\SendPhoneRequest;
use Illuminate\Support\Facades\Cache;

class EnsureTelegramSessionExists
{
    public function handle(string $chatId, ?string $messageText = null, bool $isOpenRoute = false)
    {
      

        return null;
    }
}