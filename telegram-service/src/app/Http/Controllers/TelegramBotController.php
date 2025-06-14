<?php
namespace App\Http\Controllers;

use App\Telegram\Handlers\CallbackHandler;
use App\Telegram\Handlers\Register\ContactHandler;
use App\Telegram\Handlers\Start\StartHandler;
use App\Telegram\Middleware\EnsureTelegramSessionExists;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = request('__internal_update') ?? Telegram::getWebhookUpdate();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $messageText = $update->getMessage()?->getText();
        $isOpenRoute = in_array($messageText, ['/start']) ||
        $update->getCallbackQuery() ||
        $update->getMessage()?->getContact();

        // Middleware-style session check
        $middlewareResult = app(EnsureTelegramSessionExists::class)->handle($update, $isOpenRoute);
        if ($middlewareResult) {
            return $middlewareResult;
        }

        // Dispatching logic based on message content
        if ($update->getMessage()?->getContact()) {
            return app(ContactHandler::class)->handle($update->getMessage());
        }

        if ($callback = $update->getCallbackQuery()) {
            return app(CallbackHandler::class)->handle($callback);
        }

        if ($messageText === '/start') {
            return app(StartHandler::class)->handle($chatId);
        }

        if ($messageText === 'Salom') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => "Assalom alaykum",
            ]);
        }

        return response()->noContent();
    }

    // Bot uchun shartlar yozish agar manu shu chat isli user
    //  bo'lmasa uni yarat yoki yaratishb tugmasi bos. Yani web apdagi kabi register tizimini qurishim kerak
}
