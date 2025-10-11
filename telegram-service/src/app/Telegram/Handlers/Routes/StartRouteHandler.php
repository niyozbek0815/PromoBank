<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\Start\StartHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class StartRouteHandler
{

    public function handle(Update $update)
    {

        $messageText = $update->getMessage()?->getText() ?? $update->getCallbackQuery()?->getData();
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        // $messageText = '/start';

        Log::info("StartRouteHandler data:" . $messageText);
        if ($messageText === '/start') {
            Log::info("start:");
            Cache::store('redis')->forget('tg_user_data:' . $chatId);
            Cache::store('redis')->forget('tg_user:' . $chatId);
            Cache::store('redis')->forget('tg_user_update:' . $chatId);

            return app(StartHandler::class)->startAsk($chatId);
        }
        // if ($update->getMessage()->getContact()) {
        //     return app(PhoneStepHandler::class)->handle($update);
        // }

        return response()->noContent();
    }
}
