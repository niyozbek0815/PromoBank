<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\Register\SendPhoneRequest;
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

        if ($callback = $update->getCallbackQuery()) {
            $messageText = $callback->getData();
            Log::info("callback_data RouteHandler:" . $messageText);

            if (str_starts_with($messageText, 'lang:')) {
                Log::info("Start handlerga yo'naltirildi:" . $messageText);
                return app(StartHandler::class)->handle($update);
            }
        }
        // $messageText = '/start';

        Log::info("StartRouteHandler data:" . $messageText);
        if ($messageText === '/start') {
            Log::info("start:");
            Cache::store('redis')->forget('tg_user_data:' . $chatId, );

            return app(StartHandler::class)->ask($chatId);
        }
        if ($update->getMessage()->getContact()) {
            Log::info("getContaxt");

            return app(SendPhoneRequest::class)->handle($update);
        }

        return response()->noContent();
    }
}
