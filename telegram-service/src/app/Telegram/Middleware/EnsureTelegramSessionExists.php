<?php
namespace App\Telegram\Middleware;

use App\Telegram\Handlers\Register\SendPhoneRequest;
use App\Telegram\Services\UserSessionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class EnsureTelegramSessionExists
{
    public function handle(Update $update, bool $isOpenRoute = false)
    {
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $messageText    = $update->getMessage()?->getText();
        $sessionService = app(UserSessionService::class);

        if (! $sessionService->exists($chatId) && ! $isOpenRoute) {
            if (! $update->getMessage()?->getContact() && $messageText) {
                Log::info($messageText . " Cachega yozildi");
                Cache::store('redis')->put("tg_pending:$chatId", $messageText, now()->addMinutes(5));
            }
            return app(SendPhoneRequest::class)->handle($chatId);
        }

        return null; // sessiya mavjud bo‘lsa, davom ettir
    }
}
