<?php
namespace App\Telegram\Middleware;

use App\Telegram\Handlers\Routes\AuthenticatedRouteHandler;
use App\Telegram\Handlers\Routes\RegisterRouteHandler;
use App\Telegram\Handlers\Routes\StartRouteHandler;
use App\Telegram\Handlers\Routes\UpdateRouteHandler;
use App\Telegram\Services\RegisterService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class EnsureTelegramSessionExists
{
    public function handle(Update $update)
    {
        $messageText = $update->getMessage()?->getText();
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $isOpenRoute = $messageText === '/start';

        $status = app(RegisterService::class)->getSessionStatus($chatId);
        Log::info("Middlewarega kirish: status-> " . $status . ". Message: " . $messageText);
        if ($status == 'in_register' && ! $isOpenRoute) {
            Log::info("in_register");
            return app(RegisterRouteHandler::class)->handle($update);
        }
        if ($status == 'in_update' && ! $isOpenRoute) {
            Log::info("in_update");
            app(UpdateRouteHandler::class)->handle($update);
        }

        if ($isOpenRoute) {
            Log::info("Middlewarega openRoute");
            return app(StartRouteHandler::class)->handle($update);
        }

        if ($status == 'authenticated') {
            Log::info("Middleware authenticated");
            app(AuthenticatedRouteHandler::class)->handle($update);
        }
        if ($status == 'none') {
            Log::info("Middleware none");
        }

        return response()->noContent();

    }
}
