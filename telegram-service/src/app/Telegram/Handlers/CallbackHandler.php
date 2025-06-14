<?php
namespace App\Telegram\Handlers;

use App\Telegram\Handlers\Start\LangCallbackHandler;

class CallbackHandler
{
    public function handle($callback)
    {
        $chatId = $callback->getMessage()->getChat()->getId();
        $data   = $callback->getData();

        if (str_starts_with($data, 'lang_')) {
            if (str_starts_with($data, 'lang_')) {
                return app(LangCallbackHandler::class)->handle($callback);
            }
        }

        return response()->noContent();
    }
}
