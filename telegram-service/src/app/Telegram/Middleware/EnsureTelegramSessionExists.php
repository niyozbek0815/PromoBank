<?php
namespace App\Telegram\Middleware;

use App\Telegram\Handlers\Routes\RegisterRouteHandler;
use App\Telegram\Handlers\Routes\StartRouteHandler;
use App\Telegram\Services\RegisterService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class EnsureTelegramSessionExists
{
    public function handle(Update $update)
    {
        $messageText  = $update->getMessage()?->getText();
        $callbackData = $update->getCallbackQuery()?->getData();
        $messageText  = $update->getMessage()?->getText();
        $isLangRoute  = $callbackData && str_starts_with($callbackData, 'lang:');
        $isStart      = $messageText === '/start';
        $isContact    = $update->getMessage()?->getContact();

        $isOpenRoute = $isLangRoute || $isStart || $isContact;
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        // Cache::store('redis')->forget('tg_user_data:' . $chatId, );

        $status = app(RegisterService::class)->getSessionStatus($chatId);
        Log::info("Middlewarega kirish: status-> " . $status . ". Message: " . $messageText);
        if ($status == 'in_progress' && ! $isOpenRoute) {
            Log::info("in_progress");
            return app(RegisterRouteHandler::class)->handle($update);
        }

        if ($isOpenRoute) {
            Log::info("Middlewarega none");

            // if (! $update->getMessage()?->getContact() && $messageText) {
            //     Cache::store('redis')->put("tg_pending:$chatId", $messageText, now()->addMinutes(5));
            // }

            return app(StartRouteHandler::class)->handle($update);
        }

        // if ($status == 'authenticated') {
        //     Log::info("in_progress");
        //     app(RegisterRoutehandler::class)->handle($update);

        // }
        return response()->noContent();

    }
}
