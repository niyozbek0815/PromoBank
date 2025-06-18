<?php
namespace App\Telegram\Handlers\Routes;

use Illuminate\Support\Facades\Log;

class AuthenticatedRouteHandler
{

    public function handle($update)
    {
        $text   = $update->getMessage()?->getText() ?? $update->getCallbackQuery()?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        Log::info("data:" . $text);

    }
}
